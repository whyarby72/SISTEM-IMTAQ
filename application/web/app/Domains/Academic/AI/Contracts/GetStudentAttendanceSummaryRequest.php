<?php

namespace App\Domains\Academic\AI\Contracts;

final class GetStudentAttendanceSummaryRequest extends StudentAttendanceRequest
{
    public static function fromArray(array $input): self
    {
        $data = self::base($input);

        return new self($data['student_id'], $data['period_start'], $data['period_end']);
    }
}
