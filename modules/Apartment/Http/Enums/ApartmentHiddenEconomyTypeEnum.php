<?php

namespace Modules\Apartment\Http\Enums;

enum ApartmentHiddenEconomyTypeEnum: int
{
    case YER_TOLA = 1;
    case FASAD = 2;
    case TOM = 3;

    public static function types(): array
    {
        return [
            self::YER_TOLA->value,
            self::FASAD->value,
            self::TOM->value
        ];
    }
}
