<?php

namespace Modules\Apartment\Contracts;

interface ProgramRepositoryInterface
{
    public function getAll();

    public function findById(int $id);

    public function create(?array $data);
}
