<?php

namespace App\Contracts;

interface UserRepositoryInterface
{
    public function all($user, $roleId);
    public function find($id);
    public function findByPin($pin);

    public function create(?array $data);

    public function update($id, array $data);

    public function delete($id);

    public function search($query, $filters);


}
