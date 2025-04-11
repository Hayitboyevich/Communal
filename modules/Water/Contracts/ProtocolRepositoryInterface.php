<?php

namespace Modules\Water\Contracts;

interface ProtocolRepositoryInterface
{
    public function all($user, $roleId);

    public function findById(?int $id);

    public function create(?array $data);

    public function update(?int $id, ?array $data);

}
