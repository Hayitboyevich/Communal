<?php

namespace Modules\Apartment\Repositories;

use App\Services\FileService;
use Illuminate\Support\Facades\DB;
use Modules\Apartment\Contracts\MonitoringRepositoryInterface;
use Modules\Apartment\Models\Monitoring;
use Modules\Apartment\Models\Regulation;

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
            $monitoring = $this->findById($id);
            $monitoring->update([
                'monitoring_status_id' => $data['monitoring_status_id'],
            ]);

            foreach ($data['regulations'] as $item) {
                $regulation = Regulation::query()->create([
                    'monitoring_id' => $monitoring->id,
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
            }
            DB::commit();
            return $monitoring;
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    private function saveImages(Regulation $regulation, ?array $images)
    {
        $paths = array_map(fn($image) => $this->fileService->uploadImage($image, 'regulation/images'), $images);
        $regulation->images()->createMany(array_map(fn($path) => ['url' => $path], $paths));
    }
}
