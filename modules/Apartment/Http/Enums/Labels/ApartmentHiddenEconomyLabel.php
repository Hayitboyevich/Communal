<?php

namespace Modules\Apartment\Http\Enums\Labels;

use Modules\Apartment\Http\Enums\ApartmentHiddenEconomyTypeEnum;

class ApartmentHiddenEconomyLabel
{
    public static function get(ApartmentHiddenEconomyTypeEnum $type): string
    {
        return match ($type) {
            ApartmentHiddenEconomyTypeEnum::FASAD => 'Fasad nazorati',
            ApartmentHiddenEconomyTypeEnum::TOM => 'Tom nazorati',
            ApartmentHiddenEconomyTypeEnum::YER_TOLA => 'Yer to\'la nazorati'
        };
    }
}
