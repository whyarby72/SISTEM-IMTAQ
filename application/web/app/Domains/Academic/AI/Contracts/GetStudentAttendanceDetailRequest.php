<?php

namespace App\Domains\Academic\AI\Contracts;

use Illuminate\Support\Carbon;

final class GetStudentAttendanceDetailRequest extends StudentAttendanceRequest
{
    public function __construct(
        string $studentId,
        Carbon $periodStart,
        Carbon $periodEnd,
        public readonly ?string $status = null,
        public readonly int $page = 1,
        public readonly int $limit = 50,
    ) {
        parent::__construct($studentId, $periodStart, $periodEnd);
    }

    public static function fromArray(array $input): self
    {
        $data = self::base($input, [
            'status' => ['nullable', 'string', 'in:PRESENT,PERMISSION,SICK,ABSENT'],
            'page' => ['integer', 'min:1', 'max:1000'],
            'limit' => ['integer', 'min:1', 'max:100'],
        ]) + ['status' => null, 'page' => 1, 'limit' => 50];
        $data = AcademicAiInput::validated($data, [
            'student_id' => ['required', 'uuid'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date'],
            'status' => ['nullable', 'string', 'in:PRESENT,PERMISSION,SICK,ABSENT'],
            'page' => ['integer', 'min:1', 'max:1000'],
            'limit' => ['integer', 'min:1', 'max:100'],
        ]);

        return new self($data['student_id'], $data['period_start'], $data['period_end'], $data['status'], $data['page'], $data['limit']);
    }
}
