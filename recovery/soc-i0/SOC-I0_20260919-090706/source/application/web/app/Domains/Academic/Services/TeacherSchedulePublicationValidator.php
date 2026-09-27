<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\ScheduleRule;
use App\Domains\Academic\Models\Semester;
use Illuminate\Support\Collection;

class TeacherSchedulePublicationValidator
{
    public function validate(Semester $semester): Collection
    {
        return ScheduleRule::query()
            ->with(['teachingAssignment.teacher', 'teachingAssignment.subject', 'teachingAssignment.academicClass', 'groups.academicClass', 'weekNumbers'])
            ->whereHas('teachingAssignment', fn ($query) => $query->where('semester_id', $semester->id))
            ->get()
            ->flatMap(function (ScheduleRule $rule): array {
                $assignment = $rule->teachingAssignment;
                $reasons = [];

                if ($assignment->teacher === null || $assignment->teacher->record_status !== 'ACTIVE') {
                    $reasons[] = 'TEACHER_NOT_FOUND_OR_INACTIVE';
                }
                if ($assignment->subject === null || $assignment->subject->status !== 'ACTIVE') {
                    $reasons[] = 'SUBJECT_NOT_FOUND_OR_INACTIVE';
                }
                if ($rule->groups->isEmpty()) {
                    $reasons[] = 'TEACHING_GROUP_SCOPE_REQUIRED';
                }
                if ($rule->groups->contains(fn ($group): bool => $group->academicClass === null || $group->academicClass->status !== 'ACTIVE')) {
                    $reasons[] = 'CLASS_SCOPE_NOT_FOUND_OR_INACTIVE';
                }
                if ($rule->recurrence_type === 'WEEK_OF_MONTH' && $rule->weekNumbers->isEmpty()) {
                    $reasons[] = 'WEEK_OF_MONTH_NUMBERS_REQUIRED';
                }

                return $reasons === [] ? [] : [[
                    'schedule_rule_id' => $rule->id,
                    'schedule_rule_key' => $assignment->assignment_code,
                    'reasons' => $reasons,
                ]];
            });
    }
}
