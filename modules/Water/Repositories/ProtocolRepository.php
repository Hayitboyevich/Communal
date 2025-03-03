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
        return Protocol::query()->findOrFail($id);
    }

    public function create(?array $data)
    {
        return  Protocol::query()->create($data);
    }

    public function update(?int $id, ?array $data)
    {
        $protocol = Protocol::query()->findOrFail($id);
        $protocol->update($data);
        return $protocol;
    }
}
