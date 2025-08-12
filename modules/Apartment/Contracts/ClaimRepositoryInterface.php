<?php

namespace Modules\Apartment\Contracts;

interface ClaimRepositoryInterface
{
    public function findById($id);

    public function all();

    public function create($data);

    public function update($id, $data);
}
