<?php

namespace App\Services;

use App\Jobs\CreateNotification;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * API'dan kelgan hozirgi ish joylarini qo'lda to'ldirilgan user_organizations bilan solishtiradi.
 * Bizda faol bog'langan tashkilot API javobida yo'q bo'lsa, user bo'shatilgan
 * hisoblanadi va notification yaratiladi.
 */
class EmploymentSyncService
{
    public function sync(User $user, object $response): void
    {
        // Xato yoki muvaffaqiyatsiz javobda solishtirmaymiz: aks holda hamma "bo'shatilgan" bo'lib qoladi
        if (($response->error ?? null) !== null || (int) ($response->result->result_code ?? 0) !== 1) {
            return;
        }

        $positions = collect($response->result->positions ?? [])
            ->filter(fn ($position) => !empty($position->org_tin))
            ->keyBy(fn ($position) => (string) $position->org_tin);

        DB::transaction(function () use ($user, $response, $positions) {
            $user->update([
                'current_work_place'    => json_encode($response, JSON_UNESCAPED_UNICODE),
                'employment_checked_at' => now(),
            ]);

            // user_organizations qo'lda to'ldiriladi; bog'lanishi yo'q user uchun solishtiradigan narsa yo'q
            $user->organizations()
                ->wherePivotNull('dismissed_at')
                ->get()
                ->reject(fn (Organization $organization) => $positions->has($organization->inn))
                ->each(fn (Organization $organization) => $this->dismiss($user, $organization));
        });
    }

    private function dismiss(User $user, Organization $organization): void
    {
        $user->organizations()->updateExistingPivot($organization->id, ['dismissed_at' => now()]);

        CreateNotification::dispatch([
            'type'     => 'employee_dismissed',
            'user_id'  => $user->id,
            'pinfl'    => $user->pin,
            'fio'      => trim($user->full_name),
            'org_inn'  => $organization->inn,
            'org_name' => $organization->name,
            'position' => $organization->pivot->position,
            'comment'  => sprintf('%s ishchi (%s) bo\'shatildi', trim($user->full_name), $organization->name),
        ], config('services.egov.dismissal_notify_user_id'));
    }
}
