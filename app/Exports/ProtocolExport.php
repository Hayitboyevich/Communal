<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Modules\Water\Models\Protocol;

class ProtocolExport implements FromCollection, WithHeadings
{

    public function __construct(
        protected ?int $regionId
    ){}

    public function collection()
    {
        return Protocol::query()
            ->with([
                'inspector',
                'protocolType',
                'district',
                'defect',
                'defect',
                'status',
            ])
            ->where('region_id', $this->regionId)
            ->get()
            ->map(function ($protocol){
                return [
                    $protocol?->district?->name_uz ?? '',
                    $protocol?->status?->name ?? '',
                    $protocol?->inspector?->full_name ?? '',
                    $protocol?->protocolType?->name ?? '',
                    $protocol?->defect?->name ?? '',
                    date_format($protocol->created_at, 'Y-m-d') ?? '',
                    $protocol->address ?? '',
                    $protocol->description ?? '',
                    $protocol->inn ?? '',
                    $protocol->expertise_name ?? '',
                    $protocol->pin ?? '',
                    $protocol->birth_date ?? '',
                    $protocol->functionary_name ?? '',
                    $protocol->phone ?? '',
                    $protocol->self_government_name ?? '',
                    $protocol->participant_name ?? '',
                    $protocol->defect_information ?? '',
                    $protocol->defect_comment ?? '',
                    $protocol->comment ?? '',
                    $protocol->fine ? $protocol->fine?->series .''.$protocol->fine->number : '',
                    $protocol->fine ? $protocol->fine->decision_series .''.$protocol->fine->decision_number : '',
                    $protocol->fine ? $protocol->fine->status_name : '',
                    $protocol->fine ? $protocol->fine->main_punishment_amount : '',
                    $protocol->fine ? $protocol->fine->paid_amount : '',
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Tuman',
            'Holati',
            'Inspektor',
            'O\'rganish turi',
            'Kamchilik turi',
            'Yaratilgan sana',
            'Address',
            'Qoshimcha malumot',
            'Korxona inn',
            'Korxona nomi',
            'Mansabdor shaxs jshir',
            'Mansabdor shaxs tug\'ilgan sana',
            'Mansabdor shaxs',
            'Mansabdor shaxs telefon raqami',
            'Fuqaroni ozini ozi boshqarish organi',
            'Boshqa ishtirok etuvchi',
            'Kamchilik haqida ma\'lumot',
            'Kamchilik izoh',
            'Izoh',
            'Bayonnoma seriya va raqami',
            'Qaror seriya va raqami',
            'Mamuriy holati',
            'Jarima miqdori',
            'To\'langan miqdor'
        ];
    }
}
