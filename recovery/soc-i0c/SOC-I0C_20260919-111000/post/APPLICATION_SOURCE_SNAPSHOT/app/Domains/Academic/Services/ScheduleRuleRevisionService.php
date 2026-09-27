<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\ScheduleChange;
use App\Domains\Academic\Models\ScheduleRule;
use App\Domains\Academic\Models\TeachingAssignment;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class ScheduleRuleRevisionService
{
    public function __construct(private readonly AcademicClassScopeResolver $classScope) {}

    public function revise(ScheduleRule $rule, array $payload, ?int $actorUserId, string $reason, ClassSessionGenerator $generator): ScheduleRule
    {
        return DB::transaction(function () use ($rule, $payload, $actorUserId, $reason, $generator): ScheduleRule {
            $current = ScheduleRule::query()->with('weekNumbers')->whereKey($rule->id)->lockForUpdate()->firstOrFail();
            $sourceGroups = $current->groups()->get(['class_id', 'scope_role']);
            $revisionFrom = CarbonImmutable::parse($payload['revision_from'])->startOfDay();
            $originalUntil = $current->effective_until;
            $originalStatus = $current->workflow_status;

            if ($revisionFrom->toDateString() === $current->effective_from->toDateString()) {
                $current->update(['workflow_status' => 'ARCHIVED', 'version_no' => $current->version_no + 1]);
            } else {
                $current->update(['effective_until' => $revisionFrom->subDay()->toDateString(), 'version_no' => $current->version_no + 1]);
            }

            $sessions = ClassSession::query()
                ->where('schedule_rule_id', $current->id)
                ->where('planned_start_at', '>=', $revisionFrom)
                ->where('session_status', 'PLANNED')
                ->whereDoesntHave('studentParticipants.attendance')
                ->lockForUpdate()->get();
            foreach ($sessions as $session) {
                $session->update(['session_status' => 'CANCELLED']);
                ScheduleChange::create([
                    'change_code' => 'REVISION-CANCEL-'.$session->id.'-'.str()->uuid(),
                    'change_type' => 'SCHEDULE_REVISION',
                    'source_session_id' => $session->id,
                    'reason' => $reason,
                    'requested_by_user_id' => $actorUserId,
                    'requested_at' => now(),
                    'approved_by_user_id' => $actorUserId,
                    'approved_at' => now(),
                    'applied_by_user_id' => $actorUserId,
                    'applied_at' => now(),
                    'status' => 'APPLIED',
                ]);
            }

            $assignment = $current->teachingAssignment;
            $newAssignment = $assignment;
            if ((string) $payload['teacher_staff_id'] !== (string) $assignment->teacher_staff_id
                || (string) $payload['subject_id'] !== (string) $assignment->subject_id) {
                $newAssignment = TeachingAssignment::create([
                    'assignment_code' => 'REV-'.$assignment->assignment_code.'-'.str()->upper(str()->random(8)),
                    'semester_id' => $assignment->semester_id,
                    'class_id' => $assignment->class_id,
                    'subject_id' => $payload['subject_id'],
                    'teacher_staff_id' => $payload['teacher_staff_id'],
                    'effective_from' => $revisionFrom->toDateString(),
                    'effective_until' => $originalUntil?->toDateString(),
                    'workflow_status' => $assignment->workflow_status,
                    'source_reference' => 'SCHEDULE-REVISION:'.$assignment->id,
                    'version_no' => $assignment->version_no + 1,
                ]);
            }

            $newRule = ScheduleRule::create([
                'teaching_assignment_id' => $newAssignment->id,
                'weekday' => $payload['weekday'],
                'start_time' => $payload['start_time'],
                'end_time' => $payload['end_time'],
                'recurrence_type' => $payload['recurrence_type'],
                'location_id' => $current->location_id,
                'effective_from' => $revisionFrom->toDateString(),
                'effective_until' => $payload['effective_until'] ?? $originalUntil?->toDateString(),
                'workflow_status' => $originalStatus,
                'version_no' => $current->version_no,
            ]);
            $newRule->weekNumbers()->createMany(array_map(
                static fn (int $week): array => ['week_no' => $week],
                array_values(array_unique(array_map('intval', $payload['week_numbers'] ?? [])))
            ));
            $newRule->groups()->createMany($sourceGroups->map(
                static fn ($sourceGroup): array => [
                    'class_id' => $sourceGroup->class_id,
                    'scope_role' => $sourceGroup->scope_role,
                ]
            )->all());

            $until = CarbonImmutable::parse($newRule->effective_until);
            for ($date = $revisionFrom; $date->lessThanOrEqualTo($until); $date = $date->addDay()) {
                $start = $date->setTimeFromTimeString($newRule->start_time);
                $end = $date->setTimeFromTimeString($newRule->end_time);
                $occupiedQuery = ClassSession::query()
                    ->whereIn('session_status', ['PLANNED', 'CONFIRMED', 'COMPLETED']);
                $this->classScope->constrainSessionQueryToClassScope($occupiedQuery, $this->classScope->forScheduleRule($newRule));
                $occupied = $occupiedQuery
                    ->where('planned_start_at', '<', $end)
                    ->where('planned_end_at', '>', $start)->exists();
                if ($occupied) {
                    continue;
                }
                $generator->generate($newRule, $date, $date);
            }

            return $newRule;
        });
    }
}
