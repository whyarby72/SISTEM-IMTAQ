<?php

namespace App\Domains\Academic\AI\Tools;

use App\Domains\Academic\AI\AcademicAiAttendanceReader;
use App\Domains\Academic\AI\AcademicAiAuthorization;
use App\Domains\Academic\AI\Contracts\AcademicAiTool;
use App\Domains\Academic\AI\Contracts\AcademicAiToolContext;
use App\Domains\Academic\AI\Contracts\AcademicAiToolResult;
use App\Domains\Academic\AI\Contracts\GetClassAttendanceSummaryRequest;
use App\Domains\Academic\Models\AcademicClass;

final class GetClassAttendanceSummaryTool implements AcademicAiTool
{
    public function __construct(private readonly AcademicAiAuthorization $authorization, private readonly AcademicAiAttendanceReader $reader) {}

    public function name(): string
    {
        return 'get_class_attendance_summary';
    }

    public function description(): string
    {
        return 'Return canonical read-only attendance metrics for one authorized class and period.';
    }

    public function inputSchema(): array
    {
        return ['class_id' => ['type' => 'uuid', 'required' => true], 'period_start' => ['type' => 'date', 'required' => true], 'period_end' => ['type' => 'date', 'required' => true]];
    }

    public function execute(AcademicAiToolContext $context, array $input): AcademicAiToolResult
    {
        $this->authorization->requireReadAccess($context->user);
        $request = GetClassAttendanceSummaryRequest::fromArray($input);
        $class = AcademicClass::query()->find($request->classId);
        if ($class === null) {
            return new AcademicAiToolResult($this->name(), 'NOT_FOUND', ['class_id' => $request->classId]);
        }
        $read = $this->reader->classSummary($class, $request->periodStart, $request->periodEnd);

        return AcademicAiToolResult::ok($this->name(), $context, [
            'filters' => ['class_id' => (string) $class->id, 'period_start' => $request->periodStart->toDateString(), 'period_end' => $request->periodEnd->toDateString()],
            'entities' => ['class' => ['class_id' => (string) $class->id, 'display_name' => $class->display_name]],
            'metrics' => $read['summary'],
            'warnings' => $read['warnings'],
            'semantic_regime' => $read['semantic_regime'],
            'canonical_entity_ids' => [(string) $class->id],
            'basis_count' => $read['summary']['eligible_opportunities'],
        ]);
    }
}
