<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\ScheduleRule;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class ScheduleRuleConflictChecker
{
    public function __construct(private readonly AcademicClassScopeResolver $classScope) {}

    public function check(array $candidate, ?string $ignoreRuleId = null): Collection
    {
        $assignment = $candidate['teaching_assignment'] ?? null;
        $rules = ScheduleRule::query()->with(['teachingAssignment', 'weekNumbers'])
            ->where('workflow_status', 'ACTIVE')
            ->when($ignoreRuleId, fn ($query) => $query->whereKeyNot($ignoreRuleId))
            ->get();
        $conflicts = collect();

        foreach ($rules as $existing) {
            $existingAssignment = $existing->teachingAssignment;
            if (! $assignment || ! $existingAssignment) {
                continue;
            }

            $candidateClassIds = $this->classScope->forScheduleCandidate($candidate);
            $existingClassIds = $this->classScope->forScheduleRule($existing);
            $sharedClassIds = array_values(array_intersect($candidateClassIds, $existingClassIds));
            $resource = $sharedClassIds !== []
                ? 'CLASS_CONFLICT'
                : ($candidate['teacher_staff_id'] === $existingAssignment->teacher_staff_id ? 'TEACHER_CONFLICT' : null);
            if ($resource === null) {
                continue;
            }

            $date = $this->firstCommonOccurrence($candidate, $existing);
            if ($date !== null && $this->timesOverlap($candidate['start_time'], $candidate['end_time'], $existing->start_time, $existing->end_time)) {
                $conflicts->push([
                    'conflict_type' => $resource,
                    'candidate_rule_ref' => $candidate['id'] ?? null,
                    'conflicting_rule_ref' => $existing->id,
                    'resource_id' => $resource === 'CLASS_CONFLICT' ? $sharedClassIds[0] : $candidate['teacher_staff_id'],
                    'occurrence_date' => $date->toDateString(),
                    'severity' => 'HIGH',
                ]);
            }
        }

        return $conflicts;
    }

    private function firstCommonOccurrence(array $candidate, ScheduleRule $existing): ?CarbonImmutable
    {
        $from = CarbonImmutable::parse(max($candidate['effective_from'], $existing->effective_from->toDateString()));
        $until = CarbonImmutable::parse(min($candidate['effective_until'] ?? '9999-12-31', $existing->effective_until?->toDateString() ?? '9999-12-31'));
        $existingWeekNumbers = $existing->weekNumbers->pluck('week_no')->all();
        for ($date = $from; $date->lessThan($until); $date = $date->addDay()) {
            if ($date->dayOfWeekIso === (int) $candidate['weekday'] && $date->dayOfWeekIso === (int) $existing->weekday
                && $this->occurs($candidate['recurrence_type'], $date, $candidate['week_numbers'] ?? [])
                && $this->occurs($existing->recurrence_type, $date, $existingWeekNumbers)) {
                return $date;
            }
        }

        return null;
    }

    private function occurs(string $type, CarbonImmutable $date, array $weekNumbers): bool
    {
        return match ($type) {
            'EVERY_WEEK' => true,
            'WEEK_OF_MONTH' => in_array((int) ceil($date->day / 7), array_map('intval', $weekNumbers), true),
            'ODD_WEEK' => $date->isoWeek % 2 === 1,
            'EVEN_WEEK' => $date->isoWeek % 2 === 0,
            default => false,
        };
    }

    private function timesOverlap(string $candidateStart, string $candidateEnd, string $existingStart, string $existingEnd): bool
    {
        return $candidateStart < $existingEnd && $candidateEnd > $existingStart;
    }
}
