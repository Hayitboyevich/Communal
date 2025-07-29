<?php

namespace Modules\Water\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Modules\Water\Const\ProtocolHistoryType;
use Modules\Water\Contracts\DecisionRepositoryInterface;
use Modules\Water\Contracts\HistoryRepositoryInterface;
use Modules\Water\Http\Requests\FineCreateRequest;
use Modules\Water\Models\ProtocolHistory;
use Modules\Water\Repositories\HistoryRepository;

class DecisionService
{

    protected DecisionRepositoryInterface $repository;

    public function __construct(
        DecisionRepositoryInterface $repository
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
        try {
            $this->repository->create($request->all());
            $this->postDecisionFromApi($request->series, $request->number);
        }catch (\Exception $exception){
            throw $exception;
        }

    }

    private function postDecisionFromApi(
        string $series,
        string $number
    ): null|object
    {
        $url = env('FINE_URL')."/decisions/callback";
        $username = env('FINE_USERNAME');
        $password = env('FINE_PASSWORD');
        $body = [
            "series" => $series,
            "number" => $number,
            "project_id" => 1,
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
        $url = env('FINE_URL')."/decisions/search";
        $username = env('FINE_USERNAME');
        $password = env('FINE_PASSWORD');

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
