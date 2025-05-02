<?php

namespace Modules\Apartment\Services;

use App\Http\Requests\MonitoringCreateSecondRequest;
use App\Services\FileService;
use Modules\Apartment\Contracts\MonitoringRepositoryInterface;
use Modules\Apartment\Http\Requests\MonitoringCreateRequest;
use Modules\Apartment\Models\Monitoring;
use Modules\Water\Models\Protocol;
use Modules\Water\Services\HistoryService;

class MonitoringService
{
    private HistoryService $historyService;

    public function __construct(
        protected MonitoringRepositoryInterface $repository,
        protected FileService                 $fileService
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
            $this->saveImages($monitoring, $request['images']);
            $this->saveFiles($monitoring, $request['docs']);
            return $monitoring;
        }catch (\Exception $exception){
            throw  $exception;
        }
    }

    public function createSecond($id, MonitoringCreateSecondRequest $request)
    {
        try {
            return $this->repository->createSecond($id, $request->all());
        }catch (\Exception $exception){
            throw  $exception;
        }
    }

    private function saveImages(Monitoring $monitoring, ?array $images)
    {
        $paths = array_map(fn($image) => $this->fileService->uploadImage($image, 'monitoring/images'), $images);
        $monitoring->images()->createMany(array_map(fn($path) => ['url' => $path], $paths));
    }
    private function saveFiles(Monitoring $monitoring, ?array $files)
    {
        if (!empty($files)) {
            $paths = array_map(fn($file) => $this->fileService->uploadImage($file, 'monitoring/files'), $files);
            $monitoring->documents()->createMany(array_map(fn($path) => ['url' => $path], $paths));
        }
    }

}
