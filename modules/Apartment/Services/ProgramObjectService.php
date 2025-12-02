<?php

namespace Modules\Apartment\Services;

use Modules\Apartment\Contracts\ProgramObjectInterface;
use Modules\Apartment\Http\Requests\AttachObjectRequest;
use Modules\Apartment\Http\Requests\ProgramObjectRequest;

class ProgramObjectService
{
    public function __construct(public ProgramObjectInterface $repository){}

    public function findById(int $id)
    {
        return $this->repository->findById($id);
    }

    public function getAll($filters)
    {
        return $this->repository->getAll($filters);
    }

    public function create(ProgramObjectRequest $request)
    {
        return $this->repository->create($request->validated());
    }

    public function attach(AttachObjectRequest $request)
    {
        return $this->repository->attach($request->validated());
    }
}
