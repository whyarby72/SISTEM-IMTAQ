<?php

namespace App\Domains\Academic\Semantics\Enums;

enum AttendanceAuthorityStatus: string
{
    case AUTHORITATIVE = 'AUTHORITATIVE';
    case NON_AUTHORITATIVE = 'NON_AUTHORITATIVE';
    case SOURCE_CONFLICT = 'SOURCE_CONFLICT';
    case DATA_INCOMPLETE = 'DATA_INCOMPLETE';
    case DATA_NOT_CERTIFIED = 'DATA_NOT_CERTIFIED';
}
