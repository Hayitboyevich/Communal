<?php

namespace App\Enums;

enum UserRoleEnum: int
{
    case ADMIN = 1;
    case INSPECTOR = 2;
    case WATER_INSPECTOR = 3;
}
