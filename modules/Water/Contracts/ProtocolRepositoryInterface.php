<?php

namespace Modules\Water\Contracts;

use App\Models\User;

interface ProtocolRepositoryInterface
{
    public function all($user, $roleId);

    public function findById(?int $id);

    public function create(?array $data);

    public function update(?int $id, ?array $data);

    public function attach(?array $data,  $user, ?int $roleId);

    public function filter($query, $filters);

}
