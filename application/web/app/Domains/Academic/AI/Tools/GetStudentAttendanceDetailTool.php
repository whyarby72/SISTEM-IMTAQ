<?php

namespace App\Domains\Academic\AI\Tools;

use App\Domains\Academic\AI\AcademicAiAttendanceReader;
use App\Domains\Academic\AI\AcademicAiAuthorization;
use App\Domains\Academic\AI\Contracts\AcademicAiTool;
use App\Domains\Academic\AI\Contracts\AcademicAiToolContext;
use App\Domains\Academic\AI\Contracts\AcademicAiToolResult;
use App\Domains\Academic\AI\Contracts\GetStudentAttendanceDetailRequest;
use App\Shared\Core\Models\Student;

final class GetStudentAttendanceDetailTool implements AcademicAiTool
{
    public function __construct(private readonly AcademicAiAuthorization $authorization, private readonly AcademicAiAttendanceReader $reader) {}

    public function name(): string
    {
        return 'get_student_attendance_detail';
    }

    public function description(): string
    {
        return 'Return bounded factual attendance records for one authorized student and period.';
    }

    public function inputSchema(): array
    {
        return ['student_id' => ['type' => 'uuid', 'required' => true], 'period_start' => ['type' => 'date', 'required' => true], 'period_end' => ['type' => 'date', 'required' => true], 'status' => ['type' => 'enum', 'values' => ['PRESENT', 'PERMISSION', 'SICK', 'ABSENT'], 'required' => false], 'page' => ['type' => 'integer', 'required' => false], 'limit' => ['type' => 'integer', 'max' => 100, 'required' => false]];
    }

    public function execute(AcademicAiToolContext $context, array $input): AcademicAiToolResult
    {
        $this->authorization->requireReadAccess($context->user);
        $request = GetStudentAttendanceDetailRequest::fromArray($input);
        $student = Student::query()->find($request->studentId);
        if ($student === null) {
            return new AcademicAiToolResult($this->name(), 'NOT_FOUND', ['student_id' => $request->studentId]);
        }
        $read = $this->reader->studentDetails($student, $request->periodStart, $request->periodEnd, $request->status, $request->page, $request->limit);

        return AcademicAiToolResult::ok($this->name(), $context, [
            'filters' => ['student_id' => (string) $student->id, 'period_start' => $request->periodStart->toDateString(), 'period_end' => $request->periodEnd->toDateString(), 'status' => $request->status, 'page' => $request->page, 'limit' => $request->limit],
            'entities' => ['student' => ['student_id' => (string) $student->id, 'display_name' => $student->full_name]],
            'records' => $read['records'],
            'warnings' => $read['warnings'],
            'semantic_regime' => $read['semantic_regime'],
            'canonical_entity_ids' => [(string) $student->id],
            'basis_count' => $read['total'],
        ]);
    }
}
