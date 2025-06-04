<?php

namespace Modules\Apartment\Repositories;

use App\Enums\UserRoleEnum;
use App\Services\FileService;
use Illuminate\Support\Facades\DB;
use Modules\Apartment\Const\MonitoringHistoryType;
use Modules\Apartment\Contracts\MonitoringRepositoryInterface;
use Modules\Apartment\Enums\MonitoringStatusEnum;
use Modules\Apartment\Models\Monitoring;
use Modules\Apartment\Models\Regulation;
use Modules\Apartment\Models\Violation;
use Modules\Water\Const\Step;
use Modules\Water\Models\Protocol;
use Modules\Water\Services\HistoryService;

class MonitoringRepository implements MonitoringRepositoryInterface
{

    private HistoryService $historyService;

    public function __construct(protected FileService $fileService)
    {
        $this->historyService = new HistoryService('monitoring_histories');
    }

    public function all($user, $roleId)
    {
        try {
            switch ($roleId) {
                case UserRoleEnum::APARTMENT_INSPECTOR->value:
                    return Monitoring::query()->where('user_id', $user->id);
                case UserRoleEnum::APARTMENT_VIEWER->value:
                    return Monitoring::query();
                case UserRoleEnum::APARTMENT_MANAGER->value:
                    return Monitoring::query()->where('region_id', $user->region_id);
                default:
                    return Monitoring::query()->whereRaw('1 = 0');
            }
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function filter($query, $filters)
    {
        try {
            return $query
                ->when(isset($filters['status']), function ($query) use ($filters) {
                    $query->where('monitoring_status_id', $filters['status']);
                })
                ->when(isset($filters['type']), function ($query) use ($filters) {
                    $query->where('type', $filters['type']);
                });
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function findById($id)
    {
        try {
            return Monitoring::query()->findOrFail($id);
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function update($id, $data)
    {
        try {
            $monitoring = $this->findById($id);
            $monitoring->update($data);
            return $monitoring;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function create($data)
    {
        try {
            $monitoring = Monitoring::query()->create($data);
            return $monitoring;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function createSecond($id, $data)
    {
        DB::beginTransaction();
        try {
            $originalMonitoring = $this->findById($id);

            $results = [];
            if ($data['monitoring_status_id'] == MonitoringStatusEnum::CONFIRM_DEFECT->value) {
                $originalMonitoring->update([
                    'monitoring_status_id' => $data['monitoring_status_id'],
                    'additional_comment' => $data['additional_comment'] ?? null,
                    'step' => $data['step'],
                ]);

                $this->createHistory($originalMonitoring, MonitoringHistoryType::VIOLATION_NOT_DETECTED);

                if (isset($data['additional_files'])) {
                    $this->uploadFiles($originalMonitoring, 'additional_files', $data['additional_files'], 'monitoring/files');
                }
            } else {
                $regulations = $data['regulations'];
                foreach ($regulations as $index => $item) {
                    if ($index === 0) {
                        $originalMonitoring->update([
                            'monitoring_status_id' => $data['monitoring_status_id'],
                            'additional_comment' => $data['additional_comment'] ?? null,
                            'step' => $data['step'],
                        ]);

                        $regulation = Regulation::create([
                            'monitoring_id' => $originalMonitoring->id,
                            'place_id' => $item['place_id'],
                            'violation_type_id' => $item['violation_type_id'],
                            'comment' => $item['comment'],
                            'user_type' => $item['user_type'],
                            'pin' => $item['pin'] ?? null,
                            'inn' => $item['inn'] ?? null,
                            'organization_name' => $item['organization_name'] ?? null,
                            'company_id' => $item['company_id'] ?? null,
                            'birth_date' => $item['birth_date'] ?? null,
                            'fish' => $item['fish'] ?? null,
                            'phone' => $item['phone'] ?? null,
                        ]);

                        $this->saveImages($regulation, $item['images']);
                        $this->createHistory($originalMonitoring, MonitoringHistoryType::VIOLATION_DETECTED);


                        $results[] = $originalMonitoring;
                    } else {
                        $newMonitoring = $originalMonitoring->replicate();
                        $newMonitoring->monitoring_status_id = $data['monitoring_status_id'];
                        $newMonitoring->additional_comment = $data['additional_comment'] ?? null;
                        $newMonitoring->step = $data['step'];
                        $newMonitoring->save();
                        $this->createHistory($newMonitoring, MonitoringHistoryType::CREATE_FIRST);

                        foreach ($originalMonitoring->images as $image) {
                            $newImage = $image->replicate();
                            $newImage->imageable_id = $newMonitoring->id;
                            $newImage->save();
                        }

                        foreach ($originalMonitoring->documents as $document) {
                            $newDocument = $document->replicate();
                            $newDocument->documentable_id = $newMonitoring->id;
                            $newDocument->save();
                        }

                        $regulation = Regulation::create([
                            'monitoring_id' => $newMonitoring->id,
                            'place_id' => $item['place_id'],
                            'violation_type_id' => $item['violation_type_id'],
                            'comment' => $item['comment'],
                            'user_type' => $item['user_type'],
                            'pin' => $item['pin'] ?? null,
                            'birth_date' => $item['birth_date'] ?? null,
                            'fish' => $item['fish'] ?? null,
                            'phone' => $item['phone'] ?? null,
                            'inn' => $item['inn'] ?? null,
                            'organization_name' => $item['organization_name'] ?? null,
                            'company_id' => $item['company_id'] ?? null,
                        ]);
                        $this->createHistory($newMonitoring, MonitoringHistoryType::VIOLATION_DETECTED);

                        $this->saveImages($regulation, $item['images']);
                        $results[] = $newMonitoring;
                    }
                }
            }


            DB::commit();
            return $results;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function confirm($id)
    {
        try {
            $monitoring = $this->findById($id);
            $monitoring->update([
                'monitoring_status_id' => MonitoringStatusEnum::NOT_DEFECT->value,
            ]);
            return $monitoring;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function reject($id)
    {
        try {
            $monitoring = $this->findById($id);
            $monitoring->update([
                'monitoring_status_id' => MonitoringStatusEnum::ENTER_RESULT->value,
            ]);

            //history yoziladi
            return $monitoring;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function createThird($id, $data)
    {
        DB::beginTransaction();
        try {
            $monitoring = $this->findById($id);
            $this->createHistory($monitoring, MonitoringHistoryType::REGULATION_FORMED);
            $monitoring->update(['monitoring_status_id' => MonitoringStatusEnum::FORMED->value, 'type' => 2, 'step' => Step::THREE]);
            $violation = new Violation();
            $violation->regulation_id = $data['regulation_id'];
            $violation->monitoring_id = $data['monitoring_id'];
            $violation->type = $data['type'];
            $violation->desc = $data['description'];
            $violation->deadline = $data['deadline'];
            $violation->save();
            DB::commit();
            return $violation;

        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    public function attach($userId, $monitoringId)
    {
        try {
           $monitoring = $this->findById($monitoringId);
           $monitoring->update([
               'user_id' => $userId,
               'monitoring_status_id' => MonitoringStatusEnum::ENTER_RESULT->value
           ]);
           return $monitoring;
        }catch (\Exception $exception){
            throw $exception;
        }
    }

    public function changeStatus($id, $status)
    {
        try {
            $monitoring = $this->findById($id);
            $monitoring->update(['monitoring_status_id' => $status]);
            return $monitoring;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    private function saveImages(Regulation $regulation, ?array $images)
    {
        $paths = array_map(fn($image) => $this->fileService->uploadImage($image, 'regulation/images'), $images);
        $regulation->images()->createMany(array_map(fn($path) => ['url' => $path], $paths));
    }

    private function uploadFiles(Monitoring $monitoring, string $column, ?array $files, $filePath)
    {
        if (!empty($files)) {
            $paths = array_map(fn($file) => $this->fileService->uploadFile($file, $filePath), $files);
            $monitoring->$column = json_encode(array_map(fn($path) => ['url' => $path], $paths));
            $monitoring->save();
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
