<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\Apartment\Models\Monitoring;
use Modules\Apartment\Services\MonitoringService;
use Modules\Water\Const\Step;

class SendMyHome extends Command
{

    public function __construct(protected MonitoringService $service)
    {
        parent::__construct();
    }

    protected $signature = 'app:send-home';


    protected $description = 'Command description';


    public function handle()
    {
        try {
            $monitorings = Monitoring::query()
                ->where('monitoring_type_id', 3)
                ->whereNull('send_home')
                ->where(function ($query) {
                    $query->where('is_administrative', true)
                        ->orWhereIn('step', [3,4]);
                })->get();

            foreach ($monitorings as $monitoring) {
                $this->service->sendMyHome($monitoring->id);
                $monitoring->update(['send_home' => 1]);
            }

        }catch (\Exception $exception){
          Log::info($exception->getMessage());
        }
    }
}
