<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\EmploymentIntegrationService;
use App\Services\EmploymentSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class ImportEmploymentCommand extends Command
{
    protected $signature = 'app:import-employment
        {--user=* : Faqat shu id li userlar}
        {--all : employment_checked_at to\'ldirilgan userlarni ham olish}';

    protected $description = 'Userlarning ish joylarini egov\'dan olib organizations va user_organizations\'ni to\'ldiradi';

    private const CHUNK = 500;

    public function handle(EmploymentIntegrationService $service, EmploymentSyncService $sync): int
    {
        $stats   = ['users' => 0, 'organizations' => 0, 'links' => 0, 'skipped' => 0, 'failed' => 0];
        $started = microtime(true);

        $query = User::query()
            ->whereNotNull('pin')
            ->when($this->option('user'), fn ($q, $ids) => $q->whereIn('id', $ids))
            ->unless($this->option('all'), fn ($q) => $q->whereNull('employment_checked_at'))
            ->select(['id', 'pin']);

        $total = (clone $query)->count();
        $this->info("Userlar: {$total}");

        $bar = $this->output->createProgressBar($total);

        $query->chunkById(self::CHUNK, function ($users) use ($service, $sync, &$stats, $bar) {
            foreach ($users as $user) {
                // Bitta userdagi xato butun importni to'xtatmasin
                try {
                    $response = $service->currentWorkPlaceOne($user->pin, retryOn429: true);
                    $created  = $response !== null ? $sync->import($user, $response) : null;

                    if ($created === null) {
                        $stats['skipped']++;
                    } else {
                        $stats['users']++;
                        $stats['organizations'] += $created['organizations'];
                        $stats['links']         += $created['links'];
                    }
                } catch (Throwable $e) {
                    $stats['failed']++;
                    Log::warning('Employment import xatosi', ['user_id' => $user->id, 'error' => $e->getMessage()]);
                }

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();

        $stats['minutes'] = round((microtime(true) - $started) / 60, 1);
        Log::info('Employment import: tugadi', $stats);

        $this->table(
            ['Userlar', 'Yangi tashkilot', 'Yangi bog\'lanish', 'Muvaffaqiyatsiz javob', 'Xato', 'Daqiqa'],
            [array_values($stats)]
        );

        return self::SUCCESS;
    }
}
