<?php

namespace App\Services;

use App\Infrastructure\ExternalApis\EmploymentIntegrationProvider;

class EmploymentIntegrationService
{
    public function __construct(private EmploymentIntegrationProvider $provider)
    {
    }

    public function currentWorkPlaceOne($pinfl, bool $retryOn429 = false)
    {
        return $this->provider->currentWorkPlaceOne($pinfl, $retryOn429);
    }

    public function currentWorkPlacePool(array $pinfls, int $concurrency, ?\Closure $acquire = null): array
    {
        return $this->provider->currentPoolRequest($pinfls, $concurrency, $acquire);
    }

}
