<?php

namespace Modules\Apartment\Services;

use App\Http\Requests\MonitoringCreateSecondRequest;
use App\Services\FileService;
use Illuminate\Support\Facades\Request;
use Modules\Apartment\Contracts\MonitoringRepositoryInterface;
use Modules\Apartment\Enums\MonitoringStatusEnum;
use Modules\Apartment\Http\Requests\MonitoringCreateRequest;
use Modules\Apartment\Http\Requests\ViolationRequest;
use Modules\Apartment\Models\Monitoring;
use Modules\Water\Models\Protocol;
use Modules\Water\Services\HistoryService;

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
        try {
            $monitoring = $this->repository->create($request->except('images', 'docs'));
            $this->saveImages($monitoring, $request['images'], 'monitoring/images');
            $this->saveFiles($monitoring, $request['docs'], 'monitoring/files');
            return $monitoring;
        } catch (\Exception $exception) {
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
            return $this->repository->confirm($id);
        } catch (\Exception $exception) {
            throw  $exception;
        }
    }

    public function reject($id, Request $request)
    {
        try {
            return $this->repository->reject($id);
            //history yoziladi
        } catch (\Exception $exception) {
            throw  $exception;
        }
    }

    public function count($user, $roleId, $filters = [])
    {
        try {
            $query = $this->repository->all($user, $roleId);
            return [
                'all' => $query->clone()->count(),
                'enter_result' => $query->clone()->where('monitoring_status_id', MonitoringStatusEnum::ENTER_RESULT->value)->count(),
                'confirm_not_defect' => $query->clone()->where('monitoring_status_id', MonitoringStatusEnum::CONFIRM_DEFECT->value)->count(),
                'not_defect' => $query->clone()->where('monitoring_status_id', MonitoringStatusEnum::NOT_DEFECT->value)->count(),
                'defect' => $query->clone()->where('monitoring_status_id', MonitoringStatusEnum::DEFECT->value)->count(),
            ];
        }catch (\Exception $exception){
            throw  $exception;
        }
    }

    public function createThird($id, ViolationRequest $request)
    {
        try {
            foreach ($request->violations as $data) {
                $violation = $this->repository->violation($data);
                $this->saveImages($violation, $data['images'], 'violation/images');
                $this->saveFiles($violation, $data['docs'], 'violation/files');
            }
        } catch (\Exception $exception) {
            throw  $exception;
        }
    }

    public function violation(ViolationRequest $request)
    {
        try {
            foreach ($request->violations as $data) {
                $violation = $this->repository->violation($data);
                $this->saveImages($violation, $data['images'], 'violation/images');
                $this->saveFiles($violation, $data['docs'], 'violation/files');
            }
        } catch (\Exception $exception) {
            throw  $exception;
        }
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

}
