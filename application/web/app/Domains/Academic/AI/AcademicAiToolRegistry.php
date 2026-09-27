<?php

namespace App\Domains\Academic\AI;

use App\Domains\Academic\AI\Contracts\AcademicAiTool;
use App\Domains\Academic\AI\Contracts\AcademicAiToolContext;
use App\Domains\Academic\AI\Contracts\AcademicAiToolResult;
use App\Domains\Academic\AI\Tools\GetClassAttendanceRosterTool;
use App\Domains\Academic\AI\Tools\GetClassAttendanceSummaryTool;
use App\Domains\Academic\AI\Tools\GetStudentAttendanceDetailTool;
use App\Domains\Academic\AI\Tools\GetStudentAttendanceSummaryTool;
use App\Domains\Academic\AI\Tools\ResolveStudentTool;
use InvalidArgumentException;

class AcademicAiToolRegistry
{
    /** @var array<string, AcademicAiTool> */
    private array $tools;

    public function __construct(
        ResolveStudentTool $resolveStudent,
        GetStudentAttendanceSummaryTool $studentSummary,
        GetStudentAttendanceDetailTool $studentDetail,
        GetClassAttendanceSummaryTool $classSummary,
        GetClassAttendanceRosterTool $classRoster,
    ) {
        $this->tools = collect([$resolveStudent, $studentSummary, $studentDetail, $classSummary, $classRoster])
            ->mapWithKeys(fn (AcademicAiTool $tool): array => [$tool->name() => $tool])
            ->all();
    }

    /** @return array<string, AcademicAiTool> */
    public function all(): array
    {
        return $this->tools;
    }

    public function get(string $name): AcademicAiTool
    {
        if (! isset($this->tools[$name])) {
            throw new InvalidArgumentException('Unknown Academic AI tool.');
        }

        return $this->tools[$name];
    }

    public function execute(string $name, AcademicAiToolContext $context, array $input): AcademicAiToolResult
    {
        return $this->get($name)->execute($context, $input);
    }
}
