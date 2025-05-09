<?php

namespace Modules\Apartment\Contracts;

interface MonitoringRepositoryInterface
{
    public function all($user, $roleId);

    public function filter($query, $filters);

    public function findById($id);

    public function create($data);
    public function createSecond($id, $data);
}
