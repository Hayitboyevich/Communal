<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\EmploymentIntegrationService;
use App\Services\EmploymentSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Kuniga bir marta qo'lda tashkilotga bog'langan (dismissed_at bo'sh) userlarning
 * hozirgi ish joyini egov orqali tekshiradi. Bog'lanishi yo'q userlarga request yuborilmaydi.
 * Cursor har chunk'dan keyin saqlanadi: command yarmida to'xtasa,
 * keyingi ishga tushishda qolgan joyidan davom etadi.
 *
 * Test uchun:
 *   php artisan app:check-employment-command --user=12 --user=15   (faqat shu userlar)
 *   php artisan app:check-employment-command --limit=10            (birinchi 10 ta user)
 * Test rejimida cursor ishlatilmaydi va o'zgarmaydi.
 */
class CheckEmploymentCommand extends Command
{
    protected $signature = 'app:check-employment-command
        {--reset : Aylanishni boshidan boshlash}
        {--user=* : Test: faqat shu id li userlarni tekshirish}
        {--limit= : Test: faqat birinchi N ta userni tekshirish}';

    protected $description = 'Userlarning hozirgi ish joyini egov orqali tekshiradi';

    private const CURSOR_KEY = 'employment:check:cursor';
    private const CHUNK = 500;
    private const COLUMNS = ['id', 'pin', 'name', 'surname', 'middle_name'];

    private int $checked = 0;
    private int $failed = 0;

    public function handle(EmploymentIntegrationService $service, EmploymentSyncService $sync): int
    {
        if ($this->option('user') || $this->option('limit')) {
            return $this->runTest($service, $sync);
        }

        if ($this->option('reset')) {
            Cache::forget(self::CURSOR_KEY);
        }

        $cursor  = (int) Cache::get(self::CURSOR_KEY, 0);
        $started = microtime(true);

        Log::info('Employment check: boshlandi', ['cursor' => $cursor]);

        User::query()
            ->whereNotNull('pin')
            ->whereHas('organizations', fn ($q) => $q->whereNull('user_organizations.dismissed_at'))
            ->where('id', '>', $cursor)
            ->select(self::COLUMNS)
            ->chunkById(self::CHUNK, function ($users) use ($service, $sync) {
                foreach ($users as $user) {
                    $this->checkUser($user, $service, $sync);
                }

                Cache::forever(self::CURSOR_KEY, $users->last()->id);
                $this->info("Tekshirildi: {$this->checked}, xato: {$this->failed}, oxirgi id: {$users->last()->id}");
            });

        // Aylanish to'liq tugadi: ertaga boshidan boshlanadi
        Cache::forget(self::CURSOR_KEY);

        Log::info('Employment check: tugadi', [
            'checked' => $this->checked,
            'failed'  => $this->failed,
            'minutes' => round((microtime(true) - $started) / 60, 1),
        ]);

        return self::SUCCESS;
    }

    private function runTest(EmploymentIntegrationService $service, EmploymentSyncService $sync): int
    {
        $users = User::query()
            ->whereNotNull('pin')
            ->whereHas('organizations', fn ($q) => $q->whereNull('user_organizations.dismissed_at'))
            ->when($this->option('user'), fn ($q, $ids) => $q->whereIn('id', $ids))
            ->when($this->option('limit'), fn ($q, $limit) => $q->limit((int) $limit))
            ->orderBy('id')
            ->get(self::COLUMNS);

        if ($users->isEmpty()) {
            $this->warn('PIN bor user topilmadi');
            return self::FAILURE;
        }

        foreach ($users as $user) {
            $ok = $this->checkUser($user, $service, $sync);

            $orgs = $user->organizations()->get()
                ->map(fn ($o) => sprintf('%s | %s | %s%s',
                    $o->inn, $o->name, $o->pivot->position ?? '-',
                    $o->pivot->dismissed_at ? ' | BO\'SHATILGAN' : ''))
                ->implode(PHP_EOL . '    ');

            $this->line(sprintf('#%d %s %s',
                $user->id, trim($user->full_name),
                $ok ? 'OK' : 'XATO (log\'ga qarang)'));
            $this->line('    ' . ($orgs ?: 'ish joyi yo\'q'));
        }

        $this->info("Tekshirildi: {$this->checked}, xato: {$this->failed}");

        return self::SUCCESS;
    }

    private function checkUser(User $user, EmploymentIntegrationService $service, EmploymentSyncService $sync): bool
    {
        // Bitta userdagi xato butun aylanishni to'xtatmasin
        try {
            $response = $service->currentWorkPlaceOne($user->pin);

            if ($response !== null) {
                $sync->sync($user, $response);
            }

            $this->checked++;

            return true;
        } catch (Throwable $e) {
            $this->failed++;
            Log::warning('Employment check xatosi', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            return false;
        }
    }
}
