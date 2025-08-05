<?php

namespace Modules\Water\Repositories;

use App\Constants\FineType;
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
        string $number,
        int $projectId
    )
    {
        return $this->model->query()
            ->where([
                'series' => $series,
                'number' => $number,
                'project_id' => $projectId
            ])
            ->first();
    }

    public function update(?array $data)
    {
        try {
            $model = $this->get($data['series'], $data['number'], $data['project_id']);
            if(!$model){
                throw new \Exception('Bunday jarima mavjud emas');
            }
            return $model->update($data);
        }catch (\Exception $exception){
            throw $exception;
        }

    }

    public function create(?array $data)
    {
        try {
            $model = $this->get($data['series'], $data['number'], $data['project_id']);
            if ($model){
                throw new \Exception('Mamuriy allaqachon qo\'shilgan');
            }
            return $this->model->query()->create(attributes: $data);
        }catch (\Exception $exception){
            throw  $exception;
        }

    }
}
