<?php

namespace Modules\Water\Contracts;

interface ProtocolRepositoryInterface
{
    public function all();

    public function findById(?int $id);

    public function createFirst(?array $data);

    public function createSecond(?int $id, ?array $data);

    public function createThird(?int $id, ?array $data);
}
