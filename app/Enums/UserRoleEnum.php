<?php

namespace App\Enums;

enum UserRoleEnum: int
{
    case ADMIN = 1;
    case INSPECTOR = 2;
    case WATER_INSPECTOR = 3;
    case HR = 4;
    case MANAGER = 5;
    case WATER_HR = 6;
    case RES_VIEWER = 7;
    case APARTMENT_HR = 10;
    case APARTMENT_INSPECTOR = 8;
    case APARTMENT_MANAGER = 9;
    case APARTMENT_VIEWER = 11;
    case CADASTR_HR = 12;
    case CADASTR_USER = 13;

    case OGOH = 101;
}
