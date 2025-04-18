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
        return  User::query()->findOrFail($id);
    }

    public function update($id, array $data)
    {
        try {
            $user = $this->find($id);
            if (!$user) throw new \Exception("User not found");
            $user->update($data);
            return $user;
        }catch (\Exception $exception){
            throw  $exception;
        }
    }

    public function delete($id){

    }
}
