<?php

namespace Modules\Apartment\Services;

use Modules\Apartment\Contracts\ProgramMonitoringInterface;
use Modules\Apartment\Http\Requests\ProgramMonitoringRequest;

class ProgramMonitoringService
{
    public function __construct(public ProgramMonitoringInterface $repository){}

    public function getAll()
    {
        return $this->repository->getAll();
    }

    public function create(ProgramMonitoringRequest $request, $user, $roleId)
    {
        return $this->repository->create($request, $user, $roleId);
    }

    public function findById(int $id)
    {
        return $this->repository->findById($id);
    }

    private function saveImages($files)
    {

    }
}
