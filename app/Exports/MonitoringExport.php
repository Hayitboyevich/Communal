<?php

namespace App\Exports;

use App\Models\Region;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Modules\Apartment\Models\Monitoring;

class MonitoringExport implements FromCollection, WithHeadings
{

    public function __construct(
        protected ?int   $regionId,
        protected ?array $filters = []
    )
    {
    }

    public function collection()
    {
        $dateFrom = null;
        $dateTo = null;
        if (!empty($this->filters)) {
            $dateFrom = $this->filters['date_from'] ?? null;
            $dateTo = $this->filters['date_to'] ?? null;
        }


        return Monitoring::query()
            ->with([
                'user',
                'monitoringType',
                'base',
                'company',
                'apartment',
                'district',
                'region',
                'status',
                'regulation',
                'violation',
                'fine',
            ])
            ->where('region_id', $this->regionId)
            ->when($dateFrom && $dateTo, function ($query) use ($dateFrom, $dateTo) {

                $startDate = Carbon::parse($dateFrom)->startOfDay();
                $endDate   = Carbon::parse($dateTo)->endOfDay();

                $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->get()
            ->map(function ($monitoring) {
                return [
                    $monitoring->id,
                    $monitoring?->region?->name_uz ?? '',
                    $monitoring?->district?->name_uz ?? '',
                    $monitoring?->status?->name ?? '',
                    $monitoring?->user?->full_name ?? '',
                    $monitoring?->monitoringType?->name ?? '',
                    $monitoring?->regulation?->place?->name ?? '',
                    $monitoring?->regulation?->violationType?->name ?? '',
                    date_format($monitoring->created_at, 'Y-m-d') ?? '',
                    $monitoring?->base?->name ?? '',
                    $monitoring?->company?->company_name ?? '',
                    $monitoring?->apartment?->street_name . ' ' . $monitoring?->apartment?->home_name ?? $monitoring->address ?? '',
                    $monitoring->address_commit ?? '',
                    $monitoring->additional_comment ?? '',
                    $monitoring?->regulation?->fish ?? $monitoring?->regulation?->organization_name ?? '',
                    $monitoring?->violation?->deadline ?? '',
                    $monitoring->fine ? $monitoring->fine->series . '' . $monitoring->fine->number : '',
                    $monitoring->fine ? $monitoring->fine->decision_series . '' . $monitoring->fine->decision_number : '',
                    $monitoring->fine ? $monitoring->fine->status_name : '',
                    $monitoring->fine ? $monitoring->fine->main_punishment_amount : '',
                    $monitoring->fine ? $monitoring->fine->paid_amount : '',
                ];
            });
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
            'To\'langan miqdor'
        ];
    }
}
