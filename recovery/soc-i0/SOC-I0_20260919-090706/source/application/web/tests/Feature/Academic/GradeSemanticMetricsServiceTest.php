<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\GradeLevel;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\SemesterSubjectGrade;
use App\Domains\Academic\Models\StudentClassEnrollment;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Models\TeachingAssignment;
use App\Domains\Academic\Services\GradeSemanticMetricsService;
use App\Models\User;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use App\Shared\Core\Models\Staff;
use App\Shared\Core\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GradeSemanticMetricsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_completeness_and_mean_use_locked_transaction_grain_only(): void
    {
        [$semester, $subject, $class, $studentOne, $studentTwo, $actor] = $this->fixture();
        $this->grade($studentOne, $semester, $subject, $actor, 80, 'LOCKED');
        $this->grade($studentTwo, $semester, $subject, $actor, 95, 'DRAFT');

        $metric = app(GradeSemanticMetricsService::class)->forSemester($semester)->first();

        $this->assertSame(2, $metric['expected_count']);
        $this->assertSame(1, $metric['locked_count']);
        $this->assertSame(1, $metric['missing_count']);
        $this->assertSame(80.0, $metric['mean_score']);
        $this->assertFalse($metric['is_official']);
    }

    public function test_student_trend_aggregates_locked_grades_per_semester(): void
    {
        [$semester, $subject, , $studentOne, , $actor, $secondSemester] = $this->fixture();
        $this->grade($studentOne, $semester, $subject, $actor, 80, 'LOCKED');
        $this->grade($studentOne, $secondSemester, $subject, $actor, 90, 'LOCKED');

        $trend = app(GradeSemanticMetricsService::class)->trendForStudent($studentOne);

        $this->assertCount(2, $trend);
        $this->assertSame(80.0, $trend[0]['mean_score']);
        $this->assertSame(90.0, $trend[1]['mean_score']);
    }

    private function fixture(): array
    {
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-GRADE-METRIC', 'unit_name' => 'Grade Metric Unit', 'unit_type' => 'SCHOOL']);
        $level = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '1', 'display_name' => 'Tingkat 1', 'sequence_no' => 1]);
        $year = AcademicYear::create(['year_code' => '2026-GRADE-METRIC', 'display_name' => '2026/2027', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $semester = Semester::create(['academic_year_id' => $year->id, 'semester_code' => 'ODD', 'display_name' => 'Ganjil', 'sequence_no' => 1, 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-31']);
        $secondSemester = Semester::create(['academic_year_id' => $year->id, 'semester_code' => 'EVEN', 'display_name' => 'Genap', 'sequence_no' => 2, 'starts_on' => '2027-01-01', 'ends_on' => '2027-06-30']);
        $class = AcademicClass::create(['class_code' => 'CLASS-GRADE-METRIC', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $level->id, 'section_code' => 'A', 'display_name' => 'Kelas Grade Metric']);
        $subject = Subject::create(['subject_code' => 'SUBJ-GRADE-METRIC', 'subject_name' => 'Grade Metric']);
        $teacher = Staff::create(['staff_code' => 'STAFF-GRADE-METRIC', 'full_name' => 'Grade Metric Teacher']);
        $assignment = TeachingAssignment::create(['assignment_code' => 'TA-GRADE-METRIC', 'semester_id' => $semester->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $teacher->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);
        $secondAssignment = TeachingAssignment::create(['assignment_code' => 'TA-GRADE-METRIC-2', 'semester_id' => $secondSemester->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $teacher->id, 'effective_from' => '2027-01-01', 'workflow_status' => 'ACTIVE']);
        $studentOne = Student::create(['student_code' => 'GRADE-METRIC-1', 'full_name' => 'Grade One']);
        $studentTwo = Student::create(['student_code' => 'GRADE-METRIC-2', 'full_name' => 'Grade Two']);
        foreach ([$studentOne, $studentTwo] as $student) {
            StudentClassEnrollment::create(['student_id' => $student->id, 'class_id' => $class->id, 'effective_from' => '2026-07-01', 'status' => 'ACTIVE']);
        }
        $actor = User::factory()->create();

        return [$semester, $subject, $class, $studentOne, $studentTwo, $actor, $secondSemester, $assignment, $secondAssignment];
    }

    private function grade(Student $student, Semester $semester, Subject $subject, User $actor, int $score, string $status): SemesterSubjectGrade
    {
        return SemesterSubjectGrade::create(['student_id' => $student->id, 'semester_id' => $semester->id, 'subject_id' => $subject->id, 'score' => $score, 'workflow_status' => $status, 'entered_by' => $actor->id, 'entered_at' => now(), 'finalized_by' => $status === 'LOCKED' ? $actor->id : null, 'finalized_at' => $status === 'LOCKED' ? now() : null, 'updated_by' => $actor->id]);
    }
}
