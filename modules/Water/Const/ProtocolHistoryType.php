<?php

namespace Modules\Water\Const;

class ProtocolHistoryType
{
    const CREATE_FIRST = 1;
    const CREATE_SECOND = 2;
    const CREATE_THIRD = 3;
    const CONFIRM_DEFECT = 4;
    const REJECT_DEFECT = 5;
    const ATTACH_INSPECTOR = 6;
    const REJECT = 7;
    const CONFIRM_NOT_DEFECT = 8;
    const NOT_DEFECT = 9;
    const CONFIRM_RESULT = 10;
    const REJECT_RESULT = 11;
    const SEND_HMQO = 12;
    const CONFIRMED = 13;

    public static function getLabel($type): string
    {
        switch ($type) {
            case self::CREATE_FIRST:
                return 'Protocol yaratildi';
            case self::CREATE_SECOND:
                return 'Kamchilik aniqlandi';
            case self::CREATE_THIRD:
                return 'Ko\'rsatma shakllantirildi';
            case self::CONFIRM_DEFECT:
                return 'Kamchilik aniqlanmadi tasdiqlandi';
            case self::REJECT_DEFECT:
                return 'Kamchilik aniqlanmadi rad etildi';
            case self::ATTACH_INSPECTOR:
                return 'Inspektor biriktirildi';
            case self::REJECT:
                return 'Ko\'rsatma rad qilindi';
            case self::CONFIRM_NOT_DEFECT:
                return 'Kamchilik aniqlanmadi tasdiqlash';
            case self::CONFIRM_RESULT:
                return 'Ko\'rsatma tasdiqlash uchun yuborildi';
            case self::CONFIRMED:
                return 'Ko\'rsatma tasdiqlandi';
            case self::REJECT_RESULT:
                return 'Ko\'rsatma rad qilindi';
            case self::SEND_HMQO:
                return 'HMQOga yuborildi';
            case self::NOT_DEFECT:
                return 'Kamchilik aniqlanmadi';
            default:
                return 'Noma’lum tur';
        }
    }
}
