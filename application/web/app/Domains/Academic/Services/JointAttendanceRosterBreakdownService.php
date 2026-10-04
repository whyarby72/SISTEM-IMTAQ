<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\ClassSession;
use App\Shared\Platform\Presentation\AcademicBusinessTime;
use Illuminate\Support\Collection;

class JointAttendanceRosterBreakdownService
{
    public function __construct(private readonly AcademicClassScopeResolver $classScope) {}

    public function for(ClassSession $session, Collection $participants): array
    {
        $classIds = $this->classScope->forSession($session);
        $classes = $session->scopeGroups()
            ->with('academicClass')
            ->get()
            ->sortBy(fn ($group) => $group->academicClass?->display_name ?? '')
            ->pluck('academicClass')
            ->filter()
            ->unique('id')
            ->values();

        if ($classes->isEmpty()) {
            $classes = collect([$session->academicClass])->filter();
        }

        $labels = [];
        $counts = $classes->mapWithKeys(fn ($class) => [(string) $class->id => 0])->all();
        $unmapped = 0;
        foreach ($participants as $participant) {
            $matches = $this->effectiveMatches($session, $participant, $classIds);

            if ($matches->count() !== 1) {
                $labels[(string) $participant->id] = null;
                $unmapped++;

                continue;
            }

            $classId = (string) $matches->first()->class_id;
            $labels[(string) $participant->id] = $classes->firstWhere('id', $classId)?->display_name;
            $counts[$classId] = ($counts[$classId] ?? 0) + 1;
        }

        return [
            'is_joint' => count($classIds) > 1,
            'classes' => $classes,
            'class_label' => $classes->pluck('display_name')->implode(' + '),
            'counts' => $classes->map(fn ($class) => ['label' => $class->display_name, 'count' => $counts[(string) $class->id] ?? 0]),
            'participant_labels' => $labels,
            'unmapped_count' => $unmapped,
            'total' => $participants->count(),
        ];
    }

    public function forClass(ClassSession $session, Collection $participants, string $classId): Collection
    {
        $classIds = $this->classScope->forSession($session);

        return $participants->filter(function ($participant) use ($session, $classIds, $classId): bool {
            $matches = $this->effectiveMatches($session, $participant, $classIds);

            return $matches->count() === 1 && (string) $matches->first()->class_id === $classId;
        })->values();
    }

    private function effectiveMatches(ClassSession $session, object $participant, array $classIds): Collection
    {
        $sessionDate = AcademicBusinessTime::date($session->planned_start_at);

        return $participant->student->classEnrollments
            ->filter(fn ($enrollment) => in_array((string) $enrollment->class_id, array_map('strval', $classIds), true)
                && $enrollment->status === 'ACTIVE'
                && $enrollment->effective_from->lte($sessionDate)
                && ($enrollment->effective_until === null || $enrollment->effective_until->gt($sessionDate)));
    }
}
