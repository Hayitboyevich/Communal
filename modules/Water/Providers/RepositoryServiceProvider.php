<?php

namespace Modules\Water\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Water\Contracts\ProtocolRepositoryInterface;
use Modules\Water\Repositories\ProtocolRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {

    }

    public function boot():void
    {
        $this->app->bind(ProtocolRepositoryInterface::class,ProtocolRepository::class);
    }
}
