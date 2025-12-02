<?php

namespace Modules\Apartment\Services;

use Modules\Apartment\Contracts\ProgramRepositoryInterface;
use Modules\Apartment\Http\Requests\ProgramRequest;

class ProgramService
{
    public function __construct(public ProgramRepositoryInterface $repository){}

    public function getAll()
    {
        return $this->repository->getAll();
    }

    public function create(ProgramRequest $request)
    {
        return $this->repository->create($request->validated());
    }

    public function findById(int $id)
    {
        return $this->repository->findById($id);
    }
}
