<?php

namespace Modules\Apartment\Providers;



use Illuminate\Support\ServiceProvider;
use Modules\Apartment\Contracts\MonitoringRepositoryInterface;
use Modules\Apartment\Repositories\MonitoringRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {

    }

    public function boot():void
    {
        $this->app->bind(MonitoringRepositoryInterface::class,MonitoringRepository::class);
    }
}
