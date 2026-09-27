<?php

namespace App\Domains\Academic\AI\Tools;

use App\Domains\Academic\AI\AcademicAiAttendanceReader;
use App\Domains\Academic\AI\AcademicAiAuthorization;
use App\Domains\Academic\AI\Contracts\AcademicAiTool;
use App\Domains\Academic\AI\Contracts\AcademicAiToolContext;
use App\Domains\Academic\AI\Contracts\AcademicAiToolResult;
use App\Domains\Academic\AI\Contracts\GetClassAttendanceRosterRequest;
use App\Domains\Academic\Models\AcademicClass;

final class GetClassAttendanceRosterTool implements AcademicAiTool
{
    public function __construct(private readonly AcademicAiAuthorization $authorization, private readonly AcademicAiAttendanceReader $reader) {}

    public function name(): string
    {
        return 'get_class_attendance_roster';
    }

    public function description(): string
    {
        return 'Return bounded factual per-student attendance aggregates for one authorized class and period.';
    }

    public function inputSchema(): array
    {
        return ['class_id' => ['type' => 'uuid', 'required' => true], 'period_start' => ['type' => 'date', 'required' => true], 'period_end' => ['type' => 'date', 'required' => true], 'page' => ['type' => 'integer', 'required' => false], 'limit' => ['type' => 'integer', 'max' => 100, 'required' => false]];
    }

    public function execute(AcademicAiToolContext $context, array $input): AcademicAiToolResult
    {
        $this->authorization->requireReadAccess($context->user);
        $request = GetClassAttendanceRosterRequest::fromArray($input);
        $class = AcademicClass::query()->find($request->classId);
        if ($class === null) {
            return new AcademicAiToolResult($this->name(), 'NOT_FOUND', ['class_id' => $request->classId]);
        }
        $read = $this->reader->classRoster($class, $request->periodStart, $request->periodEnd, $request->page, $request->limit);

        return AcademicAiToolResult::ok($this->name(), $context, [
            'filters' => ['class_id' => (string) $class->id, 'period_start' => $request->periodStart->toDateString(), 'period_end' => $request->periodEnd->toDateString(), 'page' => $request->page, 'limit' => $request->limit],
            'entities' => ['class' => ['class_id' => (string) $class->id, 'display_name' => $class->display_name]],
            'records' => $read['records'],
            'warnings' => $read['warnings'],
            'semantic_regime' => $read['semantic_regime'],
            'canonical_entity_ids' => [(string) $class->id],
            'basis_count' => $read['total'],
        ]);
    }
}
