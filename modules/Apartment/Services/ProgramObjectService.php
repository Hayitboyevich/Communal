<?php

namespace Modules\Apartment\Services;

use Modules\Apartment\Const\ProgramObjectStatusList;
use Modules\Apartment\Contracts\ProgramObjectInterface;
use Modules\Apartment\Http\Requests\AttachObjectRequest;
use Modules\Apartment\Http\Requests\ProgramObjectRequest;

class ProgramObjectService
{
    public function __construct(public ProgramObjectInterface $repository){}

    public function findById(int $id)
    {
        return $this->repository->findById($id);
    }

    public function getAll($user, $roleId, $filters)
    {
        $query = $this->repository->getAll($user, $roleId);
        return $this->repository->search($filters, $query);
    }

    public function count($user, $roleId, $filters)
    {
        $query = $this->getAll($user, $roleId, $filters);
        return [
            'all' => $query->clone()->count(),
            'not_active' => $query->clone()->where('status', ProgramObjectStatusList::NOT_ACTIVE)->count(),
            'progress' => $query->clone()->where('status', ProgramObjectStatusList::PROGRESS)->count(),
            'done' => $query->clone()->where('status', ProgramObjectStatusList::DONE)->count(),
            'need_repair' => $query->clone()->where('status', ProgramObjectStatusList::NEED_REPAIR)->count(),
            'suspended' => $query->clone()->where('status', ProgramObjectStatusList::SUSPENDED)->count(),
        ];
    }

    public function create(ProgramObjectRequest $request)
    {
        return $this->repository->create($request->validated());
    }

    public function attach(AttachObjectRequest $request)
    {
        return $this->repository->attach($request->validated());
    }
}
