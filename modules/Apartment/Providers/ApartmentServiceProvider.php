<?php

namespace Modules\Apartment\Providers;

use Illuminate\Support\ServiceProvider;

class ApartmentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config.php', 'apartment');
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->app->register(RouteServiceProvider::class);
        $this->app->register(RepositoryServiceProvider::class);

    }
}
