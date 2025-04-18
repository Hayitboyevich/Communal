<?php

namespace App\Enums;

enum UserStatusEnum: int
{
    case ACTIVE = 1;
    case INACTIVE = 2;
    case RELEASED = 3;
}
