<?php

namespace Modules\Apartment\Repositories;

use App\Enums\UserRoleEnum;
use Modules\Apartment\Contracts\ProgramObjectInterface;
use Modules\Apartment\Models\ProgramObject;
use Modules\Apartment\Models\ProgramObjectChecklist;

class ProgramObjectRepository implements ProgramObjectInterface
{

    public function __construct(public ProgramObject $model)
    {
    }

    public function getAll($user, $roleId)
    {
        switch ($roleId) {
            case UserRoleEnum::APARTMENT_MANAGER->value:
            case UserRoleEnum::REG_VIEWER->value:
            case UserRoleEnum::APARTMENT_INSPECTOR->value:
                return $this->model::query()->where('region_id', $user->region_id);
            case UserRoleEnum::APARTMENT_VIEWER->value:
                return $this->model::query();
            default:
                return $this->model::query()->whereRaw('1 = 0');
        }
    }

    public function search($filters, $query)
    {
        return $query
            ->when(!empty($filters['work_type']), function ($q) use ($filters) {
                $q->where('work_type_id', $filters['work_type']);
            })
            ->when(!empty($filters['apartment']), function ($q) use ($filters) {
                $q->where('apartment_number', $filters['apartment']);
            })
            ->when(!empty($filters['quarter']), function ($q) use ($filters) {
                $q->where('quarter_name', 'ILIKE', '%' . $filters['quarter'] . '%');
            })
            ->when(!empty($filters['district_id']), function ($q) use ($filters) {
                $q->where('district_id', $filters['district_id']);
            })
            ->when(!empty($filters['region_id']), function ($q) use ($filters) {
                $q->where('region_id', $filters['region_id']);
            });
    }


    public function findById($id)
    {
        return $this->model->find($id);
    }

    public function create($data)
    {
        return $this->model->create($data);
    }

    public function attach($data)
    {
        try {
            $object = $this->findById($data['program_object_id']);

            if (!$object) throw new \Exception("Program object not found");

            $attachData = [];

            foreach ($data['checklist'] as $item) {
                $attachData[$item['checklist_id']] = [
                    'plan' => $item['plan'],
                    'unit' => $item['unit'],
                    'program_id' => $item['program_id'],
                ];
            }

            $object->checklists()->attach($attachData);

            return $object;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
