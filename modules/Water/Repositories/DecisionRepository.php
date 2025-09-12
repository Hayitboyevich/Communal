<?php

namespace Modules\Water\Repositories;

use App\Constants\FineType;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Apartment\Models\Monitoring;
use Modules\Water\Contracts\DecisionRepositoryInterface;
use Modules\Water\Contracts\HistoryRepositoryInterface;
use Modules\Water\Models\Decision;
use Modules\Water\Models\Protocol;

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

            if (! $model) {
                $model = $this->model->query()->create($data);
            }

            if ($data['project_id'] == FineType::APARTMENT) {
                $monitoring = Monitoring::query()->findOrFail($data['guid']);
                $monitoring->update(['decision_id' => $model->id]);
            }else{
                $protocol = Protocol::query()->findOrFail($data['guid']);
                $protocol->update(['decision_id' => $model->id]);
            }
            return $model;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

}
