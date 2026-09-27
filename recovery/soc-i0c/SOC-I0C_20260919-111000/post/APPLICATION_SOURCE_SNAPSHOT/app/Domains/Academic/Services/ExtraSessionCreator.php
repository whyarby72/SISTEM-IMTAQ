<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Exceptions\ScheduleConflictException;
use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\TeachingAssignment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ExtraSessionCreator
{
    public function __construct(private readonly TeacherObligationLockService $teacherObligationLock, private readonly AcademicClassScopeResolver $classScope) {}

    public function create(TeachingAssignment $assignment, Carbon|string $start, Carbon|string $end, string $source = 'EXTRA', string $participantScope = 'FULL_CLASS'): ClassSession
    {
        if (! in_array($participantScope, ['FULL_CLASS', 'SELECTED_STUDENTS'], true)) {
            throw new \InvalidArgumentException('Participant scope must be FULL_CLASS or SELECTED_STUDENTS.');
        }

        $startAt = Carbon::parse($start);
        $endAt = Carbon::parse($end);
        $conflict = ClassSession::query()
            ->with('teachingAssignment')
            ->whereIn('session_status', ['PLANNED', 'CONFIRMED', 'COMPLETED'])
            ->where('planned_start_at', '<', $endAt)
            ->where('planned_end_at', '>', $startAt)
            ->where(function ($query) use ($assignment): void {
                $this->classScope->constrainSessionQueryToClassScope($query, [$assignment->class_id]);
                $query->orWhereHas('teachingAssignment', fn ($q) => $q->where('teacher_staff_id', $assignment->teacher_staff_id));
            })
            ->first();

        if ($conflict !== null) {
            throw new ScheduleConflictException([
                'conflict_type' => $this->conflictType($assignment->class_id, $conflict),
                'conflicting_session_ref' => $conflict->id,
                'resource_id' => $this->conflictType($assignment->class_id, $conflict) === 'CLASS_CONFLICT' ? $assignment->class_id : $assignment->teacher_staff_id,
                'overlap_start_at' => max($startAt->toDateTimeString(), $conflict->planned_start_at->toDateTimeString()),
                'overlap_end_at' => min($endAt->toDateTimeString(), $conflict->planned_end_at->toDateTimeString()),
                'severity' => 'HIGH',
            ]);
        }

        return DB::transaction(function () use ($assignment, $startAt, $endAt, $source, $participantScope): ClassSession {
            $this->teacherObligationLock->lockTeachers([$assignment->teacher_staff_id]);
            $conflict = ClassSession::query()->with('teachingAssignment')
                ->whereIn('session_status', ['PLANNED', 'CONFIRMED', 'COMPLETED'])
                ->where('planned_start_at', '<', $endAt)->where('planned_end_at', '>', $startAt)
                ->where(function ($query) use ($assignment): void {
                    $this->classScope->constrainSessionQueryToClassScope($query, [$assignment->class_id]);
                    $query->orWhereHas('teachingAssignment', fn ($q) => $q->where('teacher_staff_id', $assignment->teacher_staff_id))
                        ->orWhereHas('teacherParticipations', fn ($q) => $q->where('teacher_staff_id', $assignment->teacher_staff_id)->where('role', 'SUBSTITUTE')->where('participation_status', 'EXPECTED'));
                })->first();
            if ($conflict !== null) {
                throw new ScheduleConflictException(['conflict_type' => $this->conflictType($assignment->class_id, $conflict), 'conflicting_session_ref' => $conflict->id, 'resource_id' => $this->conflictType($assignment->class_id, $conflict) === 'CLASS_CONFLICT' ? $assignment->class_id : $assignment->teacher_staff_id, 'severity' => 'HIGH']);
            }

            return ClassSession::create(['session_code' => $source.'-'.str()->uuid(), 'teaching_assignment_id' => $assignment->id, 'class_id' => $assignment->class_id, 'subject_id' => $assignment->subject_id, 'planned_start_at' => $startAt, 'planned_end_at' => $endAt, 'session_source' => $source, 'participant_scope' => $participantScope, 'session_status' => 'PLANNED']);
        });
    }

    private function conflictType(string $candidateClassId, ClassSession $conflict): string
    {
        return in_array($candidateClassId, $this->classScope->forSession($conflict), true) ? 'CLASS_CONFLICT' : 'TEACHER_CONFLICT';
    }
}
