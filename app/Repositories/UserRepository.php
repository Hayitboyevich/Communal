<?php

namespace App\Repositories;

use App\Contracts\UserRepositoryInterface;
use App\Models\User;

class UserRepository implements UserRepositoryInterface
{
    public function all()
    {
        return User::query();
    }

    public function create(?array $data)
    {
        try {
            return  User::query()->create($data);
        }catch (\Exception $exception){
            throw  $exception;
        }
    }

    public function find($id)
    {
        $user = User::query()->find($id);
    }

    public function update($id, array $data){

    }

    public function delete($id){

    }
}
