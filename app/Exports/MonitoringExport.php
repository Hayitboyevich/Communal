<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromGenerator;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Modules\Apartment\Models\Monitoring;

class MonitoringExport implements WithHeadings, FromGenerator, WithMapping
{

    private const HIDDEN_ECONOMY_PLACES = [1, 2, 8, 9, 10];
    private const CHUNK = 1000;
    public function __construct(
        protected ?int   $regionId,
        protected ?array $filters = []
    )
    {
    }

    public function generator(): \Generator
    {
        $dateFrom = $this->filters['date_from'] ?? null;
        $dateTo = $this->filters['date_to'] ?? null;

        yield from Monitoring::query()
            ->with([
                'confirmRegulationHistory',
                'user',
                'monitoringType',
                'base',
                'company',
                'apartment',
                'district',
                'region',
                'status',
                'regulation.place',
                'regulation.violationType',
                'violation',
                'fine',
                'apartmentHiddenEconomy'
            ])
            ->when(!empty($this->regionId), function ($query) {
                $query->where('region_id', $this->regionId);
            })
            ->when($dateFrom && $dateTo, function ($query) use ($dateFrom, $dateTo) {
                $query->whereBetween('created_at', [
                    Carbon::parse($dateFrom)->startOfDay(),
                    Carbon::parse($dateTo)->endOfDay()
                ]);
            })
            ->lazyById(self::CHUNK);
    }

    public function map($m): array
    {
        $fine = $m->fine;

        return [
            $m->id,
            $m->apartment?->home_id ?? '',
            $m->region?->name_uz ?? '',
            $m->district?->name_uz ?? '',
            $m->status?->name ?? '',
            $m->user?->full_name ?? '',
            $m->monitoringType?->name ?? '',
            $m->regulation?->place?->name ?? '',
            $m->regulation?->violationType?->name ?? '',
            $m->created_at?->format('Y-m-d'),
            $m->base?->name ?? '',
            $m->company?->company_name ?? '',
            $m->address ?? trim(($m->apartment?->street_name ?? '') . ' ' . ($m->apartment?->home_name ?? '')),
            $m->address_commit ?? '',
            $m->additional_comment ?? '',
            $m->regulation?->fish ?? $m->regulation?->organization_name ?? '',
            $m->violation?->deadline ?? '',
            $fine ? $fine->series . $fine->number : '',
            $fine ? $fine->decision_series . $fine->decision_number : '',
            $fine?->status_name ?? '',
            $fine?->main_punishment_amount ?? '',
            $fine?->paid_amount ?? '',
            $fine?->created_time ?? '',
            $fine?->updated_time ?? '',
            $fine?->execution_date ?? '',
            $m->my_home_integration ? 'true' : 'false',
            $m->long_term ? 'Uzoq muddatli' : '',
            match ((int) $m->long_term_type) {
                1 => 'Vafot etgan',
                2 => 'Chet elda',
                3 => 'Boshqa holat',
                default => '',
            },
            $m->send_court ? 'Sudga yuborilgan' : '',
            $m->treatment_number ?? '',
            $m->treatment_date ?? '',
            $this->isHiddenEconomy($m) ? 'true' : 'false',
            $m->confirmRegulationHistory?->created_at?->format('Y-m-d H:i'),
        ];
    }

    private function isHiddenEconomy(Monitoring $m): bool
    {
        return $m->apartmentHiddenEconomy !== null
            || in_array($m->regulation?->place_id, self::HIDDEN_ECONOMY_PLACES, true);
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Home_ID',
            'Viloyat',
            'Tuman',
            'Holati',
            'Inspektor fish',
            'O\'rganish turi',
            'O\'rganilgan joy',
            'Aniqlangan qoidabuzarlik',
            'Sana',
            'Asos',
            'Korxona',
            'Turar joy',
            'Xonadon',
            'Qoshimcha malumot',
            'Javobgar',
            'Ko\'rsatma muddati',
            'Bayonnoma seriya va raqami',
            'Qaror seriya va raqami',
            'Mamuriy holati',
            'Jarima miqdori',
            'To\'langan miqdor',
            'Bayonnoma tizimga kiritilgan sana',
            'Bayonnoma yangilangan sana',
            'Qaror ijrosi sanasi',
            'Mening uyimdan kelgan',
            'Uzoq muddatli ko\'rsatma',
            'Uzoq muddatli sababi',
            'Sud',
            'Davo ariza raqami',
            'Davo ariza sanasi',
            'Yashirin iqtisodiyot',
            'Ko\'rsatma bajarilgan sana'
        ];
    }
}
