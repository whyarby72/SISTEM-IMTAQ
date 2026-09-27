<?php

namespace App\Domains\Academic\AI\Contracts;

use Illuminate\Support\Carbon;

final class GetClassAttendanceRosterRequest extends ClassAttendanceRequest
{
    public function __construct(
        string $classId,
        Carbon $periodStart,
        Carbon $periodEnd,
        public readonly int $page = 1,
        public readonly int $limit = 100,
    ) {
        parent::__construct($classId, $periodStart, $periodEnd);
    }

    public static function fromArray(array $input): self
    {
        $data = self::base($input, [
            'page' => ['integer', 'min:1', 'max:1000'],
            'limit' => ['integer', 'min:1', 'max:100'],
        ]) + ['page' => 1, 'limit' => 100];
        $data = AcademicAiInput::validated($data, [
            'class_id' => ['required', 'uuid'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date'],
            'page' => ['integer', 'min:1', 'max:1000'],
            'limit' => ['integer', 'min:1', 'max:100'],
        ]);

        return new self($data['class_id'], $data['period_start'], $data['period_end'], $data['page'], $data['limit']);
    }
}
