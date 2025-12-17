<?php

namespace Modules\Apartment\Services;

use Modules\Apartment\Contracts\LetterInterface;
use Modules\Apartment\Http\Requests\LetterRequest;

class LetterService
{
    public function __construct(public LetterInterface $repository){}

    public function getAll()
    {
        return $this->repository->all();
    }

    public function findById(int $id)
    {
        return $this->repository->findById($id);
    }

    public function create(LetterRequest $request)
    {
        return $this->repository->create($request->validated());
    }

    public function change($id, $data)
    {
        return $this->repository->change($id, $data);
    }

    public function getHybrid($id)
    {
        return $this->repository->getLetter($id);
    }
}
