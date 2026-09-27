<?php

namespace App\Domains\Academic\Semantics\Enums;

enum CanonicalAttendanceStatus: string
{
    case PRESENT = 'PRESENT';
    case PERMISSION = 'PERMISSION';
    case SICK = 'SICK';
    case ABSENT = 'ABSENT';
}
