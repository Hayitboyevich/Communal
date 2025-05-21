<?php

namespace Modules\Apartment\Const;

class MonitoringHistoryType
{
    const CREATE_FIRST = 1;
    const VIOLATION_DETECTED = 2;
    const REGULATION_FORMED = 3;
    const REJECT_DEFECT = 5;
    const REJECT = 7;
    const CONFIRMED = 13;
    const VIOLATION_NOT_DETECTED = 20;
    const REJECT_VIOLATION_NOT_DETECTED = 21;
    const CONFIRM_VIOLATION = 22;
    const REGULATION_NOT_DETECTED = 30;
    const REJECT_REGULATION_NOT_DETECTED = 31;
    const CONFIRM_REGULATION = 32;
    const SEND_COURT = 40;
    const SEND_MIIB = 50;
    const DONE = 100;
    const HMQO = 10;

    public static function getLabel($type): string
    {
        switch ($type) {
            case self::CREATE_FIRST:
                return 'Monitoring yaratildi';
            case self::VIOLATION_DETECTED:
                return 'Qoidabuzarlik aniqlandi';
            case self::VIOLATION_NOT_DETECTED:
                return 'Qoidabuzarlik aniqlanmadi tasdiqlash';
            case self::REJECT_VIOLATION_NOT_DETECTED:
                return 'Qoidabuzarlik aniqlanmadi rad qilindi';
            case self::CONFIRM_VIOLATION:
                return 'Qoidabuzarlik aniqlanmadi tasdiqlandi';
            case self::REGULATION_FORMED:
                return 'Ko\'rsatma shakllantirildi';
            case self:: REGULATION_NOT_DETECTED:
                return 'Ko\'rsatma bajarildi tasdiqlash';
            case self:: REJECT_REGULATION_NOT_DETECTED:
                return 'Ko\'rsatma bajarildi rad qilindi';
            case self::CONFIRM_REGULATION:
                return 'Ko\'rsatma bajarildi tasdiqlandi';
            case self::SEND_COURT:
                return 'Sudga yuborildi';
            case self::SEND_MIIB:
                return 'MIBga yuborildi';
            case self::DONE:
                return 'Chora ko\'rildi';
            case self::HMQO:
                return 'HMQOga yuborildi';

            default:
                return 'Noma’lum tur';
        }
    }
}
