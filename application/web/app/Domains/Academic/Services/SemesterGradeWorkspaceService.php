<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\SemesterSubjectGrade;
use App\Domains\Academic\Models\StudentClassEnrollment;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Support\Collection;

class SemesterGradeWorkspaceService
{
    public function __construct(private readonly SemesterGradeAuthorizationService $authorization) {}

    public function forSelection(User $actor, Semester $semester, AcademicClass $class, Subject $subject): array
    {
        $this->authorization->requireViewGradeWorkspace($actor, $semester, $class, $subject);

        $assignments = TeachingAssignment::query()->with('teacher')->where('semester_id', $semester->id)->where('class_id', $class->id)->where('subject_id', $subject->id)->where('workflow_status', 'ACTIVE')->whereDate('effective_from', '<=', $semester->ends_on->toDateString())->where(fn ($query) => $query->whereNull('effective_until')->orWhereDate('effective_until', '>', $semester->starts_on->toDateString()))->get();
        $students = StudentClassEnrollment::query()->with('student')->where('class_id', $class->id)->where('status', 'ACTIVE')->whereDate('effective_from', '<=', $semester->ends_on->toDateString())->where(fn ($query) => $query->whereNull('effective_until')->orWhereDate('effective_until', '>', $semester->starts_on->toDateString()))->get()->unique('student_id')->sortBy(fn ($enrollment) => $enrollment->student->student_code)->values();
        $grades = SemesterSubjectGrade::query()->where('semester_id', $semester->id)->where('subject_id', $subject->id)->whereIn('student_id', $students->pluck('student_id'))->get()->keyBy('student_id');

        return [
            'semester' => $semester,
            'class' => $class,
            'subject' => $subject,
            'assignments' => $assignments,
            'scope_type' => $this->authorization->scopeType($actor, $semester, $class, $subject),
            'students' => $students->map(function ($enrollment) use ($grades): array {
                $grade = $grades->get($enrollment->student_id);

                return [
                    'student_id' => (string) $enrollment->student_id,
                    'student_code' => $enrollment->student->student_code,
                    'student_name' => $enrollment->student->full_name,
                    'score' => $grade?->score,
                    'is_missing' => $grade === null || $grade->score === null,
                    'workflow_status' => $grade?->workflow_status,
                    'version_no' => $grade?->version_no,
                    'grade_source' => $grade?->grade_source,
                    'responsible_staff_id' => $grade?->responsible_staff_id,
                    'grade_id' => $grade?->id,
                ];
            })->all(),
        ];
    }

    public function emptyState(User $actor, ?Semester $semester = null, ?AcademicClass $class = null): array
    {
        return [
            'scope_type' => $semester ? $this->authorization->scopeType($actor, $semester, $class) : null,
            'students' => [],
            'assignments' => collect(),
        ];
    }
}
