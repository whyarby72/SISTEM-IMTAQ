<?php

namespace App\Domains\Academic\AI\Tools;

use App\Domains\Academic\AI\AcademicAiAttendanceReader;
use App\Domains\Academic\AI\AcademicAiAuthorization;
use App\Domains\Academic\AI\Contracts\AcademicAiTool;
use App\Domains\Academic\AI\Contracts\AcademicAiToolContext;
use App\Domains\Academic\AI\Contracts\AcademicAiToolResult;
use App\Domains\Academic\AI\Contracts\GetStudentAttendanceSummaryRequest;
use App\Shared\Core\Models\Student;

final class GetStudentAttendanceSummaryTool implements AcademicAiTool
{
    public function __construct(private readonly AcademicAiAuthorization $authorization, private readonly AcademicAiAttendanceReader $reader) {}

    public function name(): string
    {
        return 'get_student_attendance_summary';
    }

    public function description(): string
    {
        return 'Return canonical read-only attendance metrics for one authorized student and period.';
    }

    public function inputSchema(): array
    {
        return ['student_id' => ['type' => 'uuid', 'required' => true], 'period_start' => ['type' => 'date', 'required' => true], 'period_end' => ['type' => 'date', 'required' => true]];
    }

    public function execute(AcademicAiToolContext $context, array $input): AcademicAiToolResult
    {
        $this->authorization->requireReadAccess($context->user);
        $request = GetStudentAttendanceSummaryRequest::fromArray($input);
        $student = Student::query()->find($request->studentId);
        if ($student === null) {
            return new AcademicAiToolResult($this->name(), 'NOT_FOUND', ['student_id' => $request->studentId]);
        }
        $read = $this->reader->studentSummary($student, $request->periodStart, $request->periodEnd);

        return AcademicAiToolResult::ok($this->name(), $context, [
            'filters' => ['student_id' => (string) $student->id, 'period_start' => $request->periodStart->toDateString(), 'period_end' => $request->periodEnd->toDateString()],
            'entities' => ['student' => ['student_id' => (string) $student->id, 'display_name' => $student->full_name]],
            'metrics' => $read['summary'],
            'warnings' => $read['warnings'],
            'semantic_regime' => $read['semantic_regime'],
            'canonical_entity_ids' => [(string) $student->id],
            'basis_count' => $read['summary']['eligible_opportunities'],
        ]);
    }
}
