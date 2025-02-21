<?php

namespace Modules\Water\Providers;

use Illuminate\Support\ServiceProvider;

class WaterServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config.php', 'water');
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->app->register(RouteServiceProvider::class);
    }
}
