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
        $protocol = Protocol::query()->findOrFail($id);
        $protocol->update($data);
        return $protocol;
    }
}
