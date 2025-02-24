<?php

namespace Modules\Water\Services;

use Modules\Water\Contracts\ProtocolRepositoryInterface;

class ProtocolService
{
    public function __construct(
        protected ProtocolRepositoryInterface $repository
    ){}

    public function getAll()
    {
        return $this->repository->all();
    }

    public function createFirst(?array $data)
    {
        return  $this->repository->createFirst($data);
    }

    public function createSecond(?int $id, ?array $data)
    {
        return $this->repository->createSecond($id, $data);
    }

    public function createThird(?int $id, ?array $data)
    {
        return $this->repository->createThird($id, $data);
    }
}
