<?php

namespace App\Enums;

enum LetterType: int
{
    case APARTMENT = 1;
    case WATER = 2;

    public static function current(): ?self
    {
        return request()->attributes->get('letter_type');
    }
}
