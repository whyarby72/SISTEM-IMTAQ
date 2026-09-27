<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Exceptions\ScheduleConflictException;
use App\Domains\Academic\Models\AcademicCalendarEvent;
use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\ScheduleRule;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ClassSessionGenerator
{
    public function __construct(private readonly TeacherObligationLockService $teacherObligationLock) {}

    public function generate(ScheduleRule $rule, CarbonImmutable|string $from, CarbonImmutable|string $until): Collection
    {
        return DB::transaction(function () use ($rule, $from, $until): Collection {
            $lockedRule = ScheduleRule::query()->whereKey($rule->id)->lockForUpdate()->firstOrFail();
            $start = CarbonImmutable::parse($from)->startOfDay()->max($lockedRule->effective_from->startOfDay());
            $end = CarbonImmutable::parse($until)->startOfDay();
            if ($lockedRule->effective_until !== null) {
                $end = $end->min($lockedRule->effective_until->startOfDay());
            }
            $assignment = $lockedRule->teachingAssignment;
            $this->teacherObligationLock->lockTeachers([$assignment->teacher_staff_id]);
            $scopeClassIds = $lockedRule->groups()->pluck('class_id')->all();
            if ($scopeClassIds === []) {
                $scopeClassIds = [$assignment->class_id];
            }
            $created = collect();

            for ($date = $start; $date->lessThanOrEqualTo($end); $date = $date->addDay()) {
                if ($date->dayOfWeekIso !== (int) $lockedRule->weekday || ! $this->occurs($lockedRule, $date)) {
                    continue;
                }

                $plannedStart = $date->setTimeFromTimeString($lockedRule->start_time);
                $plannedEnd = $date->setTimeFromTimeString($lockedRule->end_time);
                if ($this->isBlocked($lockedRule, $plannedStart, $plannedEnd)) {
                    continue;
                }
                $this->assertTeacherFree($lockedRule, $plannedStart, $plannedEnd);
                $session = ClassSession::firstOrCreate(
                    ['schedule_rule_id' => $lockedRule->id, 'planned_start_at' => $plannedStart],
                    [
                        'session_code' => 'SESSION-'.$lockedRule->id.'-'.$plannedStart->format('YmdHis'),
                        'teaching_assignment_id' => $assignment->id,
                        'class_id' => $assignment->class_id,
                        'subject_id' => $assignment->subject_id,
                        'location_id' => $lockedRule->location_id,
                        'planned_end_at' => $plannedEnd,
                        'session_source' => 'SCHEDULED',
                        'participant_scope' => 'FULL_CLASS',
                        'session_status' => 'PLANNED',
                    ]
                );
                foreach ($scopeClassIds as $scopeClassId) {
                    $session->scopeGroups()->firstOrCreate([
                        'class_id' => $scopeClassId,
                    ], [
                        'scope_role' => count($scopeClassIds) > 1 ? 'JOINT_SCOPE' : 'TEACHING_SCOPE',
                    ]);
                }
                $created->push($session);
            }

            return $created;
        });
    }

    private function occurs(ScheduleRule $rule, CarbonImmutable $date): bool
    {
        return match ($rule->recurrence_type) {
            'EVERY_WEEK' => true,
            'ODD_WEEK' => $date->isoWeek % 2 === 1,
            'EVEN_WEEK' => $date->isoWeek % 2 === 0,
            'WEEK_OF_MONTH' => $rule->weekNumbers->contains('week_no', (int) ceil($date->day / 7)),
            default => false,
        };
    }

    private function isBlocked(ScheduleRule $rule, CarbonImmutable $plannedStart, CarbonImmutable $plannedEnd): bool
    {
        $assignment = $rule->teachingAssignment;

        return AcademicCalendarEvent::query()
            ->where('academic_year_id', $assignment->semester->academic_year_id)
            ->where('regular_session_policy', 'BLOCK')
            ->where('start_at', '<', $plannedEnd)
            ->where('end_at', '>', $plannedStart)
            ->where(function ($query) use ($assignment, $rule): void {
                $query->whereNull('class_id')->orWhereIn('class_id', $rule->groups()->pluck('class_id')->push($assignment->class_id)->unique()->all());
            })
            ->exists();
    }

    private function assertTeacherFree(ScheduleRule $rule, CarbonImmutable $plannedStart, CarbonImmutable $plannedEnd): void
    {
        $assignment = $rule->teachingAssignment;
        $conflict = ClassSession::query()
            ->where('planned_start_at', '<', $plannedEnd)
            ->where('planned_end_at', '>', $plannedStart)
            ->whereIn('session_status', ['PLANNED', 'CONFIRMED', 'COMPLETED'])
            ->where(function ($query) use ($assignment): void {
                $query->whereHas('teachingAssignment', fn ($q) => $q->where('teacher_staff_id', $assignment->teacher_staff_id))
                    ->orWhereHas('teacherParticipations', fn ($q) => $q->where('teacher_staff_id', $assignment->teacher_staff_id)->where('role', 'SUBSTITUTE')->where('participation_status', 'EXPECTED'));
            })
            ->where(function ($query) use ($rule, $plannedStart): void {
                $query->where('schedule_rule_id', '!=', $rule->id)
                    ->orWhere('planned_start_at', '!=', $plannedStart);
            })
            ->first();

        if ($conflict !== null) {
            throw new ScheduleConflictException([
                'conflict_type' => 'TEACHER_CONFLICT',
                'conflicting_session_ref' => $conflict->id,
                'resource_id' => $assignment->teacher_staff_id,
                'severity' => 'HIGH',
            ]);
        }
    }
}
