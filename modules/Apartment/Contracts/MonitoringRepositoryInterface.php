<?php

namespace Modules\Apartment\Contracts;

interface MonitoringRepositoryInterface
{
    public function all($user, $roleId);

    public function filter($query, $filters);

    public function findById($id);

    public function create($data);

    public function update($id, $data);
    public function createSecond($id, $data);

    public function confirm($id);
    public function reject($id);
    public function createThird($id, $data);
    public function changeStatus($id, $status);

    public function attach($userId, $monitoringId);
}
