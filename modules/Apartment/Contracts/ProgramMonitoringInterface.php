<?php

namespace Modules\Apartment\Contracts;

interface ProgramMonitoringInterface
{
    public function getAll();

    public function findById(?int $id);

    public function create(?array $data);
}
