<?php

namespace Modules\Water\Services;

use App\Constants\FineType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Modules\Apartment\Services\MonitoringService;
use Modules\Water\Contracts\DecisionRepositoryInterface;
use Modules\Water\Http\Requests\FineCreateRequest;
use Modules\Water\Http\Requests\FineUpdateRequest;

class DecisionService
{

    protected DecisionRepositoryInterface $repository;

    public function __construct(
        DecisionRepositoryInterface $repository,
        protected ProtocolService $protocolService,
        protected MonitoringService $monitoringService
    )
    {
        $this->repository = $repository;
    }

    public function search(
        string $series,
        string $number
    )
    {
        return $this->searchDecisionFromApi(
            series: $series,
            number: $number
        );
    }

    public function create(FineCreateRequest $request)
    {
        DB::beginTransaction();
        try {
            $this->repository->create($request->all());
            $this->postDecisionFromApi($request->series, $request->number, $request['project_id']);
            if($request['project_id'] == FineType::WATER) {
                $this->protocolService->fine($request['guid']);
            }
            if ($request['protocol_id'] == FineType::APARTMENT) {
                $this->monitoringService->fine($request['guid']);
            }
            DB::commit();
        }catch (\Exception $exception){
            DB::rollBack();
            throw $exception;
        }

    }

    public function update(FineUpdateRequest $request)
    {
        try {
            return $this->repository->update($request->validated());
        }catch (\Exception $exception){
            throw $exception;
        }
    }

    private function postDecisionFromApi(
        string $series,
        string $number,
        int $project_id
    ): null|object
    {
        $url = config('water.fine.url')."/decisions/callback";
        $username = config('water.fine.login');
        $password = config('water.fine.password');
        $body = [
            "series" => $series,
            "number" => $number,
            "project_id" => $project_id,
        ];


        try {
            $response = Http::withBasicAuth(
                username: $username,
                password: $password
            )
                ->withoutVerifying()
                ->post(
                    url: $url,
                    data: $body
                );

            if(!$response->successful()) {
                return null;
            }

            return $response->object()->result ?? null;
        } catch (\Exception $exception) {
           throw $exception;
        }
    }

    private function searchDecisionFromApi(
        string $series,
        string $number
    )
    {
        $url = config('water.fine.url')."/decisions/search";
        $username = config('water.fine.login');
        $password = config('water.fine.password');

        $body = [
            "series" => $series,
            "number" => $number,
        ];

        try {
            $response = Http::withBasicAuth(
                username: $username,
                password: $password
            )
                ->withoutVerifying()
                ->post(
                    url: $url,
                    data: $body,

                );

            if(!$response->successful()) {
                return null;
            }

            return $response->object()->result ?? null;
        } catch (\Exception $exception) {
            return null;
        }
    }
}
