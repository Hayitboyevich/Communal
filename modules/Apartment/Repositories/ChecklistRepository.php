<?php

namespace Modules\Apartment\Repositories;

use Modules\Apartment\Contracts\ChecklistRepositoryInterface;
use Modules\Apartment\Models\Checklist;

class ChecklistRepository implements ChecklistRepositoryInterface
{

    public function __construct(public Checklist $model)
    {
    }

    public function getAll($data)
    {
        return $this->model;
    }

    public function findById(int $id)
    {
        return $this->model->find($id);
    }

    public function create(?array $data)
    {
        return $this->model->create($data);
    }
}
