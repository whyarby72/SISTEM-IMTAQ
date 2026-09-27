<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\SemesterSubjectGrade;
use App\Domains\Academic\Models\StudentClassEnrollment;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Models\TeachingAssignment;
use Illuminate\Support\Collection;

class SemesterGradeCompletenessService
{
    public function check(Semester $semester, ?Subject $subject = null): Collection
    {
        $assignments = TeachingAssignment::query()
            ->with('subject')
            ->where('semester_id', $semester->id)
            ->where('workflow_status', 'ACTIVE')
            ->when($subject !== null, fn ($query) => $query->where('subject_id', $subject->id))
            ->get();

        return $assignments
            ->groupBy('subject_id')
            ->map(function (Collection $subjectAssignments) use ($semester): array {
                $subject = $subjectAssignments->first()->subject;
                $classIds = $subjectAssignments->pluck('class_id')->unique()->values();
                $expectedStudentIds = StudentClassEnrollment::query()
                    ->whereIn('class_id', $classIds)
                    ->where('status', 'ACTIVE')
                    ->whereDate('effective_from', '<=', $semester->ends_on->toDateString())
                    ->where(fn ($query) => $query->whereNull('effective_until')->orWhereDate('effective_until', '>', $semester->starts_on->toDateString()))
                    ->pluck('student_id')
                    ->unique()
                    ->values();
                $availableStudentIds = SemesterSubjectGrade::query()
                    ->where('semester_id', $semester->id)
                    ->where('subject_id', $subject->id)
                    ->whereNotNull('score')
                    ->pluck('student_id')
                    ->unique()
                    ->values();
                $missingStudentIds = $expectedStudentIds->diff($availableStudentIds)->values();

                return [
                    'subject' => $subject,
                    'expected_student_ids' => $expectedStudentIds->all(),
                    'available_student_ids' => $availableStudentIds->all(),
                    'missing_student_ids' => $missingStudentIds->all(),
                    'expected_count' => $expectedStudentIds->count(),
                    'available_count' => $availableStudentIds->count(),
                    'missing_count' => $missingStudentIds->count(),
                    'is_complete' => $missingStudentIds->isEmpty(),
                ];
            })
            ->values();
    }
}
