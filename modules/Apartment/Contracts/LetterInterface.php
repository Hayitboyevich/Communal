<?php

namespace Modules\Apartment\Contracts;

interface LetterInterface
{
    public function all();

    public function findById(int $id);

    public function change(int $id, $data);

    public function create(array $data);
}
