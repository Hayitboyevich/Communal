<?php

namespace App\Providers;

use App\Infrastructure\ExternalApis\EmploymentIntegrationProvider;
use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;

class EmploymentIntegrationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(EmploymentIntegrationProvider::class, function () {
            return new EmploymentIntegrationProvider(new Client([
                'timeout'         => 50,
                'connect_timeout' => 10
            ]));
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
