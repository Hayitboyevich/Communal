<?php

namespace App\Providers;

use App\Infrastructure\ExternalApis\ShaffofIdIntegrationProvider;
use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;

class ShaffofIdIntegrationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(ShaffofIdIntegrationProvider::class, function () {
            return new ShaffofIdIntegrationProvider(new Client([
                'base_uri' => config('services.shaffof_id.main_url')
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
