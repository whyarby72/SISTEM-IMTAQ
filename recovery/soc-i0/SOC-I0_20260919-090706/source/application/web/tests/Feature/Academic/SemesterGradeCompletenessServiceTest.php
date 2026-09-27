<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\GradeLevel;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\SemesterSubjectGrade;
use App\Domains\Academic\Models\StudentClassEnrollment;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Models\TeachingAssignment;
use App\Domains\Academic\Services\SemesterGradeCompletenessService;
use App\Models\User;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use App\Shared\Core\Models\Staff;
use App\Shared\Core\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SemesterGradeCompletenessServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_reports_expected_available_and_missing_students_without_mutating_grades(): void
    {
        [$semester, $subject, $first, $second, $actor] = $this->fixtures();
        $before = SemesterSubjectGrade::count();
        SemesterSubjectGrade::create([
            'student_id' => $first->id,
            'semester_id' => $semester->id,
            'subject_id' => $subject->id,
            'score' => 82,
            'entered_by' => $actor->id,
            'entered_at' => now(),
            'updated_by' => $actor->id,
            'updated_at' => now(),
        ]);

        $result = app(SemesterGradeCompletenessService::class)->check($semester, $subject)->first();

        $this->assertSame(2, $result['expected_count']);
        $this->assertSame(1, $result['available_count']);
        $this->assertSame(1, $result['missing_count']);
        $this->assertContains($second->id, $result['missing_student_ids']);
        $this->assertFalse($result['is_complete']);
        $this->assertSame($before + 1, SemesterSubjectGrade::count());
    }

    public function test_student_transfer_does_not_duplicate_expected_grade(): void
    {
        [$semester, $subject, $first] = $this->fixtures();

        $result = app(SemesterGradeCompletenessService::class)->check($semester, $subject)->first();

        $this->assertSame(2, $result['expected_count']);
        $this->assertCount(1, array_keys($result['expected_student_ids'], $first->id, true));
    }

    private function fixtures(): array
    {
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-GCP', 'unit_name' => 'Grade Completeness Unit', 'unit_type' => 'SCHOOL']);
        $gradeLevel = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '1', 'display_name' => 'Tingkat 1', 'sequence_no' => 1]);
        $year = AcademicYear::create(['year_code' => '2026-GCP', 'display_name' => '2026/2027', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $semester = Semester::create(['academic_year_id' => $year->id, 'semester_code' => 'ODD', 'display_name' => 'Ganjil', 'sequence_no' => 1, 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-31']);
        $classA = AcademicClass::create(['class_code' => 'CLASS-GCP-A', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $gradeLevel->id, 'section_code' => 'A', 'display_name' => 'Kelas GCP A']);
        $classB = AcademicClass::create(['class_code' => 'CLASS-GCP-B', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $gradeLevel->id, 'section_code' => 'B', 'display_name' => 'Kelas GCP B']);
        $subject = Subject::create(['subject_code' => 'SUBJ-GCP', 'subject_name' => 'Completeness Subject']);
        $teacher = Staff::create(['staff_code' => 'STAFF-GCP', 'full_name' => 'Completeness Teacher']);
        $first = Student::create(['student_code' => 'STU-GCP-1', 'full_name' => 'Transferred Student']);
        $second = Student::create(['student_code' => 'STU-GCP-2', 'full_name' => 'Continuing Student']);
        $actor = User::factory()->create();
        StudentClassEnrollment::create(['student_id' => $first->id, 'class_id' => $classA->id, 'effective_from' => '2026-07-01', 'effective_until' => '2026-09-01']);
        StudentClassEnrollment::create(['student_id' => $first->id, 'class_id' => $classB->id, 'effective_from' => '2026-09-01']);
        StudentClassEnrollment::create(['student_id' => $second->id, 'class_id' => $classA->id, 'effective_from' => '2026-07-01']);
        TeachingAssignment::create(['assignment_code' => 'TA-GCP-A', 'semester_id' => $semester->id, 'class_id' => $classA->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $teacher->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);
        TeachingAssignment::create(['assignment_code' => 'TA-GCP-B', 'semester_id' => $semester->id, 'class_id' => $classB->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $teacher->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);

        return [$semester, $subject, $first, $second, $actor];
    }
}
