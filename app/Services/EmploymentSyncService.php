<?php

namespace App\Services;

use App\Jobs\CreateNotification;
use App\Models\Organization;
use App\Models\User;
use App\Models\UserOrganization;
use App\Models\UserWorkPlaceSnapshot;
use Illuminate\Support\Facades\DB;

/**
 * organizations va user_organizations qo'lda yoki bir marta app:import-employment bilan to'ldiriladi, sync ularni o'zgartirmaydi
 * (faqat dismissed_at qo'yadi). Har bir ishga tushishda userning faol bog'langan
 * tashkilotlari INN bo'yicha API'dagi hozirgi ish joylari bilan solishtiriladi.
 * Bazadagi INN API javobida bo'lmasa, user bo'shatilgan hisoblanadi; tashkilotda
 * notification_send = true bo'lsa, notification yuboriladi.
 */
class EmploymentSyncService
{
    public function sync(User $user, object $response): void
    {
        // Xato yoki muvaffaqiyatsiz javobda solishtirmaymiz: aks holda hamma "bo'shatilgan" bo'lib qoladi
        if (! $this->isSuccessful($response)) {
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
     * Bir martalik boshlang'ich to'ldirish: API'dagi har bir ish joyi uchun tashkilot (INN bo'yicha)
     * va bog'lanish yaratiladi. Faqat qo'shadi, hech kimni bo'shatmaydi; mavjudlariga tegmaydi.
     *
     * @return array{organizations: int, links: int}|null  muvaffaqiyatsiz javobda null
     */
    public function import(User $user, object $response): ?array
    {
        if (! $this->isSuccessful($response)) {
            return null;
        }

        return DB::transaction(function () use ($user, $response) {
            $this->storeSnapshot($user, $response);

            $user->update(['employment_checked_at' => now()]);

            $created = ['organizations' => 0, 'links' => 0];

            foreach ($response->result->positions ?? [] as $position) {
                $inn = trim((string) ($position->org_tin ?? ''));
                if ($inn === '') {
                    continue;
                }

                $organization = Organization::firstOrCreate(
                    ['inn' => $inn],
                    ['name' => trim((string) ($position->org ?? $inn))]
                );
                $created['organizations'] += (int) $organization->wasRecentlyCreated;

                // Bitta tashkilotda bir nechta lavozim bo'lsa, birinchisi saqlanadi
                $link = UserOrganization::firstOrCreate(
                    ['user_id' => $user->id, 'organization_id' => $organization->id],
                    ['position' => $position->position ?? null, 'begin_date' => $position->begin_date ?? null]
                );
                $created['links'] += (int) $link->wasRecentlyCreated;
            }

            return $created;
        });
    }

    private function isSuccessful(object $response): bool
    {
        return ($response->error ?? null) === null && (int) ($response->result->result_code ?? 0) === 1;
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

        // Bo'shatish hamma tashkilot uchun belgilanadi, notification esa faqat kuzatiladiganlari uchun
        if (! $organization->notification_send) {
            return;
        }

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
