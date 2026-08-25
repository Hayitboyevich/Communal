<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Modules\Apartment\Http\Enums\ApartmentHiddenEconomyTypeEnum;
use Modules\Apartment\Models\Apartment;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ApartmentHiddenEconomyExport implements FromQuery, WithHeadings, WithMapping, WithChunkReading, WithStyles, ShouldAutoSize
{
    public function __construct(
        protected int   $regionId,
        protected array $filters = []
    )
    {
    }

    public function query()
    {
        $yerTolaFilter = $this->monitoringsFilter(1, null);
        $tomFilter = $this->monitoringsFilter(2, [8, 9]);
        $fasadFilter = $this->monitoringsFilter(2, [10]);

        $query = Apartment::query()
            ->where('home_integration', 1)
            ->whereHas('company', fn($q) => $q->where('region_id', $this->regionId))
            ->when(!empty($this->filters['district_id']), fn($q) => $q->whereHas('company',
                fn($q) => $q->where('district_id', $this->filters['district_id'])))
            ->when(!empty($this->filters['company_id']), fn($q) => $q->where('company_id', $this->filters['company_id']))
            ->when(!empty($this->filters['home_id']), fn($q) => $q->where('home_id', $this->filters['home_id']))
            ->with('company')
            ->withCount([
                'monitorings as yer_tola_count' => $yerTolaFilter,
                'monitorings as tom_count' => $tomFilter,
                'monitorings as fasad_count' => $fasadFilter,
            ]);

        if (!empty($this->filters['type'])) {
            [$monitoringTypeId, $placeId] = $this->resolveTypeParams((int) $this->filters['type']);
            $monitoringsFilter = $this->monitoringsFilter($monitoringTypeId, $placeId);
            $hiddenEconomyFilter = $this->hiddenEconomyFilter($monitoringTypeId, $placeId);

            $query->when(!empty($this->filters['status']) and $this->filters['status'] == 1,
                fn($q) => $q->whereDoesntHave('monitorings', $monitoringsFilter)->whereDoesntHave('apartmentHiddenEconomy', $hiddenEconomyFilter)
            )->when(!empty($this->filters['status']) and $this->filters['status'] == 2,
                fn($q) => $q->where(function ($q) use ($monitoringsFilter, $hiddenEconomyFilter) {
                    $q->whereHas('monitorings', $monitoringsFilter)->orWhereHas('apartmentHiddenEconomy', $hiddenEconomyFilter);
                })
            );
        }

        return $query;
    }

    private function resolveTypeParams(int $type): array
    {
        return match ($type) {
            ApartmentHiddenEconomyTypeEnum::YER_TOLA->value => [1, null],
            ApartmentHiddenEconomyTypeEnum::TOM->value => [2, [8, 9]],
            ApartmentHiddenEconomyTypeEnum::FASAD->value => [2, [10]],
        };
    }

    private function monitoringsFilter(int $monitoringTypeId, ?array $placeId): \Closure
    {
        return function ($query) use ($monitoringTypeId, $placeId) {
            $query->when($monitoringTypeId == 1, function ($query) use ($monitoringTypeId) {
                $query->where('monitoring_type_id', $monitoringTypeId)
                    ->where(function ($query) use ($monitoringTypeId) {
                        $query->whereHas('regulation', function ($query) {
                            $query->whereIn('place_id', [1, 2]);
                        })->orWhereHas('apartmentHiddenEconomy', function ($query) use ($monitoringTypeId) {
                            $query->where('monitoring_type_id', $monitoringTypeId);
                        })->with('regulation');
                    });
            })->when($monitoringTypeId == 2, function ($query) use ($monitoringTypeId, $placeId) {
                $query->where('monitoring_type_id', $monitoringTypeId)
                    ->where(function ($query) use ($monitoringTypeId, $placeId) {
                        $query->whereHas('regulation', function ($query) use ($placeId) {
                            $query->whereIn('place_id', $placeId);
                        })->orWhereHas('apartmentHiddenEconomy', function ($query) use ($monitoringTypeId, $placeId) {
                            $query->when(!empty($placeId) && in_array(8, $placeId) && in_array(9, $placeId), function ($query) use ($monitoringTypeId) {
                                $query->where(['monitoring_type_id' => $monitoringTypeId, 'hidden_economy_type' => ApartmentHiddenEconomyTypeEnum::TOM->value]);
                            })->when(!empty($placeId) && in_array(10, $placeId), function ($query) use ($monitoringTypeId) {
                                $query->where(['monitoring_type_id' => $monitoringTypeId, 'hidden_economy_type' => ApartmentHiddenEconomyTypeEnum::FASAD->value]);
                            });
                        })
                            ->with('regulation');
                    });
            });
        };
    }

    private function hiddenEconomyFilter(int $monitoringTypeId, ?array $placeId): \Closure
    {
        return function ($query) use ($monitoringTypeId, $placeId) {
            $query->when($monitoringTypeId == 1, function ($query) use ($monitoringTypeId) {
                $query->where('monitoring_type_id', $monitoringTypeId);
            })->when(!empty($placeId) && in_array(8, $placeId) && in_array(9, $placeId), function ($query) use ($monitoringTypeId) {
                $query->where(['monitoring_type_id' => $monitoringTypeId, 'hidden_economy_type' => ApartmentHiddenEconomyTypeEnum::TOM->value]);
            })->when(!empty($placeId) && in_array(10, $placeId), function ($query) use ($monitoringTypeId) {
                $query->where(['monitoring_type_id' => $monitoringTypeId, 'hidden_economy_type' => ApartmentHiddenEconomyTypeEnum::FASAD->value]);
            });
        };
    }

    public function map($apartment): array
    {
        return [
            $apartment->home_id,
            $apartment->street_name,
            $apartment->company?->company_name ?? '',
            $apartment->home_name,
            $apartment->yer_tola_count,
            $apartment->tom_count,
            $apartment->fasad_count,
        ];
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function headings(): array
    {
        return [
            'Xonadon ID',
            'Ko\'cha',
            'Korxona',
            'Xonadon',
            'Yer to\'la',
            'Tom',
            'Fasad',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        $lastColumn = $sheet->getHighestColumn();

        $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(24);

        $sheet->getStyle("A1:{$lastColumn}{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D9D9D9']],
            ],
        ]);

        $sheet->getStyle("E1:G{$lastRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle("A1:A{$lastRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->freezePane('A2');

        return [];
    }
}
