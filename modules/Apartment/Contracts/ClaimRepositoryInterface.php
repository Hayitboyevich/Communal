<?php

namespace Modules\Apartment\Contracts;

interface ClaimRepositoryInterface
{
    public function findById($id);

    public function all($user, $roleId, $filters);

    public function create($data);

    public function update($id, $data);
}
