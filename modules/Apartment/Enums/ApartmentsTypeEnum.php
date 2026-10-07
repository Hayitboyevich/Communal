<?php

namespace Modules\Apartment\Enums;

use PhpParser\Node\Stmt\Label;

enum ApartmentsTypeEnum: int
{
    case BSK = 1; // Boshqaruv Servis Kompaniyasi
    case UJMSH = 2; // Uy-joy mulkdorlari shirkati
    case OOB = 3; // O'z o'zini boshqarish
    case YATT = 4; // Yakka tartibdagi tadbirkor
    case BOSHQARUVSIZ = 5; // Boshqaruvsiz
    case FILIAL = 6; // Filial
}
