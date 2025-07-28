<?php

namespace Modules\Water\Repositories;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Water\Contracts\DecisionRepositoryInterface;
use Modules\Water\Contracts\HistoryRepositoryInterface;
use Modules\Water\Models\Decision;

class DecisionRepository implements DecisionRepositoryInterface
{
    private Decision $model;

    public function __construct(
        Decision $model
    )
    {
        $this->model = $model;
    }

    public function get(
        string $series,
        string $number
    )
    {
        return $this->model->query()
            ->where([
                'series' => $series,
                'number' => $number
            ])
            ->first();
    }

    public function update(
        string $series,
        string $number,
        array $data,
    ): bool
    {
        return $this->model->query()
            ->where([
                'series' => $series,
                'number' => $number
            ])
            ->update(
                values: $data
            );
    }

    public function create(
        array $data
    )
    {
        return $this->model->query()
            ->create(
                attributes: $data
            );
    }
}
