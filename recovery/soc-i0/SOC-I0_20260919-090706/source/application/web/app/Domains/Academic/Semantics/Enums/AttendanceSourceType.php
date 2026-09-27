<?php

namespace App\Domains\Academic\Semantics\Enums;

enum AttendanceSourceType: string
{
    case LIVE_TRANSACTIONAL = 'LIVE_TRANSACTIONAL';
    case LEGACY_MONTHLY_SNAPSHOT = 'LEGACY_MONTHLY_SNAPSHOT';
}
