<?php

namespace Modules\Water\Enums;

enum ProtocolStatusEnum: int
{
    case ENTER_RESULT = 1;
    case NOT_DEFECT = 2;
    case FORMING = 3;
    case FORMED = 4;
    case ADMINISTRATIVE = 5;
    case CONFIRMED = 6;
    case HMQO = 7;
}
