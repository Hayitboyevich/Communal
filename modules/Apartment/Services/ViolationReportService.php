<?php

namespace Modules\Apartment\Services;

use App\Enums\UserRoleEnum;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Apartment\Const\MonitoringHistoryType;
use Modules\Apartment\Const\ViolationReportConfig;

/**
 * 1–5 ЖДВ hisobotlari.
 *
 * Bazaviy filtr (Excel izohlaridagi 1-band "Кўрсатма муддати – фақат муддат борлари"):
 *   violations.deadline IS NOT NULL
 * Har bir guruh: regulations.violation_type_id IN (...)
 *
 *   count       – holatlar soni (= berilgan majburiy ko'rsatmalar)
 *   fixed       – bartaraf etilgan (monitoring_histories.type = CONFIRM_REGULATION)
 *   fine_count  – jarima soni (decisions.decision_number mavjud)
 *   fine_sum    – jarima summasi, mln so'm (decisions.main_punishment_amount)
 *   court       – sudga kiritilgan (monitorings.send_court)
 *   mib         – MIBga yuborilgan (monitorings.send_mib)
 */
class ViolationReportService
{
    /** Butun respublikani ko'radigan rollar */
    private const FULL_ACCESS_ROLES = [
        UserRoleEnum::SUPER_ADMIN,
        UserRoleEnum::APARTMENT_VIEWER,
        UserRoleEnum::MONITORING_VIEWER,
    ];

    /** Faqat o'z viloyatini ko'radigan rollar */
    private const REGION_ACCESS_ROLES = [
        UserRoleEnum::APARTMENT_MANAGER,
        UserRoleEnum::REG_VIEWER,
    ];

    private const METRICS = ['count', 'fixed', 'fine_count', 'fine_sum', 'court', 'mib'];

    public function build(int $type, array $filters, $user, $roleId): array
    {
        $config = ViolationReportConfig::get($type);

        $regionId = $this->resolveRegionId($filters['region_id'] ?? null, $user, (int)$roleId);
        $from = Carbon::parse($filters['date_from'] ?? now()->startOfYear())->startOfDay();
        $to = Carbon::parse($filters['date_to'] ?? now())->endOfDay();

        // region tanlangan bo'lsa qatorlar – tumanlar, aks holda – viloyatlar
        $unitColumn = $regionId ? 'district_id' : 'region_id';

        $units = $this->units($regionId);
        $stats = $this->violationStats(ViolationReportConfig::allIds($config), $unitColumn, $regionId, $from, $to);
        $studied = $this->studiedHouses($config['monitoring_type_ids'], $unitColumn, $regionId, $from, $to);
        $existing = !empty($config['with_existing_houses'])
            ? $this->existingHouses($unitColumn, $regionId)
            : null;

        $rows = $units->map(function ($unit) use ($config, $stats, $studied, $existing) {
            return $this->buildRow(
                $config,
                $unit->id,
                $unit->name,
                $stats->get($unit->id, collect()),
                (int)($studied[$unit->id] ?? 0),
                $existing ? (int)($existing[$unit->id] ?? 0) : null,
            );
        })->values();

        return [
            'type' => $type,
            'title' => $config['title'],
            'period' => ['from' => $from->toDateString(), 'to' => $to->toDateString()],
            'region_id' => $regionId,
            'row_type' => $regionId ? 'district' : 'region',
            'columns' => $this->columns($config),
            'total' => $this->sumRows($rows, $config),
            'rows' => $rows,
        ];
    }

    private function resolveRegionId(?int $requested, $user, int $roleId): ?int
    {
        foreach (self::FULL_ACCESS_ROLES as $role) {
            if ($role->value === $roleId) {
                return $requested;
            }
        }

        foreach (self::REGION_ACCESS_ROLES as $role) {
            if ($role->value === $roleId) {
                return (int)$user->region_id;
            }
        }

        throw new AuthorizationException('Ushbu hisobotni ko\'rishga ruxsat yo\'q');
    }

    private function units(?int $regionId): Collection
    {
        if ($regionId) {
            return DB::table('districts')
                ->where('region_id', $regionId)
                ->where('status', true)
                ->orderBy('id')
                ->get(['id', 'name_uz as name']);
        }

        return DB::table('regions')
            ->where('status', true)
            ->orderBy('id')
            ->get(['id', 'name_uz as name']);
    }

    /**
     * @return Collection<int, Collection<int, object>> unit_id => violation_type_id => stats
     */
    private function violationStats(array $violationTypeIds, string $unitColumn, ?int $regionId, Carbon $from, Carbon $to): Collection
    {
        if (empty($violationTypeIds)) {
            return collect();
        }

        $fineSum = <<<'SQL'
            COALESCE(SUM(
                CASE WHEN dc.decision_number IS NOT NULL
                      AND dc.main_punishment_amount ~ '^[0-9]+([.][0-9]+)?$'
                     THEN dc.main_punishment_amount::numeric END
            ), 0) AS fine_sum
            SQL;

        return DB::table('monitorings as m')
            ->join('regulations as r', 'r.monitoring_id', '=', 'm.id')
            ->leftJoin('decisions as dc', function ($join) {
                $join->on('dc.id', '=', 'm.decision_id')->whereNull('dc.deleted_at');
            })
            ->whereIn('r.violation_type_id', $violationTypeIds)
            ->whereExists(function ($q) {
                $q->selectRaw('1')
                    ->from('violations as v')
                    ->whereColumn('v.monitoring_id', 'm.id')
                    ->whereNotNull('v.deadline');
            })
            ->whereBetween('m.created_at', [$from, $to])
            ->when($regionId, fn($q) => $q->where('m.region_id', $regionId))
            ->groupBy("m.{$unitColumn}", 'r.violation_type_id')
            ->select([
                DB::raw("m.{$unitColumn} AS unit_id"),
                'r.violation_type_id',
                DB::raw('COUNT(*) AS count'),
                DB::raw('COUNT(*) FILTER (WHERE EXISTS (
                    SELECT 1 FROM monitoring_histories mh
                    WHERE mh.guid = m.id AND mh.type = ' . (int)MonitoringHistoryType::CONFIRM_REGULATION . '
                )) AS fixed'),
                DB::raw('COUNT(dc.id) FILTER (WHERE dc.decision_number IS NOT NULL) AS fine_count'),
                DB::raw($fineSum),
                DB::raw('COUNT(*) FILTER (WHERE m.send_court) AS court'),
                DB::raw('COUNT(*) FILTER (WHERE m.send_mib) AS mib'),
            ])
            ->get()
            ->groupBy('unit_id')
            ->map(fn(Collection $items) => $items->keyBy('violation_type_id'));
    }

    /** O'rganib chiqilgan uylar (yerto'lalar) soni – COUNT(DISTINCT apartment_id) */
    private function studiedHouses(array $monitoringTypeIds, string $unitColumn, ?int $regionId, Carbon $from, Carbon $to): Collection
    {
        return DB::table('monitorings as m')
            ->whereIn('m.monitoring_type_id', $monitoringTypeIds)
            ->whereNotNull('m.apartment_id')
            ->whereBetween('m.created_at', [$from, $to])
            ->when($regionId, fn($q) => $q->where('m.region_id', $regionId))
            ->groupBy("m.{$unitColumn}")
            ->selectRaw("m.{$unitColumn} AS unit_id, COUNT(DISTINCT m.apartment_id) AS cnt")
            ->pluck('cnt', 'unit_id');
    }

    /** Mavjud uy-joylar soni – apartments jadvalidan */
    private function existingHouses(string $unitColumn, ?int $regionId): Collection
    {
        return DB::table('apartments')
            ->when($regionId, fn($q) => $q->where('region_id', $regionId))
            ->whereNotNull($unitColumn)
            ->groupBy($unitColumn)
            ->selectRaw("{$unitColumn} AS unit_id, COUNT(*) AS cnt")
            ->pluck('cnt', 'unit_id');
    }

    private function buildRow(array $config, ?int $id, ?string $name, Collection $unitStats, int $studied, ?int $existing): array
    {
        $sections = [];
        $grand = $this->emptyMetrics();

        foreach ($config['sections'] as $sectionKey => $section) {
            $sectionTotal = $this->emptyMetrics();
            $groups = [];

            foreach ($section['groups'] as $groupKey => $group) {
                $metrics = $this->emptyMetrics();

                foreach ($group['violation_type_ids'] as $vtId) {
                    if ($stat = $unitStats->get($vtId)) {
                        foreach (self::METRICS as $metric) {
                            $metrics[$metric] += (float)$stat->{$metric};
                        }
                    }
                }

                $groups[$groupKey] = $this->formatMetrics($metrics);
                $sectionTotal = $this->addMetrics($sectionTotal, $metrics);
            }

            $sections[$sectionKey] = [
                'groups' => $groups,
                'total' => $this->withCourtStatus($this->formatMetrics($sectionTotal)),
            ];
            $grand = $this->addMetrics($grand, $sectionTotal);
        }

        return array_filter([
            'id' => $id,
            'name' => $name,
            'existing_houses' => $existing,
            'studied_houses' => $studied,
            'sections' => $sections,
            'total' => $this->withCourtStatus($this->formatMetrics($grand)),
        ], fn($value, $key) => $key !== 'existing_houses' || $value !== null, ARRAY_FILTER_USE_BOTH);
    }

    private function sumRows(Collection $rows, array $config): array
    {
        $total = [
            'name' => 'Жами',
            'studied_houses' => $rows->sum('studied_houses'),
        ];

        if (!empty($config['with_existing_houses'])) {
            $total['existing_houses'] = $rows->sum('existing_houses');
        }

        foreach ($config['sections'] as $sectionKey => $section) {
            foreach ($section['groups'] as $groupKey => $group) {
                $total['sections'][$sectionKey]['groups'][$groupKey] = $this->sumMetrics(
                    $rows->pluck("sections.{$sectionKey}.groups.{$groupKey}")
                );
            }
            $total['sections'][$sectionKey]['total'] = $this->withCourtStatus(
                $this->sumMetrics($rows->pluck("sections.{$sectionKey}.total"))
            );
        }

        $total['total'] = $this->withCourtStatus($this->sumMetrics($rows->pluck('total')));

        return $total;
    }

    private function sumMetrics(Collection $items): array
    {
        $sum = $this->emptyMetrics();
        foreach ($items as $item) {
            $sum = $this->addMetrics($sum, $item);
        }

        return $this->formatMetrics($sum, alreadyMln: true);
    }

    private function emptyMetrics(): array
    {
        return array_fill_keys(self::METRICS, 0);
    }

    private function addMetrics(array $a, array $b): array
    {
        foreach (self::METRICS as $metric) {
            $a[$metric] += $b[$metric] ?? 0;
        }

        return $a;
    }

    private function formatMetrics(array $m, bool $alreadyMln = false): array
    {
        return [
            'count' => (int)$m['count'],
            'fixed' => (int)$m['fixed'],
            'regulations' => (int)$m['count'], // majburiy ko'rsatmalar = muddati bor holatlar
            'fine_count' => (int)$m['fine_count'],
            'fine_sum' => round($alreadyMln ? $m['fine_sum'] : $m['fine_sum'] / 1_000_000, 3),
            'court' => (int)$m['court'],
            'mib' => (int)$m['mib'],
        ];
    }

    /** Da'vo ariza holati tizimda hali yo'q */
    private function withCourtStatus(array $metrics): array
    {
        return $metrics + [
            'court_satisfied' => null,
            'court_rejected' => null,
            'court_pending' => null,
        ];
    }

    private function columns(array $config): array
    {
        $columns = [];
        foreach ($config['sections'] as $sectionKey => $section) {
            $columns[$sectionKey] = [
                'title' => $section['title'],
                'groups' => collect($section['groups'])->map(fn($g) => $g['label'])->all(),
            ];
        }

        return $columns;
    }
}
