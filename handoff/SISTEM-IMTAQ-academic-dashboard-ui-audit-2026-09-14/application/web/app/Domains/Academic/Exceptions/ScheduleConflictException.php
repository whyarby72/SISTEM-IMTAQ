<?php

namespace App\Domains\Academic\Exceptions;

use RuntimeException;

class ScheduleConflictException extends RuntimeException
{
    public function __construct(public readonly array $conflict)
    {
        parent::__construct('Academic schedule conflict: '.$conflict['conflict_type']);
    }
}
