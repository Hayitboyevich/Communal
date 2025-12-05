<?php

namespace Modules\Apartment\Const;

class ObjectChecklistStatus
{
    const NOT_ACTIVE = 1; // Monitoring otkazilmagan
    const PROGRESS = 2; // jarayonda
    const DONE = 3; // Topshirilgan
    const NEED_REPAIR = 4; // tamirtalab
}
