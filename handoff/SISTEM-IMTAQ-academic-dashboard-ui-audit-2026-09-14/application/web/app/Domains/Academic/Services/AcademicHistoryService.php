<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\SemesterSubjectGrade;
use App\Shared\Core\Models\Student;
use Illuminate\Support\Collection;

class AcademicHistoryService
{
    public function forStudent(Student $student): Collection
    {
        return SemesterSubjectGrade::query()
            ->with(['semester', 'subject', 'sourceTeachingAssignment'])
            ->where('student_id', $student->id)
            ->where('workflow_status', 'LOCKED')
            ->whereNotNull('score')
            ->get()
            ->sort(function (SemesterSubjectGrade $left, SemesterSubjectGrade $right): int {
                $semesterOrder = $right->semester->starts_on->getTimestamp() <=> $left->semester->starts_on->getTimestamp();

                return $semesterOrder !== 0
                    ? $semesterOrder
                    : strcasecmp($left->subject->subject_name, $right->subject->subject_name);
            })
            ->values()
            ->map(fn (SemesterSubjectGrade $grade): array => [
                'grade_id' => (string) $grade->id,
                'semester_id' => (string) $grade->semester_id,
                'semester_name' => $grade->semester->display_name,
                'semester_starts_on' => $grade->semester->starts_on->toDateString(),
                'subject_id' => (string) $grade->subject_id,
                'subject_name' => $grade->subject->subject_name,
                'score' => $grade->score,
                'grade_source' => $grade->grade_source,
                'grade_version_no' => $grade->version_no,
                'source_teaching_assignment_id' => $grade->source_teaching_assignment_id,
            ]);
    }
}
