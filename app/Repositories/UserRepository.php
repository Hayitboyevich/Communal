<?php

namespace App\Repositories;

use App\Contracts\UserRepositoryInterface;
use App\Enums\UserRoleEnum;
use App\Models\User;

class UserRepository implements UserRepositoryInterface
{
    public function all($user, $roleId)
    {
        switch ($roleId) {
            case UserRoleEnum::HR->value:
                return User::query()->whereHas('roles', function ($query) use ($user) {
                    $query->whereIn('role_id', [UserRoleEnum::INSPECTOR->value, UserRoleEnum::MANAGER->value]);
                });
            case UserRoleEnum::APARTMENT_HR->value:
                return User::query()->whereHas('roles', function ($query) use ($user) {
                    $query->whereIn('role_id', [UserRoleEnum::APARTMENT_INSPECTOR->value, UserRoleEnum::APARTMENT_MANAGER->value]);
                });
            case UserRoleEnum::WATER_HR->value:
                return User::query()->whereHas('roles', function ($query) use ($user) {
                    $query->whereIn('role_id', [UserRoleEnum::WATER_INSPECTOR->value]);
                });
            case UserRoleEnum::MANAGER->value:
                return User::query()->where('region_id', $user->region_id);

            case UserRoleEnum::RES_VIEWER->value:
                return User::query()->whereHas('roles', function ($query) use ($user) {
                    $query->whereIn('role_id', [UserRoleEnum::INSPECTOR->value]);
                });

            default:
                return User::query()->whereRaw('1 = 0');
        }
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
