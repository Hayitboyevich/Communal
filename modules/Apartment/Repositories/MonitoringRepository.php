<?php

namespace Modules\Apartment\Repositories;

use App\Services\FileService;
use Illuminate\Support\Facades\DB;
use Modules\Apartment\Contracts\MonitoringRepositoryInterface;
use Modules\Apartment\Enums\MonitoringStatusEnum;
use Modules\Apartment\Models\Monitoring;
use Modules\Apartment\Models\Regulation;
use Modules\Apartment\Models\Violation;
use Modules\Water\Models\Protocol;

class MonitoringRepository implements MonitoringRepositoryInterface
{
    public function __construct(protected FileService $fileService)
    {
    }

    public function all($user, $roleId)
    {
        try {
            return Monitoring::query();
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function filter($query, $filters)
    {
        try {
            return $query;
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
            if($data['monitoring_status_id'] == MonitoringStatusEnum::CONFIRM_DEFECT->value)
            {
                $originalMonitoring->update([
                    'monitoring_status_id' => $data['monitoring_status_id'],
                    'additional_comment' => $data['additional_comment'] ?? null,
                    'step' => $data['step'],
                ]);

                if(isset($data['additional_files'])){
                    $this->uploadFiles($originalMonitoring, 'additional_files', $data['additional_files'], 'monitoring/files');
                }
            }else{
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
                            'pin' => $item['pin'],
                            'birth_date' => $item['birth_date'],
                            'fish' => $item['fish'],
                            'phone' => $item['phone'],
                            'description' => $item['description'],
                        ]);

                        $this->saveImages($regulation, $item['images']);

                        $results[] = $originalMonitoring;
                    } else {
                        $newMonitoring = $originalMonitoring->replicate();
                        $newMonitoring->monitoring_status_id = $data['monitoring_status_id'];
                        $newMonitoring->additional_comment = $data['additional_comment'] ?? null;
                        $newMonitoring->step = $data['step'];
                        $newMonitoring->save();

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
                            'pin' => $item['pin'],
                            'birth_date' => $item['birth_date'],
                            'fish' => $item['fish'],
                            'phone' => $item['phone'],
                            'description' => $item['description'],
                        ]);

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
        }catch (\Exception $exception){
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
        } catch (\Exception $exception){
            throw $exception;
        }
    }

    public function violation($data)
    {
        try {
            $violation = new Violation();
            $violation->regulation_id = $data['regulation_id'];
            $violation->type = $data['type'];
            $violation->desc = $data['description'];
            $violation->deadline = $data['deadline'];
            $violation->save();
            return $violation;

        }catch (\Exception $exception){
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
}
