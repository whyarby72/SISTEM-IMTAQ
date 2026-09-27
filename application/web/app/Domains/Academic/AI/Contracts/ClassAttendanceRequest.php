<?php

namespace App\Domains\Academic\AI\Contracts;

use Illuminate\Support\Carbon;

abstract class ClassAttendanceRequest
{
    public function __construct(
        public readonly string $classId,
        public readonly Carbon $periodStart,
        public readonly Carbon $periodEnd,
    ) {}

    protected static function base(array $input): array
    {
        return AcademicAiInput::validated($input, [
            'class_id' => ['required', 'uuid'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date'],
        ]);
    }
}
