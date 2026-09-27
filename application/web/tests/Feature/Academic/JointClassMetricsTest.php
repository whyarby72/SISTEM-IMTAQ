<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\ClassSessionGroup;
use App\Domains\Academic\Models\GradeLevel;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\SessionStudentParticipant;
use App\Domains\Academic\Models\StudentClassEnrollment;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Models\TeachingAssignment;
use App\Domains\Academic\Services\AttendanceSemanticMetricsService;
use App\Domains\Academic\Services\SessionSemanticMetricsService;
use App\Models\User;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use App\Shared\Core\Models\Staff;
use App\Shared\Core\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class JointClassMetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_joint_session_is_counted_for_each_scoped_class_without_cross_counting_students(): void
    {
        [$first, $second, $session, $actor] = $this->fixture();
        $firstStudent = Student::create(['student_code' => 'JOINT-1', 'full_name' => 'Santri 1']);
        $secondStudent = Student::create(['student_code' => 'JOINT-2', 'full_name' => 'Santri 2']);
        StudentClassEnrollment::create(['student_id' => $firstStudent->id, 'class_id' => $first->id, 'effective_from' => '2026-07-01']);
        StudentClassEnrollment::create(['student_id' => $secondStudent->id, 'class_id' => $second->id, 'effective_from' => '2026-07-01']);
        SessionStudentParticipant::create(['class_session_id' => $session->id, 'student_id' => $firstStudent->id, 'participant_basis' => 'CLASS_ENROLLMENT']);
        SessionStudentParticipant::create(['class_session_id' => $session->id, 'student_id' => $secondStudent->id, 'participant_basis' => 'CLASS_ENROLLMENT']);

        $attendance = app(AttendanceSemanticMetricsService::class);
        $sessionMetrics = app(SessionSemanticMetricsService::class);
        $from = Carbon::parse('2026-07-01')->startOfDay();
        $to = Carbon::parse('2026-07-31')->endOfDay();

        $this->assertSame(1, $attendance->forClassPeriod($first, $from, $to)['eligible_opportunities']);
        $this->assertSame(1, $attendance->forClassPeriod($second, $from, $to)['eligible_opportunities']);
        $this->assertSame(1, $sessionMetrics->forClassPeriod($second, $from, $to)['counted_sessions']);
    }

    private function fixture(): array
    {
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-JOINT', 'unit_name' => 'Joint Unit', 'unit_type' => 'SCHOOL']);
        $grade = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '2', 'display_name' => 'Tingkat 2', 'sequence_no' => 2]);
        $year = AcademicYear::create(['year_code' => '2026-JOINT', 'display_name' => '2026/2027', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $semester = Semester::create(['academic_year_id' => $year->id, 'semester_code' => 'ODD', 'display_name' => 'Ganjil', 'sequence_no' => 1, 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-31']);
        $first = AcademicClass::create(['class_code' => 'JOINT-A', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id, 'section_code' => 'A', 'display_name' => 'Kelas A']);
        $second = AcademicClass::create(['class_code' => 'JOINT-B', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id, 'section_code' => 'B', 'display_name' => 'Kelas B']);
        $subject = Subject::create(['subject_code' => 'SUBJ-JOINT', 'subject_name' => 'Joint Subject']);
        $staff = Staff::create(['staff_code' => 'STAFF-JOINT', 'full_name' => 'Joint Teacher']);
        $assignment = TeachingAssignment::create(['assignment_code' => 'TA-JOINT', 'semester_id' => $semester->id, 'class_id' => $first->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $staff->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);
        $session = ClassSession::create(['session_code' => 'SESSION-JOINT', 'teaching_assignment_id' => $assignment->id, 'class_id' => $first->id, 'subject_id' => $subject->id, 'planned_start_at' => '2026-07-10 08:00:00', 'planned_end_at' => '2026-07-10 09:00:00', 'session_source' => 'SCHEDULED', 'participant_scope' => 'FULL_CLASS', 'session_status' => 'COMPLETED']);
        ClassSessionGroup::create(['class_session_id' => $session->id, 'class_id' => $first->id, 'scope_role' => 'JOINT_SCOPE']);
        ClassSessionGroup::create(['class_session_id' => $session->id, 'class_id' => $second->id, 'scope_role' => 'JOINT_SCOPE']);

        return [$first, $second, $session, User::factory()->create()];
    }
}
