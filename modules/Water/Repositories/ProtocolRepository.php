<?php

namespace Modules\Water\Repositories;

use Modules\Water\Contracts\ProtocolRepositoryInterface;
use Modules\Water\Models\Protocol;

class ProtocolRepository implements ProtocolRepositoryInterface
{
    public function all()
    {
        return Protocol::query();
    }

    public function findById(?int $id)
    {
        try {
            return Protocol::query()->findOrFail($id);
        }catch (\Exception $exception){
            throw $exception;
        }
    }

    public function createFirst(?array $data)
    {
        $protocol = Protocol::query()->create($data);

        return $protocol;
    }

    public function createSecond(?int $id, ?array $data)
    {

    }

    public function createThird(?int $id, ?array $data)
    {

    }
}
