<?php

namespace App\Domains\Academic\Semantics\Enums;

enum AttendanceDataReadiness: string
{
    case COMPLETE = 'COMPLETE';
    case INCOMPLETE = 'INCOMPLETE';
    case NOT_STARTED = 'NOT_STARTED';
    case PARTIAL = 'PARTIAL';
    case BLOCKED = 'BLOCKED';
}
