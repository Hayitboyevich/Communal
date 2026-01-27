<?php

namespace Modules\Apartment\Contracts;

interface ProgramObjectInterface
{
    public function findById(int $id);

    public function getAll();

    public function search($filters, $query);

    public function create(?array $data);

    public function attach(?array $data);
}
