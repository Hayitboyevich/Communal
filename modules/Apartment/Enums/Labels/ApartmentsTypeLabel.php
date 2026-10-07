<?php

namespace Modules\Apartment\Enums\Labels;

use Modules\Apartment\Enums\ApartmentsTypeEnum;

class ApartmentsTypeLabel
{
    public static function getLabel(ApartmentsTypeEnum $type): string
    {
        return match ($type) {
            ApartmentsTypeEnum::BSK => 'Boshqaruv Servis Kompaniyasi',
            ApartmentsTypeEnum::UJMSH => 'Uy-joy mulkdorlari shirkati',
            ApartmentsTypeEnum::OOB => 'O\'z o\'zini boshqarish',
            ApartmentsTypeEnum::YATT => 'Yakka tartibdagi tadbirkor',
            ApartmentsTypeEnum::BOSHQARUVSIZ => 'Boshqaruvsiz',
            ApartmentsTypeEnum::FILIAL => 'Filial'
        };
    }
}
