<?php

namespace Modules\Apartment\Contracts;

interface LetterInterface
{
    public function all($user, $roleId);

    public function findById(int $id);

    public function change(int $id, $data);

    public function create(array $data);

    public function getLetter($id);

    public function receipt($id);
}
