<?php

namespace Modules\Apartment\Repositories;

use Modules\Apartment\Contracts\ProgramObjectInterface;
use Modules\Apartment\Models\ProgramObject;
use Modules\Apartment\Models\ProgramObjectChecklist;

class ProgramObjectRepository implements ProgramObjectInterface
{

    public function __construct(public ProgramObject $model){}

    public function getAll($filters)
    {
        return $this->model;
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
        }catch (\Exception $e){
            throw $e;
        }
    }
}
