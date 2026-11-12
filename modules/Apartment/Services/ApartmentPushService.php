<?php

namespace Modules\Apartment\Services;

use App\Models\District;
use App\Models\Region;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Modules\Apartment\Models\Apartment;

class ApartmentPushService
{
    /**
     * Tashqi tizimdan kelgan apartmentlarni home_id bo'yicha upsert qiladi.
     * Bitta SQL (INSERT ... ON CONFLICT (home_id) DO UPDATE) — parallel so'rovlarda ham xavfsiz.
     */
    public function push(array $items): array
    {
        // Deadlock oldini olish: parallel batchlarda qatorlar bir xil tartibda lock qilinsin
        usort($items, fn ($a, $b) => $a['home_id'] <=> $b['home_id']);

        [$regions, $districts] = $this->resolveSoato($items);

        $unknownRegionSoato = [];
        $unknownDistrictSoato = [];
        $rows = [];

        foreach ($items as $item) {
            $regionSoato = $item['region_soato'] ?? null;
            $districtSoato = $item['district_soato'] ?? null;

            $district = $districtSoato ? ($districts[$districtSoato] ?? null) : null;
            $regionId = $regionSoato ? ($regions[$regionSoato] ?? null) : null;

            if ($regionSoato && !$regionId) $unknownRegionSoato[] = $regionSoato;
            if ($districtSoato && !$district) $unknownDistrictSoato[] = $districtSoato;

            $rows[] = [
                'home_id' => (int) $item['home_id'],
                'company_id' => (int) $item['company_id'],
                'street_id' => $item['street_id'] ?? null,
                'street_name' => $item['street_name'] ?? null,
                'home_name' => $item['home_name'] ?? null,
                'apartment_type' => $item['apartment_type'] ?? null,
                'region_soato' => $regionSoato,
                'district_soato' => $districtSoato,
                'cadastral_number' => $item['cadastral_number'] ?? null,
                // region_soato kelmasa yoki topilmasa — tumandan olinadi
                'region_id' => $regionId ?? $district?->region_id,
                'district_id' => $district?->id,
            ];
        }

        $updateColumns = array_values(array_diff(array_keys($rows[0]), ['home_id']));

        DB::transaction(function () use ($rows, $updateColumns) {
            foreach (array_chunk($rows, 250) as $chunk) {
                // Eloquent upsert created_at/updated_at ni o'zi qo'shadi
                Apartment::query()->upsert($chunk, ['home_id'], $updateColumns);
            }
        });

        return [
            'received' => count($rows),
            'unknown_region_soato' => array_values(array_unique($unknownRegionSoato)),
            'unknown_district_soato' => array_values(array_unique($unknownDistrictSoato)),
        ];
    }

    /**
     * Soato -> id xaritasi (har bir so'rov uchun 2 ta query).
     */
    private function resolveSoato(array $items): array
    {
        $regionSoatos = array_values(array_unique(array_filter(Arr::pluck($items, 'region_soato'))));
        $districtSoatos = array_values(array_unique(array_filter(Arr::pluck($items, 'district_soato'))));

        $regions = $regionSoatos
            ? Region::query()->whereIn('soato', $regionSoatos)->pluck('id', 'soato')->all()
            : [];

        $districts = $districtSoatos
            ? District::query()->whereIn('soato', $districtSoatos)->get(['id', 'region_id', 'soato'])->keyBy('soato')->all()
            : [];

        return [$regions, $districts];
    }
}
