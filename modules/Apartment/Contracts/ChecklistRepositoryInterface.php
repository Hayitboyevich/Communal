<?php

namespace Modules\Apartment\Contracts;

interface ChecklistRepositoryInterface
{
    public function getAll(?array $data);

    public function findById(int $id);

    public function create(?array $data);
}
