<?php

namespace Modules\Water\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Modules\Water\Const\ProtocolHistoryType;
use Modules\Water\Contracts\DecisionRepositoryInterface;
use Modules\Water\Contracts\HistoryRepositoryInterface;
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

    public function handleSearchingDecision(
        string $series,
        string $number
    ): bool
    {
        $decision = $this->repository->get(
            series: $series,
            number: $number
        );

        if(!$decision) {
            return false;
        }

        $data = $this->getDecisionFromApi(
            series: $series,
            number: $number
        );

        if(!$data) {
            return false;
        }

        $result = $this->repository->create(
            data: $data
        );

        if(!$result) {
            return false;
        }

        return true;
    }

    public function handleCreatingDecision(
        string $series,
        string $number
    ): bool
    {
        $decision = $this->repository->get(
            series: $series,
            number: $number
        );

        if(!$decision) {
            return false;
        }

        $data = $this->getDecisionFromApi(
            series: $series,
            number: $number
        );

        if(!$data) {
            return false;
        }

        $result = $this->repository->create(
            data: $data
        );

        if(!$result) {
           return false;
        }

        return true;
    }

    private function getDecisionFromApi(
        string $series,
        string $number
    ): null|object
    {
        $url = env('API_DATA_URL')."/decisions/callback";
        $username = env('API_DATA_USERNAME');
        $password = env('API_DATA_PASSWORD');
        $body = [
            "series" => $series,
            "number" => $number,
        ];

        try {
            $response = Http::withBasicAuth(
                username: $username,
                password: $password
            )
                ->post(
                    url: $url,
                    data: $body
                );

            if(!$response->successful()) {
                return null;
            }

            return $response->object()->result ?? null;
        } catch (\Exception $exception) {
            return null;
        }
    }

    private function searchDecisionFromApi(
        string $series,
        string $number
    ): null|object
    {
        $url = env('API_DATA_URL')."/decisions/search";
        $username = env('API_DATA_USERNAME');
        $password = env('API_DATA_PASSWORD');
        $body = [
            "series" => $series,
            "number" => $number,
        ];

        try {
            $response = Http::withBasicAuth(
                username: $username,
                password: $password
            )
                ->post(
                    url: $url,
                    data: $body
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
