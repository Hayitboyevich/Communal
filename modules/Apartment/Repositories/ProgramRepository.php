<?php

namespace Modules\Apartment\Repositories;

use Modules\Apartment\Contracts\ProgramRepositoryInterface;
use Modules\Apartment\Models\Program;

class ProgramRepository implements ProgramRepositoryInterface
{
    public function __construct(public Program $program){}

    public function findById(int $id)
    {
        return $this->program->find($id);
    }

    public function getAll()
    {
        return $this->program;
    }

    public function create(?array $data)
    {
        return $this->program->create($data);
    }
}
