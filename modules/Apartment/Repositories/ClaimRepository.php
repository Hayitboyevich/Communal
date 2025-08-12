<?php

namespace Modules\Apartment\Repositories;

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

    public function all()
    {
        return $this->model->query();
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
        }catch (\Exception $exception){
            throw $exception;
        }
    }
}
