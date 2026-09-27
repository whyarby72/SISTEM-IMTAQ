<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\AttendancePeriodLock;
use App\Domains\Academic\Models\GradeLevel;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\SemesterSubjectGrade;
use App\Domains\Academic\Models\StudentClassEnrollment;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Models\TeachingAssignment;
use App\Domains\Academic\Services\ReportCardReadinessService;
use App\Models\User;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use App\Shared\Core\Models\Staff;
use App\Shared\Core\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportCardReadinessServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_ready_requires_locked_grade_and_all_semester_attendance_periods(): void
    {
        [$student, $semester, $class] = $this->fixtures(true, true);

        $result = app(ReportCardReadinessService::class)->check($student, $semester, $class);

        $this->assertTrue($result['is_ready']);
        $this->assertSame([], $result['reasons']);
        $this->assertSame([], $result['missing_grade_subject_ids']);
        $this->assertSame([], $result['unlocked_attendance_periods']);
    }

    public function test_missing_grade_blocks_readiness_without_inventing_zero(): void
    {
        [$student, $semester, $class] = $this->fixtures(false, true);

        $result = app(ReportCardReadinessService::class)->check($student, $semester, $class);

        $this->assertFalse($result['is_ready']);
        $this->assertContains('MISSING_OR_UNLOCKED_SEMESTER_GRADE', $result['reasons']);
        $this->assertCount(1, $result['missing_grade_subject_ids']);
        $this->assertSame(0, SemesterSubjectGrade::count());
    }

    public function test_unlocked_attendance_period_blocks_readiness(): void
    {
        [$student, $semester, $class] = $this->fixtures(true, false);

        $result = app(ReportCardReadinessService::class)->check($student, $semester, $class);

        $this->assertFalse($result['is_ready']);
        $this->assertContains('UNLOCKED_ATTENDANCE_PERIOD', $result['reasons']);
        $this->assertCount(6, $result['unlocked_attendance_periods']);
    }

    private function fixtures(bool $withGrade, bool $lockAttendance): array
    {
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-RDY', 'unit_name' => 'Readiness Unit', 'unit_type' => 'SCHOOL']);
        $gradeLevel = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '1', 'display_name' => 'Tingkat 1', 'sequence_no' => 1]);
        $year = AcademicYear::create(['year_code' => '2026-RDY', 'display_name' => '2026/2027', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $semester = Semester::create(['academic_year_id' => $year->id, 'semester_code' => 'ODD', 'display_name' => 'Ganjil', 'sequence_no' => 1, 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-31']);
        $class = AcademicClass::create(['class_code' => 'CLASS-RDY-A', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $gradeLevel->id, 'section_code' => 'A', 'display_name' => 'Kelas Readiness']);
        $subject = Subject::create(['subject_code' => 'SUBJ-RDY', 'subject_name' => 'Readiness Subject']);
        $teacher = Staff::create(['staff_code' => 'STAFF-RDY', 'full_name' => 'Readiness Teacher']);
        $student = Student::create(['student_code' => 'STU-RDY', 'full_name' => 'Readiness Student']);
        $actor = User::factory()->create();
        StudentClassEnrollment::create(['student_id' => $student->id, 'class_id' => $class->id, 'effective_from' => '2026-07-01']);
        TeachingAssignment::create(['assignment_code' => 'TA-RDY', 'semester_id' => $semester->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $teacher->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);
        if ($withGrade) {
            SemesterSubjectGrade::create(['student_id' => $student->id, 'semester_id' => $semester->id, 'subject_id' => $subject->id, 'score' => 88, 'workflow_status' => 'LOCKED', 'entered_by' => $actor->id, 'entered_at' => now(), 'finalized_by' => $actor->id, 'finalized_at' => now(), 'updated_by' => $actor->id, 'updated_at' => now()]);
        }
        if ($lockAttendance) {
            foreach (range(7, 12) as $month) {
                $start = sprintf('2026-%02d-01', $month);
                AttendancePeriodLock::create(['class_id' => $class->id, 'period_start' => $start, 'period_end' => date('Y-m-t', strtotime($start)), 'status' => 'LOCKED', 'locked_by' => $actor->id, 'locked_at' => now()]);
            }
        }

        return [$student, $semester, $class];
    }
}
