<?php

namespace Modules\Apartment\Repositories;

use App\Enums\UserRoleEnum;
use App\Models\UserRole;
use Modules\Apartment\Contracts\ClaimRepositoryInterface;
use Modules\Apartment\Models\Claim;

class ClaimRepository implements ClaimRepositoryInterface
{
    public function __construct(protected Claim $model)
    {
    }

    public function findById($id)
    {
        return $this->model->query()->findOrFail($id);
    }

    public function all($user, $roleId, $filters)
    {
        $query = $this->model->query()
            ->when(isset($filters['status']), function ($q) use ($filters) {
                $q->where('status', $filters['status']);
            });
        switch ($roleId) {
            case UserRoleEnum::CADASTR_USER->value:
                return $query->where('user_id', $user)->orWhere('district_id', $user->district_id);
            case  UserRoleEnum::APARTMENT_INSPECTOR->value:
                return $query->where('inspector_id', $user->id);
            default:
                return $this->model->query()->whereRaw('1 = 0');
        }
    }

    public function create($data)
    {
        return $this->model->query()->create($data);
    }

    public function update($id, $data)
    {
        try {
            $claim = $this->model->query()->findOrFail($id);
            $claim->update($data);
            return $claim;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
}
