<?php

namespace Modules\Water\Enums;

enum ProtocolStatusEnum: int
{
    case ENTER_RESULT = 1;  // Natija kiritishda
    case NOT_DEFECT = 2; // Kamchilik aniqlanmadi
    case FORMING = 3; // Korsatma shakllantirish
    case FORMED = 4; //Korsatma shakllantirildi
    case ADMINISTRATIVE = 5; // Mamuriy qilindi
    case CONFIRMED = 6; //Korsatma bajarildi
    case HMQO = 7; // HMQOga yuborildi
    case CONFIRM_RESULT = 8; // Korsatmani tasdiqlash
    case CONFIRM_NOT_DEFECT = 9; // Kamchilik aniqlanmadini tasdiqlash
    case NEW = 10; // Yangi
}
