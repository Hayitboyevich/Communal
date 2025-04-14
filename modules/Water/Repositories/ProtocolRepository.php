<?php

namespace Modules\Water\Repositories;

use App\Enums\UserRoleEnum;
use Modules\Water\Contracts\ProtocolRepositoryInterface;
use Modules\Water\Models\Protocol;

class ProtocolRepository implements ProtocolRepositoryInterface
{
    public function all($user, $roleId)
    {
        switch ($roleId) {
            case UserRoleEnum::INSPECTOR->value:
                return Protocol::query()->where('inspector_id', $user->id);
            case UserRoleEnum::ADMIN->value:
                return Protocol::query();
            case UserRoleEnum::WATER_INSPECTOR->value:
                return Protocol::query()->where('user_id', $user->id);
            default:
                return Protocol::query()->whereRaw('1 = 0');
        }
    }

    public function filter($query, $filters)
    {
       return $query
           ->when(isset($filters['status']), function ($query) use ($filters) {
               $query->whereIn('protocol_status_id', $filters['status']);
           })
           ->when(isset($filters['protocol_number']), function ($query) use ($filters) {
               $query->where('protocol_number', $filters['protocol_number']);
           })
           ->when(isset($filters['region_id']), function ($query) use ($filters) {
               $query->where('region_id', $filters['region_id']);
           })
           ->when(isset($filters['district_id']), function ($query) use ($filters) {
               $query->where('district_id', $filters['district_id']);
           })
           ->when(isset($filters['protocol_type']), function ($query) use ($filters) {
               $query->where('protocol_type_id', $filters['protocol_type']);
           });

    }



    public function findById(?int $id)
    {
        return Protocol::query()->findOrFail($id);
    }

    public function create(?array $data)
    {
        return  Protocol::query()->create($data);
    }

    public function update(?int $id, ?array $data)
    {
        $protocol = $this->findById($id);
        $protocol->update($data);
        return $protocol;
    }

    public function attach($data, $user, $roleId)
    {
        $protocol = $this->findById($data['id']);
        $protocol->update(['inspector_id' => $data['inspector_id']]);
        return $protocol;
    }

}
