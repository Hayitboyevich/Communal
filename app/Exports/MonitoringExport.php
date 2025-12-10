<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Modules\Apartment\Models\Monitoring;

class MonitoringExport implements FromCollection, WithHeadings
{

    public function __construct(
        protected ?int $regionId
    ){}

    public function collection()
    {
        return Monitoring::query()
            ->with([
                'user',
                'monitoringType',
                'base',
                'company',
                'apartment',
                'district',
                'status',
                'regulation',
                'violation',
                'fine',
            ])
            ->where('region_id', $this->regionId)
            ->get()
            ->map(function ($monitoring) {
                return [
                    $monitoring->id,
                    $monitoring?->district?->name_uz ?? '',
                    $monitoring?->status?->name ?? '',
                    $monitoring?->user?->full_name ?? '',
                    $monitoring?->monitoringType?->name ?? '',
                    $monitoring?->regulation?->place?->name ?? '',
                    $monitoring?->regulation?->violationType?->name ?? '',
                    date_format($monitoring->created_at, 'Y-m-d') ?? '',
                    $monitoring?->base?->name ?? '',
                    $monitoring?->company?->company_name ?? '',
                    $monitoring?->apartment?->street_name .' '. $monitoring?->apartment?->home_name ?? '',
                    $monitoring->address_commit ?? '',
                    $monitoring->address ?? '',
                    $monitoring->additional_comment ?? '',
                    $monitoring?->regulation?->fish ?? $monitoring?->regulation?->organization_name ?? '',
                    $monitoring?->violation?->deadline ?? '',
                    $monitoring->fine ? $monitoring->fine->series .''.$monitoring->fine->number : '',
                    $monitoring->fine ? $monitoring->fine->decision_series .''.$monitoring->fine->decision_number : '',
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
            'Manzil',
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
