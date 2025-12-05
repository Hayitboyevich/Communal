<?php

namespace Modules\Apartment\Repositories;

use App\Services\FileService;
use Illuminate\Support\Facades\DB;
use Modules\Apartment\Const\ObjectChecklistStatus;
use Modules\Apartment\Contracts\ProgramMonitoringInterface;
use Modules\Apartment\Contracts\ProgramRepositoryInterface;
use Modules\Apartment\Http\Requests\ProgramMonitoringRequest;
use Modules\Apartment\Models\ProgramMonitoring;
use Modules\Apartment\Models\ProgramObjectChecklist;
use Modules\Apartment\Models\ProgramRegulation;

class ProgramMonitoringRepository implements ProgramMonitoringInterface
{

    public function __construct(public ProgramMonitoring $model, public FileService $fileService){}

    public function findById($id)
    {
        return $this->model
            ->with([
               'object:id,region_id,district_id,apartment_number',
               'object.region:id,name_uz',
               'object.district:id,name_uz',
                'images',
                'user',
                'role'
            ])
            ->withCount('regulations')
            ->find($id);
    }

    public function getAll()
    {
        return $this->model
            ->with([
                'object:id,region_id,district_id,apartment_number',
                'object.region:id,name_uz,soato',
                'object.district:id,name_uz,soato',
                'images',
                'user',
                'role'
            ])
            ->withCount('regulations');
    }

    public function create(ProgramMonitoringRequest $request, $user, $roleId)
    {
        DB::beginTransaction();
        try {
            $status = 1;
            $monitoring = $this->model->create([
                'lat' => $request->lat,
                'long' => $request->long,
                'program_object_id' => $request->program_object_id,
                'user_id' => $user->id,
                'role_id' => $roleId,
            ]);

            if ($request->images) {
                $this->saveImages($monitoring, $request->images, 'images/object-monitoring');
            }

            foreach ($request->checklists as $item) {
                $regulation = ProgramRegulation::query()->create([
                   'program_monitoring_id'  => $monitoring->id,
                    'program_object_id' => $request->program_object_id,
                    'checklist_id' => $item['checklist_id'],
                    'program_id' => $item['program_id'],
                    'program_object_checklist_id' => $item['program_object_checklist_id'],
                    'plan' => $item['plan'],
                    'all' => $item['all'],
                    'need_repair' => $item['need_repair'],
                    'done' => $item['done'],
                    'progress' => $item['progress'],
                    'extra' => $item['extra'],
                ]);

                if ($regulation->plan > $regulation->done) {
                    $status = ObjectChecklistStatus::PROGRESS;
                }elseif ($regulation->plan == $regulation->done){
                    $status = ObjectChecklistStatus::DONE;
                }elseif ($regulation->plan < $regulation->need_repair && $regulation->plan == $regulation->done){
                    $status = ObjectChecklistStatus::NEED_REPAIR;
                }

                $regulation->objectChecklist()->update(['status' => $status]);
                
                if ($item['images']){
                    $this->saveImages($regulation, $item['images'], 'images/object-regulation');
                }
            }
            DB::commit();
            return $monitoring;

        }catch (\Exception $exception){
            DB::rollBack();
            throw  $exception;
        }
    }

//    private function saveImages($model, $images, $path)
//    {
//        if (!empty($images)) {
//            foreach ($images as $image) {
//                $path = $image->store($path, 'public');
//                $model->images()->create(['url' => $path]);
//            }
//        }
//    }
    private function saveImages($model, ?array $images, $filePath)
    {
        $paths = array_map(fn($image) => $this->fileService->uploadImage($image, $filePath), $images);
        $model->images()->createMany(array_map(fn($path) => ['url' => $path], $paths));
    }

}
