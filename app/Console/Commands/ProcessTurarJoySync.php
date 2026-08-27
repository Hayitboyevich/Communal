<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\Apartment\Models\Apartment;
use Modules\Apartment\Services\TurarJoySyncService;

class ProcessTurarJoySync extends Command
{
    public function __construct(protected TurarJoySyncService $service)
    {
        parent::__construct();
    }

    protected $signature = 'app:turar-joy-sync';

    public function handle()
    {
        try {
            $apartments = Apartment::query()
                ->where('home_integration', 1)
                ->whereNotNull('turar_joy_sync_ready_at')
                ->where('turar_joy_sync_ready_at', '<=', now())
                ->limit(50)
                ->get();

            foreach ($apartments as $apartment) {
                $sources = $apartment->turar_joy_sync_sources;

                $apartment->update([
                    'turar_joy_sync_sources' => null,
                    'turar_joy_sync_ready_at' => null,
                ]);

                $this->service->dispatch($apartment->home_id, $sources);
            }
        } catch (\Exception $exception) {
            Log::info($exception->getMessage());
        }
    }
}
