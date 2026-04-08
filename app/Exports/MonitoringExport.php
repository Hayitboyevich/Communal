<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Modules\Apartment\Models\Monitoring;

class MonitoringExport implements WithHeadings, FromQuery, WithMapping, WithChunkReading
{

    public function __construct(
        protected ?int   $regionId,
        protected ?array $filters = []
    )
    {
    }

    public function query()
    {
        $dateFrom = $this->filters['date_from'] ?? null;
        $dateTo   = $this->filters['date_to'] ?? null;

        return Monitoring::query()
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
            ])
            ->where('region_id', $this->regionId)
            ->when($dateFrom && $dateTo, function ($query) use ($dateFrom, $dateTo) {
                $query->whereBetween('created_at', [
                    Carbon::parse($dateFrom)->startOfDay(),
                    Carbon::parse($dateTo)->endOfDay()
                ]);
            });
    }

    public function map($monitoring): array
    {
        return [
            $monitoring->id,
            $monitoring?->region?->name_uz ?? '',
            $monitoring?->district?->name_uz ?? '',
            $monitoring?->status?->name ?? '',
            $monitoring?->user?->full_name ?? '',
            $monitoring?->monitoringType?->name ?? '',
            $monitoring?->regulation?->place?->name ?? '',
            $monitoring?->regulation?->violationType?->name ?? '',
            optional($monitoring->created_at)->format('Y-m-d'),
            $monitoring?->base?->name ?? '',
            $monitoring?->company?->company_name ?? '',
            $monitoring->address ?? ($monitoring?->apartment?->street_name . ' ' . $monitoring?->apartment?->home_name),
            $monitoring->address_commit ?? '',
            $monitoring->additional_comment ?? '',
            $monitoring?->regulation?->fish ?? $monitoring?->regulation?->organization_name ?? '',
            $monitoring?->violation?->deadline ?? '',
            $monitoring->fine ? $monitoring->fine->series . $monitoring->fine->number : '',
            $monitoring->fine ? $monitoring->fine->decision_series . $monitoring->fine->decision_number : '',
            $monitoring->fine ? $monitoring->fine->status_name : '',
            $monitoring->fine ? $monitoring->fine->main_punishment_amount : '',
            $monitoring->fine ? $monitoring->fine->paid_amount : '',
            optional($monitoring->confirmRegulationHistory)->created_at,
        ];
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function headings(): array
    {
        return [
            'ID',
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
            'Ko\'rsatma bajarilgan sana'
        ];
    }
}
