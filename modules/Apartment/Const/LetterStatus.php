<?php

namespace Modules\Apartment\Const;

class LetterStatus
{
    CONST New = 1;
    CONST SuccessDelivered = 3; // yetkazildi
    CONST Process = 2; // jarayonda
    CONST ReceiverDead = 4; // egasi olgan
    CONST ReceiverNotLivesThere = 5; // egasi u joyda yashamaydi
    CONST IncompleteAddress = 6; // address toliq emas
    CONST ReceiverRefuse = 7; // egasi otkaz berdi
    CONST NotAtHome = 8; // uyda yoq
    CONST DidntAppearOnNotice = 9; // Xabarnoma korsatilmadi
    CONST Defect = 10; // address topilmadi
    CONST TryPerform = 11; // qayta yuborildi
    CONST OrganizationWithGivenAddressNotFound = 12; //addresda egasi yoq kompaniyani


    public static function getStatus($status = null)
    {
        return match ($status) {
            0 => self::SuccessDelivered,
            1 => self::ReceiverDead,
            2 => self::ReceiverNotLivesThere,
            3 => self::IncompleteAddress,
            4 => self::ReceiverRefuse,
            5 => self::NotAtHome,
            6=> self::DidntAppearOnNotice,
            7 => self::Defect,
            8 => self::TryPerform,
            9 => self::OrganizationWithGivenAddressNotFound,
            default => self::Process,
        };
    }

}
