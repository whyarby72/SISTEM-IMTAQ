<?php

namespace App\Domains\Academic\AI\Contracts;

final class GetClassAttendanceSummaryRequest extends ClassAttendanceRequest
{
    public static function fromArray(array $input): self
    {
        $data = self::base($input);

        return new self($data['class_id'], $data['period_start'], $data['period_end']);
    }
}
