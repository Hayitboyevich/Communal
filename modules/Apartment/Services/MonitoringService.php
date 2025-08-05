<?php

namespace Modules\Apartment\Services;

use App\Http\Requests\MonitoringCreateSecondRequest;
use App\Http\Requests\MonitoringMyHomeRequest;
use App\Http\Resources\DocumentResource;
use App\Http\Resources\ImageResource;
use App\Models\Role;
use App\Models\User;
use App\Services\FileService;
use Illuminate\Support\Facades\DB;
use Modules\Apartment\Const\MonitoringHistoryType;
use Modules\Apartment\Contracts\MonitoringRepositoryInterface;
use Modules\Apartment\Enums\MonitoringStatusEnum;
use Modules\Apartment\Http\Requests\MonitoringChangeStatusRequest;
use Modules\Apartment\Http\Requests\MonitoringCreateRequest;
use Modules\Apartment\Http\Requests\ViolationRequest;
use Modules\Apartment\Models\MonitoringHistory;
use Modules\Apartment\Models\MonitoringStatus;
use Modules\Water\Const\ProtocolHistoryType;
use Modules\Water\Const\Step;
use Modules\Water\Models\ProtocolStatus;
use Modules\Water\Services\HistoryService;
use Illuminate\Http\Request;

class MonitoringService
{
    private HistoryService $historyService;

    public function __construct(
        protected MonitoringRepositoryInterface $repository,
        protected FileService                   $fileService
    )
    {
        $this->historyService = new HistoryService('monitoring_histories');
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

    public function create(MonitoringCreateRequest $request)
    {
        DB::beginTransaction();
        try {
            $monitoring = $this->repository->create($request->except('images', 'docs'));
            $this->saveImages($monitoring, $request['images'], 'monitoring/images');
            $this->saveFiles($monitoring, $request['docs'], 'monitoring/files');
            $this->createHistory($monitoring, MonitoringHistoryType::CREATE_FIRST);
            DB::commit();
            return $monitoring;
        } catch (\Exception $exception) {
            DB::rollBack();
            throw  $exception;
        }
    }

    public function createBasic(MonitoringMyHomeRequest $request)
    {
        DB::beginTransaction();
        try {
            $monitoring = $this->repository->create($request->except('images', 'docs', 'region', 'district'));
            $this->saveImages($monitoring, $request['images'], 'monitoring/images');
            $this->saveFiles($monitoring, $request['docs'], 'monitoring/files');
            $this->createHistory($monitoring, MonitoringHistoryType::CREATE_FIRST);
            DB::commit();
            return $monitoring;
        }catch (\Exception $exception){
            DB::rollBack();
            throw  $exception;
        }
    }

    public function attach($userId, $monitoringId)
    {
        try {
            $monitoring = $this->repository->attach($userId, $monitoringId);
            $this->createHistory($monitoring, MonitoringHistoryType::ATTACH);
            return $monitoring;
        }catch (\Exception $exception){
            throw  $exception;
        }
    }

    public function history($id)
    {
        try {
            $monitoring = $this->findById($id);
            return $monitoring->histories->map(function ($history) {
                return [
                    'id' => $history->id,
                    'comment' => $history->content->comment,
                    'user' => $history->content->user ? User::query()->find($history->content->user, ['name', 'surname', 'middle_name']) : null,
                    'role' => $history->content->role ? Role::query()->find($history->content->role, ['name', 'description']) : null,
                    'status' => $history->content->status ? MonitoringStatus::query()->find($history->content->status, ['id', 'name']) : null,
                    'type' => $history->type,
                    'files' => $history->documents ? DocumentResource::collection($history->documents): null,
                    'images' =>$history->images ? ImageResource::collection($history->images): null,
                    'is_change' => $history->type ? MonitoringHistoryType::getLabel($history->type) : null,
                    'created_at' => $history->created_at,
                ];
            })->sortByDesc('created_at')->values();

        }catch (\Exception $exception){
            throw  $exception;
        }
    }

    public function createSecond($id, MonitoringCreateSecondRequest $request)
    {
        try {
            return $this->repository->createSecond($id, $request->all());
        } catch (\Exception $exception) {
            throw  $exception;
        }
    }

    public function confirm($id)
    {
        try {
            $monitoring = $this->repository->confirm($id);
            $this->createHistory($monitoring, MonitoringHistoryType::CONFIRM_VIOLATION);
            return $monitoring;
        } catch (\Exception $exception) {
            throw  $exception;
        }
    }

    public function reject($id, Request $request)
    {
        try {
            $monitoring = $this->repository->reject($id);
            $historyId = $this->createHistory($monitoring, MonitoringHistoryType::REJECT_VIOLATION_NOT_DETECTED, $request['comment']);
            $monitoringHistory = MonitoringHistory::query()->find($historyId);
            if (isset($request['images'])){
                $this->saveImages($monitoringHistory, $request['images'], 'monitoring-history/images');
            }

            if (isset($request['docs'])){
                $this->saveFiles($monitoringHistory, $request['docs'], 'monitoring-history/files');
            }
            return$monitoring;

        } catch (\Exception $exception) {
            throw  $exception;
        }
    }

    public function changeStatus($id, MonitoringChangeStatusRequest $request)
    {
        try {
            $monitoring = $this->repository->changeStatus($id, $request->monitoring_status_id);

            $historyId = null;
            $statusId = $request->monitoring_status_id;

            if ($statusId ==MonitoringStatusEnum::CONFIRM_RESULT->value) {
                $historyId = $this->createHistory(
                    $monitoring,
                    type: MonitoringHistoryType::REGULATION_NOT_DETECTED,
                    comment: $request['comment'] ?? null
                );
            } elseif ($statusId ==MonitoringStatusEnum::COURT->value) {
                $historyId = $this->createHistory(
                    $monitoring,
                    type: MonitoringHistoryType::SEND_COURT,
                    comment: $request['comment'] ?? null
                );
            }
            elseif ($statusId ==MonitoringStatusEnum::MIB->value) {
                $historyId = $this->createHistory(
                    $monitoring,
                    type: MonitoringHistoryType::SEND_MIIB,
                    comment: $request['comment'] ?? null
                );
            }
            elseif ($statusId == MonitoringStatusEnum::FIXED->value) {
                $historyId = $this->createHistory(
                    $monitoring,
                    type: MonitoringHistoryType::DONE,
                    comment: $request['comment'] ?? null
                );
            }

            elseif ($statusId ==MonitoringStatusEnum::DONE->value) {
                $historyId = $this->createHistory(
                    $monitoring,
                    type: MonitoringHistoryType::DONE,
                    comment: $request['comment'] ?? null
                );
            }

            if ($historyId) {
                $monitoringHistory = MonitoringHistory::query()->find($historyId);

                if (!empty($request['images'])) {
                    $this->saveImages($monitoringHistory, $request['images'], 'monitoring-history/images');
                }

                if (!empty($request['docs'])) {
                    $this->saveFiles($monitoringHistory, $request['docs'], 'monitoring-history/files');
                }
            }

            return $this->repository->update($id, $request->only([
                'is_administrative',
                'send_court',
                'type',
                'send_mib',
                'send_chora'
            ]));

        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function confirmRegulation($id)
    {
        try {
            $this->repository->changeStatus($id, MonitoringStatusEnum::DONE->value);
            $monitoring = $this->repository->update($id, ['step' => Step::FOUR]);
            $this->createHistory($monitoring, MonitoringHistoryType::CONFIRM_REGULATION);
            return $monitoring;
        }catch (\Exception $exception){
            throw  $exception;
        }
    }

    public function rejectRegulation($id, Request $request)
    {
        try {
            $monitoring =  $this->repository->changeStatus($id, MonitoringStatusEnum::FORMED->value);
            $this->createHistory($monitoring, type:MonitoringHistoryType::REJECT_REGULATION_NOT_DETECTED, comment: $request['comment']);
            return $monitoring;
        }catch (\Exception $exception){
            throw  $exception;
        }
    }

    public function count($user, $roleId,$filters = [])
    {
        try {
            $data = $this->repository->all($user, $roleId);
            $query = $this->repository->filter($data, $filters);

            if ($filters['type'] == 1){
                return [
                    'all' => $query->clone()->count(),
                    'enter_result' => $query->clone()->where('type', $filters['type'])->where('monitoring_status_id', MonitoringStatusEnum::ENTER_RESULT->value)->count(),
                    'confirm_not_defect' => $query->clone()->where('monitoring_status_id', MonitoringStatusEnum::CONFIRM_DEFECT->value)->count(),
                    'not_defect' => $query->clone()->where('monitoring_status_id', MonitoringStatusEnum::NOT_DEFECT->value)->count(),
                    'defect' => $query->clone()->where('monitoring_status_id', MonitoringStatusEnum::DEFECT->value)->count(),
                ];
            }elseif ($filters['type'] == 2){
                return [
                    'all' => $query->clone()->count(),
                    'formed' => $query->clone()->where('monitoring_status_id', MonitoringStatusEnum::FORMED->value)->count(),
                    'administrative' => $query->clone()->where('monitoring_status_id', MonitoringStatusEnum::ADMINISTRATIVE->value)->count(),
                    'done' => $query->clone()->where('monitoring_status_id', MonitoringStatusEnum::DONE->value)->count(),
                    'hmqo' => $query->clone()->where('monitoring_status_id', MonitoringStatusEnum::HMQO->value)->count(),
                    'confirm_result' => $query->clone()->where('monitoring_status_id', MonitoringStatusEnum::CONFIRM_RESULT->value)->count(),
                    'court' => $query->clone()->where('monitoring_status_id', MonitoringStatusEnum::COURT->value)->count(),
                    'mib' => $query->clone()->where('monitoring_status_id', MonitoringStatusEnum::MIB->value)->count(),
                    'sryx' => $query->clone()->where('monitoring_status_id', MonitoringStatusEnum::SRYX->value)->count(),
                    'fixed' => $query->clone()->where('monitoring_status_id', MonitoringStatusEnum::FIXED->value)->count(),
                ];
            }
            return null;

        }catch (\Exception $exception){
            throw  $exception;
        }
    }

    public function createThird($id, ViolationRequest $request)
    {
        try {
            foreach ($request->violations as $data) {
                $violation = $this->repository->createThird($id, $data);
                if (isset($data['images']))
                {
                    $this->saveImages($violation, $data['images'], 'violation/images');
                }
                if (isset($data['docs'])){
                    $this->saveFiles($violation, $data['docs'], 'violation/files');
                }
            }

        } catch (\Exception $exception) {
            throw  $exception;
        }
    }

    public function fine($monitoringId)
    {
        $data['monitoring_status_id'] = MonitoringStatusEnum::ADMINISTRATIVE->value;
        $data['is_administrative'] = true;
        $this->repository->update($monitoringId, $data);
        $monitoring = $this->findById($monitoringId);
        $this->createHistory($monitoring, MonitoringHistoryType::FINE);
    }

    private function saveImages($model, ?array $images, $filePath)
    {
        $paths = array_map(fn($image) => $this->fileService->uploadImage($image, $filePath), $images);
        $model->images()->createMany(array_map(fn($path) => ['url' => $path], $paths));
    }

    private function saveFiles($model, ?array $files, $filePath)
    {
        if (!empty($files)) {
            $paths = array_map(fn($file) => $this->fileService->uploadImage($file, $filePath), $files);
            $model->documents()->createMany(array_map(fn($path) => ['url' => $path], $paths));
        }
    }

    public function createHistory($monitoring, $type, $comment = "", $meta = null)
    {
        return $this->historyService->createHistory(
            guid: $monitoring->id,
            status: $monitoring->monitoring_status_id->value,
            type: $type,
            date: null,
            comment: $comment,
            additionalInfo: $meta
        );
    }

}
