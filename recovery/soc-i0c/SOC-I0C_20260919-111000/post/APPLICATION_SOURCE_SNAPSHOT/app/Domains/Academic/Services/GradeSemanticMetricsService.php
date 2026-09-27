<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\SemesterSubjectGrade;
use App\Domains\Academic\Models\StudentClassEnrollment;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Models\TeachingAssignment;
use App\Shared\Core\Models\Student;
use Illuminate\Support\Collection;

class GradeSemanticMetricsService
{
    public function forSemester(Semester $semester, ?Subject $subject = null, ?AcademicClass $class = null): Collection
    {
        $assignments = TeachingAssignment::query()
            ->with('subject')
            ->where('semester_id', $semester->id)
            ->where('workflow_status', 'ACTIVE')
            ->when($subject !== null, fn ($query) => $query->where('subject_id', $subject->id))
            ->when($class !== null, fn ($query) => $query->where('class_id', $class->id))
            ->get();

        return $assignments->groupBy('subject_id')->map(function (Collection $subjectAssignments) use ($semester): array {
            $subject = $subjectAssignments->first()->subject;
            $expectedStudentIds = StudentClassEnrollment::query()
                ->whereIn('class_id', $subjectAssignments->pluck('class_id')->unique())
                ->where('status', 'ACTIVE')
                ->whereDate('effective_from', '<=', $semester->ends_on->toDateString())
                ->where(fn ($query) => $query->whereNull('effective_until')->orWhereDate('effective_until', '>', $semester->starts_on->toDateString()))
                ->pluck('student_id')->unique()->values();
            $grades = SemesterSubjectGrade::query()
                ->where('semester_id', $semester->id)
                ->where('subject_id', $subject->id)
                ->whereIn('student_id', $expectedStudentIds)
                ->where('workflow_status', 'LOCKED')
                ->whereNotNull('score')
                ->get();
            $missing = $expectedStudentIds->diff($grades->pluck('student_id'))->values();

            return [
                'subject' => $subject,
                'expected_count' => $expectedStudentIds->count(),
                'locked_count' => $grades->count(),
                'missing_count' => $missing->count(),
                'missing_student_ids' => $missing->all(),
                'mean_score' => $grades->isEmpty() ? null : round((float) $grades->avg('score'), 2),
                'is_complete' => $expectedStudentIds->isNotEmpty() && $missing->isEmpty(),
                'is_official' => $expectedStudentIds->isNotEmpty() && $missing->isEmpty(),
            ];
        })->values();
    }

    public function trendForStudent(Student $student): Collection
    {
        return SemesterSubjectGrade::query()
            ->with('semester')
            ->where('student_id', $student->id)
            ->where('workflow_status', 'LOCKED')
            ->whereNotNull('score')
            ->get()
            ->groupBy('semester_id')
            ->map(fn (Collection $grades): array => [
                'semester' => $grades->first()->semester,
                'grade_count' => $grades->count(),
                'mean_score' => round((float) $grades->avg('score'), 2),
            ])
            ->sortBy(fn (array $row) => $row['semester']->starts_on)
            ->values();
    }
}
