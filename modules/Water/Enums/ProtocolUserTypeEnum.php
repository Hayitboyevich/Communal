<?php

namespace Modules\Water\Enums;

/**
 * protocols.user_type — Javobgar turi (2-qadamda frontenddan keladi)
 */
enum ProtocolUserTypeEnum: int
{
    case PHYSICAL = 1; // Jismoniy shaxs
    case LEGAL = 2;    // Yuridik shaxs

    public function label(): string
    {
        return match ($this) {
            self::PHYSICAL => 'Jismoniy shaxs',
            self::LEGAL => 'Yuridik shaxs',
        };
    }
}
