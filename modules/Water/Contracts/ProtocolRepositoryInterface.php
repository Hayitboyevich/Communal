<?php

namespace Modules\Water\Contracts;

interface ProtocolRepositoryInterface
{
    public function all();

    public function findById(?int $id);

    public function create(?array $data);

    public function update(?int $id, ?array $data);

}
