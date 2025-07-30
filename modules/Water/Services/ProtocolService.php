<?php

namespace Modules\Water\Services;

use App\Enums\UserRoleEnum;
use App\Http\Requests\ProtocolChangeRequest;
use App\Http\Resources\DocumentResource;
use App\Http\Resources\ImageResource;
use App\Models\Role;
use App\Models\User;
use App\Services\FileService;
use Illuminate\Support\Facades\DB;
use Modules\Water\Const\CategoryType;
use Modules\Water\Const\ProtocolHistoryType;
use Modules\Water\Contracts\ProtocolRepositoryInterface;
use Modules\Water\Enums\ProtocolStatusEnum;
use Modules\Water\Models\Protocol;
use Modules\Water\Models\ProtocolHistory;
use Modules\Water\Models\ProtocolStatus;

class ProtocolService
{
    private HistoryService $historyService;

    public function __construct(
        protected ProtocolRepositoryInterface $repository,
        protected FileService                 $fileService
    )
    {
        $this->historyService = new HistoryService('protocol_histories');
    }

    public function getAll($user, $roleId, $filters = [])
    {
        $query = $this->repository->all($user, $roleId);
        return $this->repository->filter($query, $filters);
    }

    public function findById(?int $id)
    {
        return $this->repository->findById($id);
    }

    public function create(?array $data)
    {
        $protocol = $this->repository->create($data);
        $this->createHistory($protocol, ProtocolHistoryType::CREATE_FIRST);
        return $protocol;
    }

    public function update($user, $roleId, ?int $id, ?array $data, $type)
    {
        if ($roleId == UserRoleEnum::INSPECTOR->value && $data['protocol_status_id'] == ProtocolStatusEnum::NOT_DEFECT->value) {
            $type = ProtocolHistoryType::CONFIRM_NOT_DEFECT;
            $data['protocol_status_id'] = ProtocolStatusEnum::CONFIRM_NOT_DEFECT->value;
        }
        if ($roleId == UserRoleEnum::WATER_INSPECTOR->value && $data['protocol_status_id'] == ProtocolStatusEnum::NOT_DEFECT->value) {
            $data['protocol_status_id'] = ProtocolStatusEnum::NOT_DEFECT->value;
            $data['is_finished'] = true;
            $type = ProtocolHistoryType::NOT_DEFECT;
        }


        $protocol = $this->repository->update($id, $data);
        $this->createHistory($protocol, $type);
        return $protocol;
    }

    public function statusChange($id, ProtocolChangeRequest $request)
    {
        DB::beginTransaction();
        try {
            $protocol = $this->repository->change($id, $request->except('images', 'docs', 'comment'));
            if ($protocol->protocol_status_id == ProtocolStatusEnum::CONFIRM_RESULT) {
                $type = ProtocolHistoryType::CONFIRM_RESULT;
            }else{
                $type = ProtocolHistoryType::SEND_HMQO;
            }
            $historyId = $this->createHistory($protocol, $type, $request->comment);
            if (!empty($request->docs)){
                $this->createHistoryFiles($historyId, $request->docs);
            }
            if (!empty($request->images)){
                $this->createHistoryImages($historyId, $request->images);
            }

            ProtocolHistory::query()->find($historyId);
            DB::commit();
            return $protocol;
        }catch (\Exception $exception){
            DB::rollBack();
            throw  $exception;
        }
    }

    public function fine($protocolId)
    {
        try {
            $data['protocol_status_id'] = ProtocolStatusEnum::ADMINISTRATIVE->value;
            $this->repository->update($protocolId, $data);
        }catch (\Exception $exception){
            throw  $exception;
        }
    }

    public function confirmDefect($user, $roleId, $id)
    {
        $protocol = $this->repository->confirmDefect($user, $roleId, $id);
        $this->createHistory($protocol, ProtocolHistoryType::CONFIRM_DEFECT);
        return $protocol;
    }

    public function confirmResult($user, $roleId, $id)
    {
        $protocol = $this->repository->confirmResult($user, $roleId, $id);
        $this->createHistory($protocol, ProtocolHistoryType::CONFIRMED);
        return $protocol;
    }

    public function rejectResult($user, $roleId, $data)
    {
        $protocol = $this->repository->rejectResult($user, $roleId, $data['id']);
        $this->createHistory($protocol, ProtocolHistoryType::REJECT_RESULT, $data['comment']);
        return $protocol;
    }



    public function rejectDefect($user, $roleId, $data)
    {
        $protocol = $this->repository->rejectDefect($user, $roleId, $data['id']);
        $this->createHistory($protocol, ProtocolHistoryType::REJECT_DEFECT, $data['comment']);
        return $protocol;
    }

    public function attach(?array $data, $user, ?int $roleId)
    {
        $protocol = $this->repository->attach($data, $user, $roleId);
        $this->createHistory($protocol, ProtocolHistoryType::ATTACH_INSPECTOR);
        return $protocol;
    }

    public function history($id)
    {
        $protocol = $this->findById($id);
        return $protocol->histories->map(function ($history) {
            return [
                'id' => $history->id,
                'comment' => $history->content->comment,
                'user' => $history->content->user ? User::query()->find($history->content->user, ['name', 'surname', 'middle_name']) : null,
                'role' => $history->content->role ? Role::query()->find($history->content->role, ['name', 'description']) : null,
                'status' => $history->content->status ? ProtocolStatus::query()->find($history->content->status, ['id', 'name']) : null,
                'type' => $history->type,
                'files' => $history->documents ? DocumentResource::collection($history->documents): null,
                'images' =>$history->images ? ImageResource::collection($history->images): null,
                'is_change' => $history->type ? ProtocolHistoryType::getLabel($history->type) : null,
                'created_at' => $history->created_at,
            ];
        })->sortByDesc('created_at')->values();

    }

    public function count($user, $roleId, $filters = []): array
    {
        $query = $this->repository->all($user, $roleId)->where('type', $filters['type']);
        if ($filters['category'] == CategoryType::MONITORING) {
            return [
                'all' => $query->clone()->where('category', CategoryType::MONITORING)->count(),
                'enter_result' => $query->clone()->where('category', CategoryType::MONITORING)->where('protocol_status_id', ProtocolStatusEnum::ENTER_RESULT->value)->count(),
                'confirm_not_defect' => $query->clone()->where('category', CategoryType::MONITORING)->where('protocol_status_id', ProtocolStatusEnum::CONFIRM_NOT_DEFECT->value)->count(),
                'forming' => $query->clone()->where('category', CategoryType::MONITORING)->where('protocol_status_id', ProtocolStatusEnum::FORMING->value)->count(),
                'not_defect' => $query->clone()->where('category', CategoryType::MONITORING)->where('protocol_status_id', ProtocolStatusEnum::NOT_DEFECT->value)->count(),
            ];
        } elseif ($filters['category'] == CategoryType::REGULATION) {
            return [
                'all' => $query->clone()->where('category', CategoryType::REGULATION)->count(),
                'formed' => $query->clone()->where('category', CategoryType::REGULATION)->where('protocol_status_id', ProtocolStatusEnum::FORMED->value)->count(),
                'administrative' => $query->clone()->where('category', CategoryType::REGULATION)->where('protocol_status_id', ProtocolStatusEnum::ADMINISTRATIVE->value)->count(),
                'hmqo' => $query->clone()->where('category', CategoryType::REGULATION)->where('protocol_status_id', ProtocolStatusEnum::HMQO->value)->count(),
                'confirm_result' => $query->clone()->where('category', CategoryType::REGULATION)->where('protocol_status_id', ProtocolStatusEnum::CONFIRM_RESULT->value)->count(),
                'confirmed' => $query->clone()->where('category', CategoryType::REGULATION)->where('protocol_status_id', ProtocolStatusEnum::CONFIRMED->value)->count(),
            ];
        } else {
            return [
                'all' => 0
            ];
        }
    }

    public function reject($user, $roleId, $data)
    {
        $protocol = $this->repository->reject($user, $roleId, $data['id']);
        $this->createHistory($protocol, ProtocolHistoryType::REJECT, $data['comment']);
        return $protocol;
    }


    public function saveImages(Protocol $protocol, ?array $images)
    {
        $paths = array_map(fn($image) => $this->fileService->uploadImage($image, 'protocol/images'), $images);
        $protocol->images()->createMany(array_map(fn($path) => ['url' => $path], $paths));
    }

    public function saveFiles(Protocol $protocol, ?array $files)
    {
        if (!empty($files)) {
            $paths = array_map(fn($file) => $this->fileService->uploadImage($file, 'protocol/files'), $files);
            $protocol->documents()->createMany(array_map(fn($path) => ['url' => $path], $paths));
        }
    }

    public function createHistoryFiles($id, $files)
    {
        $history = ProtocolHistory::query()->findOrFail($id);
        $paths = array_map(fn($file) => $this->fileService->uploadFile($file, 'protocol-history/files'), $files);
        $history->documents()->createMany(array_map(fn($path) => ['url' => $path], $paths));
    }

    public function createHistoryImages($id, $images)
    {
        $history = ProtocolHistory::query()->findOrFail($id);
        $paths = array_map(fn($file) => $this->fileService->uploadFile($file, 'protocol-history/images'), $images);
        $history->images()->createMany(array_map(fn($path) => ['url' => $path], $paths));
    }

    public function uploadFiles(Protocol $protocol, string $column, ?array $files)
    {
        if (!empty($files)) {
            $paths = array_map(fn($file) => $this->fileService->uploadFile($file, 'protocol/files'), $files);
            $protocol->$column = json_encode(array_map(fn($path) => ['url' => $path], $paths));
            $protocol->save();
        }
    }

    public function createHistory($protocol, $type, $comment = "", $meta = null)
    {
        return $this->historyService->createHistory(
            guid: $protocol->id,
            status: $protocol->protocol_status_id->value,
            type: $type,
            date: null,
            comment: $comment,
            additionalInfo: $meta
        );
    }

}
