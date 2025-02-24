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

    public function createFirst(?array $data)
    {

    }

    public function createSecond(?int $id, ?array $data)
    {

    }

    public function createThird(?int $id, ?array $data)
    {

    }
}
