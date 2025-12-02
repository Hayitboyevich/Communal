<?php

namespace Modules\Apartment\Repositories;

use Modules\Apartment\Contracts\ProgramMonitoringInterface;
use Modules\Apartment\Contracts\ProgramRepositoryInterface;
use Modules\Apartment\Models\ProgramMonitoring;

class ProgramMonitoringRepository implements ProgramMonitoringInterface
{

    public function __construct(public ProgramMonitoring $model){}

    public function findById($id)
    {
        return $this->model->find($id);
    }

    public function getAll()
    {
        return $this->model;
    }

    public function create(?array $data)
    {
        return $this->model->create($data);
    }
}
