<?php

namespace Modules\Apartment\Enums;

enum MonitoringStatusEnum: int
{
    case ENTER_RESULT = 1;  // Natija kiritishda
    case CONFIRM_DEFECT = 2; // TAsdiqlashda
    case NOT_DEFECT = 3; // Qoidabuzarlik aniqlanmadi
    case DEFECT = 4; //Qoidabuzarlik aniqlandi
    case FORMED = 5; //Qoidabuzarlik aniqlandi
    case ADMINISTRATIVE = 6; // Mamuriy qilindi
    case DONE = 7; //Korsatma bajarildi
    case HMQO = 8; // HMQOga yuborildi
    case CONFIRM_RESULT = 9; // Korsatmani tasdiqlash
    case COURT = 10;
    case MIB = 11;
    case SRYX = 12;
    case  FIXED= 13;

}
