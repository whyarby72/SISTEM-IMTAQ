<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\GradeLevel;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\SessionStudentParticipant;
use App\Domains\Academic\Models\StudentAttendance;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Models\TeachingAssignment;
use App\Domains\Academic\Services\AttendanceSemanticMetricsService;
use App\Models\User;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use App\Shared\Core\Models\Staff;
use App\Shared\Core\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceSemanticMetricsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_metrics_use_transaction_grain_and_do_not_count_cancelled_or_missing_as_absent(): void
    {
        [$class, $actor] = $this->fixture();
        foreach (['PRESENT', 'LATE', 'ABSENT', null] as $index => $status) {
            $session = $this->makeSession($class, $index, 'COMPLETED');
            $participant = SessionStudentParticipant::create(['class_session_id' => $session->id, 'student_id' => Student::create(['student_code' => "METRIC-STU-{$index}", 'full_name' => "Metric {$index}"])->id, 'participant_basis' => 'CLASS_ENROLLMENT']);
            if ($status !== null) {
                StudentAttendance::create(['session_student_participant_id' => $participant->id, 'attendance_status' => $status, 'workflow_status' => 'VALIDATED', 'entered_by' => $actor->id, 'entered_at' => now(), 'finalized_by' => $actor->id, 'finalized_at' => now(), 'updated_by' => $actor->id, 'updated_at' => now()]);
            }
        }
        $cancelled = $this->makeSession($class, 4, 'CANCELLED');
        SessionStudentParticipant::create(['class_session_id' => $cancelled->id, 'student_id' => Student::create(['student_code' => 'METRIC-CANCEL', 'full_name' => 'Cancelled'])->id, 'participant_basis' => 'CLASS_ENROLLMENT']);

        $metrics = app(AttendanceSemanticMetricsService::class)->forClassPeriod($class, now()->startOfMonth(), now()->endOfMonth());

        $this->assertSame(4, $metrics['eligible_opportunities']);
        $this->assertSame(3, $metrics['resolved_opportunities']);
        $this->assertSame(1, $metrics['missing_opportunities']);
        $this->assertSame(50.0, $metrics['physical_presence_rate']);
        $this->assertSame(33.33, $metrics['unexcused_absence_rate']);
        $this->assertSame(1, $metrics['late_count']);
        $this->assertTrue($metrics['accounting_invariant']['is_balanced']);
        $this->assertSame(75.0, $metrics['completeness_rate']);
        $this->assertSame(1, $metrics['counts']['ABSENT']);
    }

    public function test_complete_required_opportunities_use_resolved_denominators(): void
    {
        [$class, $actor] = $this->fixture();
        foreach (['PRESENT', 'LATE', 'PRESENT', 'ABSENT'] as $index => $status) {
            $session = $this->makeSession($class, $index, 'COMPLETED');
            $participant = $this->participant($session, $index);
            $this->attendance($participant, $status, $actor);
        }

        $metrics = app(AttendanceSemanticMetricsService::class)->forClassPeriod($class, now()->startOfMonth(), now()->endOfMonth());

        $this->assertSame(4, $metrics['eligible_opportunities']);
        $this->assertSame(4, $metrics['resolved_opportunities']);
        $this->assertSame(0, $metrics['missing_opportunities']);
        $this->assertSame(75.0, $metrics['physical_presence_rate']);
        $this->assertSame(25.0, $metrics['unexcused_absence_rate']);
        $this->assertSame(100.0, $metrics['completeness_rate']);
    }

    public function test_reconciliation_required_and_missing_remain_visible_in_canonical_metrics(): void
    {
        [$class, $actor] = $this->fixture();
        $session = $this->makeSession($class, 0, 'COMPLETED');

        foreach (['PRESENT', 'LATE', 'IZIN', 'SICK', 'ABSENT', 'EXCUSED', null] as $index => $status) {
            $participant = $this->participant($session, $index);
            if ($status !== null) {
                $this->attendance($participant, $status, $actor);
            }
        }

        $metrics = app(AttendanceSemanticMetricsService::class)->forClassPeriod($class, now()->startOfMonth(), now()->endOfMonth());

        $this->assertSame(7, $metrics['eligible_opportunities']);
        $this->assertSame(5, $metrics['resolved_opportunities']);
        $this->assertSame(1, $metrics['missing_opportunities']);
        $this->assertSame(1, $metrics['reconciliation_required_count']);
        $this->assertSame(28.57, $metrics['attendance_rate']);
        $this->assertSame(71.43, $metrics['completeness_rate']);
        $this->assertTrue($metrics['accounting_invariant']['is_balanced']);
    }

    public function test_non_required_expected_participants_are_excluded_from_mandatory_metrics(): void
    {
        [$class, $actor] = $this->fixture();
        $session = $this->makeSession($class, 0, 'COMPLETED');
        for ($index = 0; $index < 20; $index++) {
            $participant = $this->participant($session, $index);
            $this->attendance($participant, 'PRESENT', $actor);
        }
        $this->participant($session, 20, false);

        $metrics = app(AttendanceSemanticMetricsService::class)->forClassPeriod($class, now()->startOfMonth(), now()->endOfMonth());

        $this->assertSame(20, $metrics['eligible_opportunities']);
        $this->assertSame(20, $metrics['resolved_opportunities']);
        $this->assertSame(0, $metrics['missing_opportunities']);
        $this->assertSame(100.0, $metrics['physical_presence_rate']);
        $this->assertSame(0.0, $metrics['unexcused_absence_rate']);
        $this->assertSame(100.0, $metrics['completeness_rate']);
    }

    public function test_unresolved_required_opportunities_do_not_report_zero_as_physical_or_absence(): void
    {
        [$class] = $this->fixture();
        $session = $this->makeSession($class, 0, 'COMPLETED');
        $this->participant($session, 0);

        $metrics = app(AttendanceSemanticMetricsService::class)->forClassPeriod($class, now()->startOfMonth(), now()->endOfMonth());

        $this->assertSame(1, $metrics['eligible_opportunities']);
        $this->assertSame(0, $metrics['resolved_opportunities']);
        $this->assertSame(1, $metrics['missing_opportunities']);
        $this->assertSame(0.0, $metrics['physical_presence_rate']);
        $this->assertNull($metrics['unexcused_absence_rate']);
        $this->assertSame(0.0, $metrics['completeness_rate']);
    }

    public function test_no_eligible_opportunities_report_null_rates(): void
    {
        [$class] = $this->fixture();

        $metrics = app(AttendanceSemanticMetricsService::class)->forClassPeriod($class, now()->startOfMonth(), now()->endOfMonth());

        $this->assertSame(0, $metrics['eligible_opportunities']);
        $this->assertSame(0, $metrics['resolved_opportunities']);
        $this->assertSame(0, $metrics['missing_opportunities']);
        $this->assertNull($metrics['physical_presence_rate']);
        $this->assertNull($metrics['unexcused_absence_rate']);
        $this->assertNull($metrics['completeness_rate']);
    }

    private function participant(ClassSession $session, int $index, bool $isRequired = true): SessionStudentParticipant
    {
        $student = Student::create(['student_code' => "METRIC-STU-{$session->id}-{$index}", 'full_name' => "Metric {$session->id}-{$index}"]);

        return SessionStudentParticipant::create([
            'class_session_id' => $session->id,
            'student_id' => $student->id,
            'participant_basis' => 'CLASS_ENROLLMENT',
            'is_required' => $isRequired,
        ]);
    }

    private function attendance(SessionStudentParticipant $participant, string $status, User $actor): void
    {
        StudentAttendance::create(['session_student_participant_id' => $participant->id, 'attendance_status' => $status, 'workflow_status' => 'VALIDATED', 'entered_by' => $actor->id, 'entered_at' => now(), 'finalized_by' => $actor->id, 'finalized_at' => now(), 'updated_by' => $actor->id, 'updated_at' => now()]);
    }

    private function fixture(): array
    {
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-METRIC', 'unit_name' => 'Metric Unit', 'unit_type' => 'SCHOOL']);
        $grade = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '1', 'display_name' => 'Tingkat 1', 'sequence_no' => 1]);
        $year = AcademicYear::create(['year_code' => '2026-METRIC', 'display_name' => '2026/2027', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $semester = Semester::create(['academic_year_id' => $year->id, 'semester_code' => 'ODD', 'display_name' => 'Ganjil', 'sequence_no' => 1, 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-31']);
        $class = AcademicClass::create(['class_code' => 'CLASS-METRIC', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id, 'section_code' => 'A', 'display_name' => 'Kelas Metric']);
        $subject = Subject::create(['subject_code' => 'SUBJ-METRIC', 'subject_name' => 'Metric Subject']);
        $teacher = Staff::create(['staff_code' => 'STAFF-METRIC', 'full_name' => 'Metric Teacher']);
        $actor = User::factory()->create();
        TeachingAssignment::create(['assignment_code' => 'TA-METRIC', 'semester_id' => $semester->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $teacher->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);

        return [$class, $actor];
    }

    private function makeSession(AcademicClass $class, int $index, string $status): ClassSession
    {
        $assignment = TeachingAssignment::where('class_id', $class->id)->firstOrFail();
        $start = now()->startOfMonth()->addDays($index)->setTime(8, 0);

        return ClassSession::create(['session_code' => "SESSION-METRIC-{$index}-{$status}", 'teaching_assignment_id' => $assignment->id, 'class_id' => $class->id, 'subject_id' => $assignment->subject_id, 'planned_start_at' => $start, 'planned_end_at' => $start->copy()->addHour(), 'session_source' => 'SCHEDULED', 'participant_scope' => 'FULL_CLASS', 'session_status' => $status]);
    }
}
