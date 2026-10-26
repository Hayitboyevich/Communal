<?php

namespace App\Authorization;

use App\Enums\LetterType;
use App\Enums\UserRoleEnum;

final class LetterPermission
{
    private const MAP = [
        LetterType::APARTMENT->value => [
            'view' => [
                UserRoleEnum::APARTMENT_MANAGER->value => 'region',
                UserRoleEnum::REG_VIEWER->value => 'region',
                UserRoleEnum::APARTMENT_VIEWER->value => 'all',
            ]
        ],
    ];
}
