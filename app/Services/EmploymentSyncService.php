<?php

namespace App\Services;

use App\Jobs\CreateNotification;
use App\Models\Organization;
use App\Models\User;
use App\Models\UserWorkPlaceSnapshot;
use Illuminate\Support\Facades\DB;

/**
 * organizations va user_organizations qo'lda to'ldiriladi, command ularni o'zgartirmaydi
 * (faqat dismissed_at qo'yadi). Har bir ishga tushishda userning faol bog'langan
 * tashkilotlari INN bo'yicha API'dagi hozirgi ish joylari bilan solishtiriladi.
 * Bazadagi INN API javobida bo'lmasa, user bo'shatilgan hisoblanadi va notification yuboriladi.
 */
class EmploymentSyncService
{
    public function sync(User $user, object $response): void
    {
        // Xato yoki muvaffaqiyatsiz javobda solishtirmaymiz: aks holda hamma "bo'shatilgan" bo'lib qoladi
        if (($response->error ?? null) !== null || (int) ($response->result->result_code ?? 0) !== 1) {
            return;
        }

        $apiInns = collect($response->result->positions ?? [])
            ->pluck('org_tin')
            ->filter()
            ->map(fn ($inn) => trim((string) $inn))
            ->flip();

        DB::transaction(function () use ($user, $response, $apiInns) {
            $snapshot = $this->storeSnapshot($user, $response);

            $user->update(['employment_checked_at' => now()]);

            $user->organizations()
                ->wherePivotNull('dismissed_at')
                ->get()
                ->reject(fn (Organization $organization) => $apiInns->has(trim((string) $organization->inn)))
                ->each(fn (Organization $organization) => $this->dismiss($user, $organization, $snapshot));
        });
    }

    /**
     * positions o'zgarmagan bo'lsa oxirgi snapshot'ning faqat last_seen_at'i yangilanadi,
     * o'zgargan bo'lsa yangi snapshot yoziladi.
     */
    private function storeSnapshot(User $user, object $response): UserWorkPlaceSnapshot
    {
        $hash   = UserWorkPlaceSnapshot::hashPositions($response->result->positions ?? []);
        $latest = $user->workPlaceSnapshots()->latest('id')->first();

        if ($latest?->hash === $hash) {
            $latest->update(['last_seen_at' => now()]);

            return $latest;
        }

        return $user->workPlaceSnapshots()->create([
            'response'      => $response,
            'hash'          => $hash,
            'first_seen_at' => now(),
            'last_seen_at'  => now(),
        ]);
    }

    private function dismiss(User $user, Organization $organization, UserWorkPlaceSnapshot $snapshot): void
    {
        $user->organizations()->updateExistingPivot($organization->id, ['dismissed_at' => now()]);

        $fio = trim($user->full_name);

        CreateNotification::dispatch([
            'type'     => 'employee_dismissed',
            'user_id'  => $user->id,
            'pinfl'    => $user->pin,
            'fio'      => $fio,
            'org_inn'  => $organization->inn,
            'org_name' => $organization->name,
            'position' => $organization->pivot->position,
            'snapshot_id' => $snapshot->id,
            'comment'  => sprintf('%s (%s) ishdan bo\'shatildi', $fio, $organization->name),
        ], config('services.egov.dismissal_notify_user_id'));
    }
}
