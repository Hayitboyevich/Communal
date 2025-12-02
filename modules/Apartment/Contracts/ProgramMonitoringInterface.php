<?php

namespace Modules\Apartment\Contracts;

use Modules\Apartment\Http\Requests\ProgramMonitoringRequest;

interface ProgramMonitoringInterface
{
    public function getAll();

    public function findById(?int $id);

    public function create(ProgramMonitoringRequest $request, $user, $roleId);
}
