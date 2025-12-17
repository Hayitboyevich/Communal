<?php

namespace Modules\Apartment\Repositories;

use App\Services\FileService;
use Illuminate\Support\Facades\DB;
use Modules\Apartment\Const\ObjectChecklistStatus;
use Modules\Apartment\Const\ProgramObjectStatusList;
use Modules\Apartment\Contracts\ProgramMonitoringInterface;
use Modules\Apartment\Contracts\ProgramRepositoryInterface;
use Modules\Apartment\Http\Requests\ProgramMonitoringRequest;
use Modules\Apartment\Models\ProgramMonitoring;
use Modules\Apartment\Models\ProgramObject;
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
            $data = $request->validated();
            $status = 1;
            $monitoring = $this->model->create([
                'lat' => $data['lat'],
                'long' => $data['long'],
                'program_object_id' => $data['program_object_id'],
                'user_id' => $user->id,
                'role_id' => $roleId,
            ]);

            if (!empty($data['images'])) {
                $this->saveImages($monitoring, $request->images, 'images/object-monitoring');
            }

            foreach ($data['checklists'] as $item) {
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

                $object = ProgramObject::query()->find($request->program_object_id);

                $objectChecklistCount =  $object->checklists()->count();
                $doneCount = $object->checklists()->whereIn('status', [ObjectChecklistStatus::DONE])->count();
                $needRepairCount = $object->checklists()->whereIn('status', [ObjectChecklistStatus::NEED_REPAIR])->count();
                $processCount = $object->checklists()->whereIn('status', [ObjectChecklistStatus::PROGRESS])->count();

                if ($objectChecklistCount == $doneCount){
                    $object->update(['status' => ProgramObjectStatusList::DONE]);
                }

                if ($needRepairCount){
                    $object->update(['status' => ProgramObjectStatusList::NEED_REPAIR]);
                }

                if ($processCount){
                    $object->update(['status' => $processCount]);
                }

                if (!empty($item['images'])){
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
