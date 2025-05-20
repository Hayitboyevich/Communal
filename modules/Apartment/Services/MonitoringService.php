<?php

namespace Modules\Apartment\Services;

use App\Http\Requests\MonitoringCreateSecondRequest;
use App\Services\FileService;
use Modules\Apartment\Const\MonitoringHistoryType;
use Modules\Apartment\Contracts\MonitoringRepositoryInterface;
use Modules\Apartment\Enums\MonitoringStatusEnum;
use Modules\Apartment\Http\Requests\MonitoringChangeStatusRequest;
use Modules\Apartment\Http\Requests\MonitoringCreateRequest;
use Modules\Apartment\Http\Requests\ViolationRequest;
use Modules\Apartment\Models\MonitoringHistory;
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
        try {
            $monitoring = $this->repository->create($request->except('images', 'docs'));
            $this->saveImages($monitoring, $request['images'], 'monitoring/images');
            $this->saveFiles($monitoring, $request['docs'], 'monitoring/files');
            $this->createHistory($monitoring, MonitoringHistoryType::CREATE_FIRST);
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
            $monitoring = $this->repository->confirm($id);
            $this->createHistory($monitoring, MonitoringHistoryType::CONFIRM_DEFECT);
        } catch (\Exception $exception) {
            throw  $exception;
        }
    }

    public function reject($id, Request $request)
    {
        try {
            $monitoring = $this->repository->reject($id);
            $historyId = $this->createHistory($monitoring, MonitoringHistoryType::REJECT_DEFECT, $request['comment']);
            $monitoringHistory = MonitoringHistory::query()->find($historyId);
            if (isset($request['images'])){
                $this->saveImages($monitoringHistory, $request['images'], 'monitoring-history/images');
            }

            if (isset($request['docs'])){
                $this->saveFiles($monitoringHistory, $request['docs'], 'monitoring-history/files');
            }

        } catch (\Exception $exception) {
            throw  $exception;
        }
    }

    public function changeStatus($id, MonitoringChangeStatusRequest $request)
    {
        try {
            $this->repository->changeStatus($id, $request->monitoring_status_id);
            return  $this->repository->update($id, $request->only(['is_administrative', 'send_court']));
        }catch (\Exception $exception){
            throw  $exception;
        }
    }

    public function confirmRegulation($id)
    {
        try {
            $monitoring = $this->repository->changeStatus($id, MonitoringStatusEnum::DONE->value);
            $this->createHistory($monitoring, MonitoringHistoryType::CONFIRMED);
            return $monitoring;
        }catch (\Exception $exception){
            throw  $exception;
        }
    }

    public function rejectRegulation($id, Request $request)
    {
        try {
            $monitoring =  $this->repository->changeStatus($id, MonitoringStatusEnum::FORMED->value);
            $this->createHistory($monitoring, type:MonitoringHistoryType::REJECT, comment: $request['comment']);
            return $monitoring;
        }catch (\Exception $exception){
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
            status: $monitoring->monitoring_status_id,
            type: $type,
            date: null,
            comment: $comment,
            additionalInfo: $meta
        );
    }

}
