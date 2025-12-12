<?php

namespace Modules\Apartment\Contracts;

interface LetterInterface
{
    public function all();

    public function findById(int $id);

    public function create(array $data);
}
