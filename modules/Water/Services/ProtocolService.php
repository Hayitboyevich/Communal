<?php

namespace Modules\Water\Services;

use App\Constants\ErrorMessage;
use App\Enums\UserRoleEnum;
use App\Services\FileService;
use Modules\Water\Const\CategoryType;
use Modules\Water\Contracts\ProtocolRepositoryInterface;
use Modules\Water\Enums\ProtocolStatusEnum;
use Modules\Water\Models\Protocol;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProtocolService
{
    public function __construct(
        protected ProtocolRepositoryInterface $repository,
        protected FileService $fileService
    ){}

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
        return  $this->repository->create($data);
    }

    public function update($user, $roleId,?int $id, ?array $data)
    {
        if ($roleId == UserRoleEnum::INSPECTOR->value && $data['protocol_status_id'] == ProtocolStatusEnum::NOT_DEFECT->value)
        {
            $data['protocol_status_id'] = ProtocolStatusEnum::CONFIRM_NOT_DEFECT->value;
        }
        if ($roleId == UserRoleEnum::WATER_INSPECTOR->value && $data['protocol_status_id'] == ProtocolStatusEnum::NOT_DEFECT->value)
        {
            $data['protocol_status_id'] = ProtocolStatusEnum::NOT_DEFECT->value;
            $data['is_finished'] = true;
        }

        return $this->repository->update($id, $data);
    }


    public function confirmDefect($user, $roleId, $id)
    {
       return  $this->repository->confirmDefect($user, $roleId, $id);
    }

    public function rejectDefect($user, $roleId, $id)
    {
        return  $this->repository->rejectDefect($user, $roleId, $id);
    }

    public function attach(?array $data, $user, ?int $roleId)
    {
        return $this->repository->attach($data, $user, $roleId);
    }

    public function count($user, $roleId, $filters = []): array
    {
        $query = $this->repository->all($user, $roleId);
        if ($filters['category'] == CategoryType::MONITORING){
            return [
                'all' => $query->clone()->where('category', CategoryType::MONITORING)->count(),
                'enter_result' => $query->clone()->where('category', CategoryType::MONITORING)->where('protocol_status_id', ProtocolStatusEnum::ENTER_RESULT->value)->count(),
                'confirm_not_defect' => $query->clone()->where('category', CategoryType::MONITORING)->where('protocol_status_id', ProtocolStatusEnum::CONFIRM_NOT_DEFECT->value)->count(),
                'forming' => $query->clone()->where('category', CategoryType::MONITORING)->where('protocol_status_id', ProtocolStatusEnum::ENTER_RESULT->value)->count(),
                'not_defect' => $query->clone()->where('category', CategoryType::MONITORING)->where('protocol_status_id', ProtocolStatusEnum::NOT_DEFECT->value)->count(),
            ];
        }elseif($filters['category'] == CategoryType::REGULATION){
            return [
                'all' => $query->clone()->where('category', CategoryType::REGULATION)->count(),
                'formed' => $query->clone()->where('category', CategoryType::REGULATION)->where('protocol_status_id', ProtocolStatusEnum::FORMED->value)->count(),
                'administrative' => $query->clone()->where('category', CategoryType::REGULATION)->where('protocol_status_id', ProtocolStatusEnum::ADMINISTRATIVE->value)->count(),
                'hmqo' => $query->clone()->where('category', CategoryType::REGULATION)->where('protocol_status_id', ProtocolStatusEnum::HMQO->value)->count(),
                'confirm_result' => $query->clone()->where('category', CategoryType::REGULATION)->where('protocol_status_id', ProtocolStatusEnum::CONFIRM_RESULT->value)->count(),
                'confirmed' => $query->clone()->where('category', CategoryType::REGULATION)->where('protocol_status_id', ProtocolStatusEnum::CONFIRMED->value)->count(),
            ];
        }
        else{
            return [
                'all' => 0
            ];
        }

    }

    public function reject($user, $roleId, $id)
    {
        return $this->repository->reject($user, $roleId, $id);
    }


    public function saveImages(Protocol $protocol, ?array $images)
    {
        $paths = array_map(fn($image) => $this->fileService->uploadImage($image, 'protocol/images'), $images);
        $protocol->images()->createMany(array_map(fn($path) => ['url' => $path], $paths));
    }

    public function saveFiles(Protocol $protocol, ?array $files)
    {
        if(!empty($files)){
            $paths = array_map(fn($file) => $this->fileService->uploadImage($file, 'protocol/files'), $files);
            $protocol->documents()->createMany(array_map(fn($path) => ['url' => $path], $paths));
        }

    }
    public function uploadFiles(Protocol $protocol, string $column, ?array $files)
    {
        if (!empty($files)) {
            $paths = array_map(fn($file) => $this->fileService->uploadFile($file, 'protocol/files'), $files);
            $protocol->$column = json_encode(array_map(fn($path) => ['url' => $path], $paths));
            $protocol->save();
        }

    }

}
