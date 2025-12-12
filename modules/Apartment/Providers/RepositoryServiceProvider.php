<?php

namespace Modules\Apartment\Providers;



use Illuminate\Support\ServiceProvider;
use Modules\Apartment\Contracts\ChecklistRepositoryInterface;
use Modules\Apartment\Contracts\ClaimRepositoryInterface;
use Modules\Apartment\Contracts\LetterInterface;
use Modules\Apartment\Contracts\MonitoringRepositoryInterface;
use Modules\Apartment\Contracts\ProgramMonitoringInterface;
use Modules\Apartment\Contracts\ProgramObjectInterface;
use Modules\Apartment\Contracts\ProgramRepositoryInterface;
use Modules\Apartment\Repositories\ChecklistRepository;
use Modules\Apartment\Repositories\ClaimRepository;
use Modules\Apartment\Repositories\LetterRepository;
use Modules\Apartment\Repositories\MonitoringRepository;
use Modules\Apartment\Repositories\ProgramMonitoringRepository;
use Modules\Apartment\Repositories\ProgramObjectRepository;
use Modules\Apartment\Repositories\ProgramRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {

    }

    public function boot():void
    {
        $this->app->bind(MonitoringRepositoryInterface::class,MonitoringRepository::class);
        $this->app->bind(ClaimRepositoryInterface::class,ClaimRepository::class);
        $this->app->bind(ProgramRepositoryInterface::class,ProgramRepository::class);
        $this->app->bind(ProgramMonitoringInterface::class,ProgramMonitoringRepository::class);
        $this->app->bind(ChecklistRepositoryInterface::class,ChecklistRepository::class);
        $this->app->bind(ProgramObjectInterface::class,ProgramObjectRepository::class);
        $this->app->bind(LetterInterface::class,LetterRepository::class);
    }
}
