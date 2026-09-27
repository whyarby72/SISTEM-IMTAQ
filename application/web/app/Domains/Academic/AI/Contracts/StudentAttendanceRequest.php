<?php

namespace App\Domains\Academic\AI\Contracts;

use Illuminate\Support\Carbon;

abstract class StudentAttendanceRequest
{
    public function __construct(
        public readonly string $studentId,
        public readonly Carbon $periodStart,
        public readonly Carbon $periodEnd,
    ) {}

    protected static function base(array $input, array $extraRules = []): array
    {
        return AcademicAiInput::validated($input, [
            'student_id' => ['required', 'uuid'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date'],
            ...$extraRules,
        ]);
    }
}
