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
            return User::query()->create($data);
        } catch (\Exception $exception) {
            throw  $exception;
        }
    }

    public function search($query, $filters)
    {
        return $query
            ->when(isset($filters['search']), function ($query) use ($filters) {
                $query->searchByFullName($filters['search']);
            })
            ->when(isset($filters['region_id']), function ($query) use ($filters) {
                $query->where('region_id', $filters['region_id']);
            })
            ->when(isset($filters['district_id']), function ($query) use ($filters) {
                $query->where('district_id', $filters['district_id']);
            })
            ->when(isset($filters['status']), function ($query) use ($filters) {
                $query->where('user_status_id', $filters['status']);
            })
            ->when(isset($filters['role_id']), function ($query) use ($filters) {
                $query->where('role_id', $filters['role_id']);
            })
            ->when(isset($filters['full_name']), function ($query) use ($filters) {
                $query->searchByFullName($filters['full_name']);
            })
            ->when(isset($filters['phone']), function ($query) use ($filters) {
                $query->where('phone', $filters['phone']);
            });
    }

    public function find($id)
    {
        return User::query()->findOrFail($id);
    }

    public function update($id, array $data)
    {
        try {
            $user = $this->find($id);
            if (!$user) throw new \Exception("User not found");
            $user->update($data);
            return $user;
        } catch (\Exception $exception) {
            throw  $exception;
        }
    }

    public function delete($id)
    {

    }
}
