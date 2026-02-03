<?php

namespace Modules\Apartment\Services;

use Modules\Apartment\Contracts\ProgramRepositoryInterface;
use Modules\Apartment\Http\Requests\ProgramEditRequest;
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

    public function update($id, ProgramRequest $request)
    {
        try {
            return  $this->repository->update($id, $request->validated());
        }catch (\Exception $exception){
            throw $exception;
        }
    }


    public function findById(int $id)
    {
        return $this->repository->findById($id);
    }
}
