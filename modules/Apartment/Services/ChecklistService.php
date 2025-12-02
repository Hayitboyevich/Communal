<?php

namespace Modules\Apartment\Services;

use Modules\Apartment\Contracts\ChecklistRepositoryInterface;
use Modules\Apartment\Http\Requests\ChecklistRequest;

class ChecklistService
{
    public function __construct(public ChecklistRepositoryInterface $repository){}

    public function getAll($filters)
    {
        return $this->repository->getAll($filters);
    }

    public function findById(int $id)
    {
        return $this->repository->findById($id);
    }

    public function create(ChecklistRequest $request)
    {
        return $this->repository->create($request->validated());
    }
}
