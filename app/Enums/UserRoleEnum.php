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
}
