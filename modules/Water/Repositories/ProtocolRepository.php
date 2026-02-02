<?php

namespace Modules\Water\Repositories;

use App\Enums\UserRoleEnum;
use Carbon\Carbon;
use Modules\Water\Const\Step;
use Modules\Water\Const\TypeList;
use Modules\Water\Contracts\ProtocolRepositoryInterface;
use Modules\Water\Enums\ProtocolStatusEnum;
use Modules\Water\Models\Protocol;

class ProtocolRepository implements ProtocolRepositoryInterface
{
    public function all($user, $roleId)
    {
        switch ($roleId) {
            case UserRoleEnum::INSPECTOR->value:
                return Protocol::query()->where('inspector_id', $user->id);
            case UserRoleEnum::ADMIN->value:
            case UserRoleEnum::RES_VIEWER->value:
            case UserRoleEnum::SUPER_ADMIN->value:
                return Protocol::query();
            case UserRoleEnum::MANAGER->value:
                return Protocol::query()->where('region_id', $user->region_id);
            case UserRoleEnum::WATER_INSPECTOR->value:
                return Protocol::query()->where('user_id', $user->id)->where('role_id', $roleId);
            case UserRoleEnum::OGOH->value:
                return Protocol::query()->where('type', TypeList::OGOH_FUQARO);
            default:
                return Protocol::query()->whereRaw('1 = 0');
        }
    }

    public function filter($query, $filters)
    {
        return $query
            ->when(!empty($filters['attach']), function ($query) {
                $query->whereNull('inspector_id')
                    ->where(function ($sub) {
                        $sub->where(function ($s) {
                            $s->where('type', TypeList::WATER_INSPECTOR)
                                ->where('protocol_status_id', ProtocolStatusEnum::FORMING);
                        })->orWhere(function ($s) {
                            $s->where('type', TypeList::OGOH_FUQARO)
                                ->where('protocol_status_id', ProtocolStatusEnum::ENTER_RESULT);
                        });
                    });
            })

            ->when(!empty($filters['inspector_id']), function ($query) use ($filters) {
                $query->where(function ($q) use ($filters) {
                    $q->where('user_id', $filters['inspector_id'])
                        ->orWhere('inspector_id', $filters['inspector_id']);
                });
            })

            ->when(!empty($filters['id']), fn ($q) =>
            $q->where('id', $filters['id'])
            )

            ->when(isset($filters['month']), function ($query) use ($filters) {
                $startDate = Carbon::createFromFormat('Y-m', $filters['month'])->startOfMonth();
                $endDate = Carbon::createFromFormat('Y-m', $filters['month'])->endOfMonth();
                $query->whereBetween('deadline', [$startDate, $endDate]);
            })
            ->when(!empty($filters['user_id']), fn ($q) =>
            $q->where('user_id', $filters['user_id'])
            )

            ->when(!empty($filters['type']), fn ($q) =>
            $q->where('type', $filters['type'])
            )

            ->when(!empty($filters['category']), fn ($q) =>
            $q->where('category', $filters['category'])
            )

            ->when(!empty($filters['status']), fn ($q) =>
            $q->where('protocol_status_id', $filters['status'])
            )

            ->when(!empty($filters['protocol_number']), fn ($q) =>
            $q->where('protocol_number', $filters['protocol_number'])
            )

            ->when(!empty($filters['region_id']), fn ($q) =>
            $q->where('region_id', $filters['region_id'])
            )

            ->when(!empty($filters['district_id']), fn ($q) =>
            $q->where('district_id', $filters['district_id'])
            )

            ->when(!empty($filters['protocol_type']), fn ($q) =>
            $q->where('protocol_type_id', $filters['protocol_type'])
            )

            ->when(isset($filters['is_administrative']), function ($q) use ($filters) {
                $q->where('is_administrative', $filters['is_administrative']);
            });
    }



    public function findById(?int $id)
    {
        return Protocol::query()->findOrFail($id);
    }

    public function create(?array $data)
    {
        return Protocol::query()->create($data);
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
        $protocol->update([
            'inspector_id' => $data['inspector_id'],
        ]);
        return $protocol;
    }

    public function sendDefect($user, $roleId, $id)
    {
        try {
            if ($roleId == UserRoleEnum::WATER_INSPECTOR->value) {
                return $this->findById($id)->update(['protocol_status_id' => ProtocolStatusEnum::NOT_DEFECT, 'is_finished' => true]);
            }
            if ($roleId == UserRoleEnum::INSPECTOR->value) {
                return $this->findById($id)->update(['protocol_status_id' => ProtocolStatusEnum::CONFIRM_NOT_DEFECT]);
            }
            return null;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function reject($user, $roleId, $id)
    {
        try {
            $protocol = $this->findById($id);
            if (!$protocol) {
                throw new \Exception("Protocol not found");
            }
            $protocol->update(['protocol_status_id' => ProtocolStatusEnum::REJECTED->value]);
            return $protocol;

        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function confirmDefect($user, $roleId, $id)
    {
        try {
            $protocol = $this->findById($id);
            $protocol->update(['protocol_status_id' => ProtocolStatusEnum::NOT_DEFECT, 'is_finished' => true]);
            return $protocol;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function rejectResult($user, $roleId, $id)
    {
        try {
            $protocol = $this->findById($id);
            if (!$protocol) {
                throw new \Exception("Protocol not found");
            }
            $protocol->update(['protocol_status_id' => ProtocolStatusEnum::FORMED->value]);
            return $protocol;

        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function confirmResult($user, $roleId, $id)
    {
        try {
            $protocol = $this->findById($id);
            $protocol->update(['protocol_status_id' => ProtocolStatusEnum::CONFIRMED, 'is_finished' => true]);
            return $protocol;

        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function change($id, $data)
    {
        try {
            $protocol = $this->findById($id);
            if (!$protocol) throw new \Exception("Protocol not found");
            if ($data['protocol_status_id'] == ProtocolStatusEnum::HMQO->value) {
                $protocol->update([
                    'is_administrative' => true,
                    'category' => 2,
                ]);
            }
            $protocol->update([
                'protocol_status_id' => $data['protocol_status_id'],
            ]);
            return $protocol;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function rejectDefect($user, $roleId, $id)
    {
        try {
            $protocol = $this->findById($id);
            $protocol->update(['protocol_status_id' => ProtocolStatusEnum::ENTER_RESULT, 'step' => Step::ONE]);
            return $protocol;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

}
