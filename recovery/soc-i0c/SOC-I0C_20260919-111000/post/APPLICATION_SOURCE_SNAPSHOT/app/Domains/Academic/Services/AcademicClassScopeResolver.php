<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\ScheduleRule;

class AcademicClassScopeResolver
{
    public function forScheduleRule(ScheduleRule $rule): array
    {
        $classIds = $rule->groups()->pluck('class_id')->unique()->values()->all();

        return $classIds !== [] ? $classIds : [$rule->teachingAssignment->class_id];
    }

    public function forScheduleCandidate(array $candidate): array
    {
        $classIds = $candidate['effective_class_ids'] ?? $candidate['class_ids'] ?? [];

        return $classIds !== [] ? array_values(array_unique($classIds)) : [$candidate['class_id']];
    }

    public function forSession(ClassSession $session): array
    {
        $classIds = $session->scopeGroups->pluck('class_id')->unique()->values()->all();

        return $classIds !== [] ? $classIds : [$session->class_id];
    }

    public function constrainSessionQueryToClassScope($query, array $classIds): void
    {
        $query->where(function ($query) use ($classIds): void {
            $query->whereHas('scopeGroups', fn ($scope) => $scope->whereIn('class_id', $classIds))
                ->orWhere(function ($fallback) use ($classIds): void {
                    $fallback->whereDoesntHave('scopeGroups')->whereIn('class_id', $classIds);
                });
        });
    }
}
