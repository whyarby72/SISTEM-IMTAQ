<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\AttendancePeriodLock;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\SemesterSubjectGrade;
use App\Domains\Academic\Models\StudentClassEnrollment;
use App\Shared\Core\Models\Student;

class ReportCardReadinessService
{
    public function check(Student $student, Semester $semester, AcademicClass $class): array
    {
        $reasons = [];
        $missingGradeSubjectIds = [];
        $unlockedAttendancePeriods = [];

        $enrolled = StudentClassEnrollment::query()
            ->where('student_id', $student->id)->where('class_id', $class->id)->where('status', 'ACTIVE')
            ->whereDate('effective_from', '<=', $semester->ends_on->toDateString())
            ->where(fn ($query) => $query->whereNull('effective_until')->orWhereDate('effective_until', '>', $semester->starts_on->toDateString()))
            ->exists();
        if (! $enrolled) {
            $reasons[] = 'STUDENT_NOT_ENROLLED_FOR_SEMESTER';
        }

        $subjectIds = $class->teachingAssignments()
            ->where('semester_id', $semester->id)->where('workflow_status', 'ACTIVE')
            ->pluck('subject_id')->unique()->values();
        if ($subjectIds->isEmpty() && $enrolled) {
            $reasons[] = 'NO_EXPECTED_SUBJECTS';
        }

        $lockedGrades = SemesterSubjectGrade::query()
            ->where('student_id', $student->id)->where('semester_id', $semester->id)
            ->whereIn('subject_id', $subjectIds)->where('workflow_status', 'LOCKED')->whereNotNull('score')
            ->pluck('subject_id');
        $missingGradeSubjectIds = $subjectIds->diff($lockedGrades)->values()->all();
        if ($missingGradeSubjectIds !== []) {
            $reasons[] = 'MISSING_OR_UNLOCKED_SEMESTER_GRADE';
        }

        $cursor = $semester->starts_on->copy()->startOfMonth();
        $lastMonth = $semester->ends_on->copy()->startOfMonth();
        while ($cursor->lessThanOrEqualTo($lastMonth)) {
            $periodStart = $cursor->toDateString();
            $periodEnd = $cursor->copy()->endOfMonth()->toDateString();
            $locked = AttendancePeriodLock::query()
                ->where('class_id', $class->id)->whereDate('period_start', $periodStart)
                ->whereDate('period_end', $periodEnd)->where('status', 'LOCKED')->exists();
            if (! $locked) {
                $unlockedAttendancePeriods[] = $periodStart;
            }
            $cursor->addMonth();
        }
        if ($unlockedAttendancePeriods !== []) {
            $reasons[] = 'UNLOCKED_ATTENDANCE_PERIOD';
        }

        return [
            'student_id' => (string) $student->id,
            'semester_id' => (string) $semester->id,
            'class_id' => (string) $class->id,
            'is_ready' => $reasons === [],
            'reasons' => $reasons,
            'expected_subject_ids' => $subjectIds->all(),
            'missing_grade_subject_ids' => $missingGradeSubjectIds,
            'unlocked_attendance_periods' => $unlockedAttendancePeriods,
        ];
    }
}
