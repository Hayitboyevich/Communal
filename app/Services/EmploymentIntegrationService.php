<?php

namespace App\Services;

use App\Infrastructure\ExternalApis\EmploymentIntegrationProvider;

class EmploymentIntegrationService
{
    public function __construct(private EmploymentIntegrationProvider $provider)
    {
    }

    public function currentWorkPlaceOne($pinfl)
    {
        return $this->provider->currentWorkPlaceOne($pinfl);
    }

    public function currentWorkPlacePool(array $pinfls, int $concurrency, ?\Closure $acquire = null): array
    {
        return $this->provider->currentPoolRequest($pinfls, $concurrency, $acquire);
    }

}
