<?php

namespace App\Domains\Academic\Semantics\Enums;

enum EligibilityStatus: string
{
    case ELIGIBLE = 'ELIGIBLE';
    case NON_ELIGIBLE = 'NON_ELIGIBLE';
}
