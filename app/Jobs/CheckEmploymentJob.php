<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\EmploymentIntegrationService;
use App\Services\EmploymentSyncService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Navbatdagi PER_MINUTE ta userni tekshiradi va cursor'ni faqat guruh bajarilgandan
 * keyin suradi. ShouldBeUnique: navbatda/ishlayotgan job bo'lsa yangisi qo'shilmaydi,
 * shuning uchun guruhlar ustma-ust tushmaydi va aylanish oldingisi tugamasdan boshlanmaydi.
 */
class CheckEmploymentJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, Queueable;

    public const PER_MINUTE = 50;
    public const CURSOR_KEY = 'employment:check:cursor';
    private const RATE_KEY = 'egov:current-work-place:sent';
    // 60 s + 1 s zaxira: band qilish va haqiqiy yuborish orasidagi farq uchun
    private const WINDOW = 61;

    public $timeout = 300;
    // Qayta urinish shu guruhni takror yuboradi; xato bo'lganlari keyingi aylanishda tekshiriladi
    public $tries = 1;
    // Worker o'lib qolsa ham unique lock shuncha sekunddan keyin bo'shaydi
    public $uniqueFor = 600;

    public function __construct()
    {
        $this->onQueue('employment');
    }

    public function handle(EmploymentIntegrationService $service, EmploymentSyncService $sync): void
    {
        $cursor = (int) Cache::get(self::CURSOR_KEY, 0);
        $users  = $this->nextBatch($cursor);

        if ($users->isEmpty() && $cursor > 0) {
            Log::info('Employment check: aylanish tugadi', ['last_id' => $cursor]);

            $users = $this->nextBatch(0);
        }

        if ($users->isEmpty()) {
            return;
        }

        // Joy pool'dan oldin band qilinadi: pool ichida sleep qilinsa,
        // yo'ldagi requestlarning javobi o'qilmay timeout bo'lib qoladi
        $this->reserve($users->count());

        $results = $service->currentWorkPlacePool($users->pluck('pin')->all(), self::PER_MINUTE);

        $usersByPin = $users->keyBy('pin');

        foreach ($results as $pinfl => $result) {
            $user = $usersByPin->get((string) $pinfl);

            if ($result === null || $user === null) {
                continue;
            }

            // Bitta userdagi xato butun guruhni qayta yuborishga olib kelmasin
            try {
                $sync->sync($user, $result);
            } catch (Throwable $e) {
                Log::error('Employment sync xatosi', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            }
        }

        Cache::forever(self::CURSOR_KEY, $users->last()->id);
    }

    private function nextBatch(int $afterId): Collection
    {
        return User::query()
            ->whereNotNull('pin')
            ->where('id', '>', $afterId)
            ->orderBy('id')
            ->limit(self::PER_MINUTE)
            ->get(['id', 'pin', 'name', 'surname', 'middle_name']);
    }

    /**
     * Sliding window: oxirgi WINDOW sekundda yuborilgan requestlar vaqti cache'da saqlanadi.
     * $count ta joy bo'shaguncha kutadi va ularni birdaniga band qiladi.
     */
    private function reserve(int $count): void
    {
        while (true) {
            $wait = Cache::lock(self::RATE_KEY . ':lock', 10)->block(10, function () use ($count) {
                $now  = microtime(true);
                $sent = array_values(array_filter(
                    Cache::get(self::RATE_KEY, []),
                    fn (float $time) => $time > $now - self::WINDOW
                ));

                if (count($sent) + $count <= self::PER_MINUTE) {
                    Cache::put(self::RATE_KEY, array_merge($sent, array_fill(0, $count, $now)), self::WINDOW * 2);
                    return 0;
                }

                // Yetarli joy bo'shashi uchun eng eskilaridan nechtasi oynadan chiqishi kerak
                $mustExpire = count($sent) + $count - self::PER_MINUTE;

                return $sent[$mustExpire - 1] + self::WINDOW - $now;
            });

            if ($wait <= 0) {
                return;
            }

            usleep((int) ceil($wait * 1_000_000));
        }
    }
}
