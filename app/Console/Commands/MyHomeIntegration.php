<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\Apartment\Models\Monitoring;
use Modules\Apartment\Services\MonitoringService;

class MyHomeIntegration extends Command
{

    public function __construct(protected MonitoringService $service)
    {
        parent::__construct();
    }

    protected $signature = 'app:home-integration';


    protected $description = 'Command description';


    public function handle()
    {
        try {
            $monitorings = Monitoring::query()
                ->where('my_home_integration', true)
                ->whereNull('send_my_home')
                ->limit(10)
                ->get();

            foreach ($monitorings as $monitoring) {
                $this->service->sendHome($monitoring);
                $monitoring->update(['send_my_home' => 1]);
            }

        }catch (\Exception $exception){
          Log::info($exception->getMessage());
        }
    }
}
