<?php

namespace Modules\Water\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Water\Contracts\CardRepositoryInterface;
use Modules\Water\Contracts\HistoryRepositoryInterface;
use Modules\Water\Contracts\ProtocolRepositoryInterface;
use Modules\Water\Repositories\CardRepository;
use Modules\Water\Repositories\HistoryRepository;
use Modules\Water\Repositories\ProtocolRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {

    }

    public function boot():void
    {
        $this->app->bind(ProtocolRepositoryInterface::class,ProtocolRepository::class);
        $this->app->bind(CardRepositoryInterface::class,CardRepository::class);
        $this->app->bind(HistoryRepositoryInterface::class,HistoryRepository::class);
    }
}
