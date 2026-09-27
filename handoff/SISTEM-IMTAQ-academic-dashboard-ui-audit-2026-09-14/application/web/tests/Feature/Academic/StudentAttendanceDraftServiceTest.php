<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\AttendancePeriodLock;
use App\Domains\Academic\Models\ClassHomeroomAssignment;
use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\GradeLevel;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\SessionStudentParticipant;
use App\Domains\Academic\Models\StudentAttendance;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Models\TeachingAssignment;
use App\Domains\Academic\Services\StudentAttendanceDraftService;
use App\Models\User;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use App\Shared\Core\Models\Staff;
use App\Shared\Core\Models\Student;
use App\Shared\Platform\Audit\Models\AuditLog;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use Tests\TestCase;

class StudentAttendanceDraftServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_draft_schema_and_wali_kelas_can_save_incomplete_attendance(): void
    {
        $this->assertTrue(Schema::hasTable('student_attendance'));
        foreach (['session_student_participant_id', 'attendance_status', 'workflow_status', 'version_no', 'entered_by', 'updated_by'] as $column) {
            $this->assertTrue(Schema::hasColumn('student_attendance', $column));
        }

        [$session, $participant, $homeroom, $actor] = $this->fixtures();
        $attendance = app(StudentAttendanceDraftService::class)->save($session, $participant, $homeroom, $actor->id);

        $this->assertSame('DRAFT', $attendance->workflow_status);
        $this->assertNull($attendance->attendance_status);
        $this->assertSame(1, $attendance->version_no);
        $this->assertSame(1, StudentAttendance::count());
        $this->assertSame(1, AuditLog::where('action', 'STUDENT_ATTENDANCE_DRAFT_SAVED')->count());
    }

    public function test_existing_draft_is_editable_and_versioned(): void
    {
        [$session, $participant, $homeroom, $actor] = $this->fixtures();
        $service = app(StudentAttendanceDraftService::class);
        $service->save($session, $participant, $homeroom, $actor->id, ['attendance_status' => 'PRESENT']);

        $updated = $service->save($session, $participant, $homeroom, $actor->id, ['attendance_status' => 'ABSENT', 'notes' => 'Correction']);

        $this->assertSame('ABSENT', $updated->attendance_status);
        $this->assertSame(2, $updated->version_no);
        $this->assertSame('DRAFT', $updated->workflow_status);
    }

    public function test_confirmed_session_with_existing_draft_remains_editable_and_versioned(): void
    {
        [$session, $participant, $homeroom, $actor] = $this->fixtures();
        $session->update(['session_status' => 'CONFIRMED']);
        $service = app(StudentAttendanceDraftService::class);
        $service->save($session, $participant, $homeroom, $actor->id, ['attendance_status' => 'PRESENT']);

        $updated = $service->save($session, $participant, $homeroom, $actor->id, ['attendance_status' => 'ABSENT']);

        $this->assertSame('ABSENT', $updated->attendance_status);
        $this->assertSame('DRAFT', $updated->workflow_status);
        $this->assertSame(2, $updated->version_no);
    }

    public function test_completed_session_is_rejected_without_mutation_or_draft_audit(): void
    {
        [$session, $participant, $homeroom, $actor] = $this->fixtures();
        $service = app(StudentAttendanceDraftService::class);
        $attendance = $service->save($session, $participant, $homeroom, $actor->id, ['attendance_status' => 'PRESENT']);
        $attendance->update(['attendance_status' => 'ABSENT', 'version_no' => 2, 'workflow_status' => 'DRAFT']);
        $session->update(['session_status' => 'COMPLETED']);
        $auditCount = AuditLog::where('action', 'STUDENT_ATTENDANCE_DRAFT_SAVED')->count();

        $this->expectException(InvalidArgumentException::class);
        try {
            $service->save($session, $participant, $homeroom, $actor->id, ['attendance_status' => 'SICK']);
        } finally {
            $this->assertSame('ABSENT', $attendance->fresh()->attendance_status);
            $this->assertSame('DRAFT', $attendance->fresh()->workflow_status);
            $this->assertSame(2, $attendance->fresh()->version_no);
            $this->assertSame('COMPLETED', $session->fresh()->session_status);
            $this->assertSame($auditCount, AuditLog::where('action', 'STUDENT_ATTENDANCE_DRAFT_SAVED')->count());
        }
    }

    public function test_validated_attendance_is_rejected_without_reopening_or_audit(): void
    {
        [$session, $participant, $homeroom, $actor] = $this->fixtures();
        $service = app(StudentAttendanceDraftService::class);
        $attendance = $service->save($session, $participant, $homeroom, $actor->id, ['attendance_status' => 'PRESENT']);
        $attendance->update(['workflow_status' => 'VALIDATED', 'version_no' => 2]);
        $auditCount = AuditLog::where('action', 'STUDENT_ATTENDANCE_DRAFT_SAVED')->count();

        $this->expectException(InvalidArgumentException::class);
        try {
            $service->save($session, $participant, $homeroom, $actor->id, ['attendance_status' => 'ABSENT']);
        } finally {
            $this->assertSame('PRESENT', $attendance->fresh()->attendance_status);
            $this->assertSame('VALIDATED', $attendance->fresh()->workflow_status);
            $this->assertSame(2, $attendance->fresh()->version_no);
            $this->assertSame($auditCount, AuditLog::where('action', 'STUDENT_ATTENDANCE_DRAFT_SAVED')->count());
        }
    }

    public function test_locked_period_is_rejected_without_mutation_or_audit(): void
    {
        [$session, $participant, $homeroom, $actor] = $this->fixtures();
        $service = app(StudentAttendanceDraftService::class);
        $attendance = $service->save($session, $participant, $homeroom, $actor->id, ['attendance_status' => 'PRESENT']);
        AttendancePeriodLock::create([
            'class_id' => $session->class_id,
            'period_start' => '2026-07-01',
            'period_end' => '2026-07-31',
            'status' => 'LOCKED',
            'locked_by' => $actor->id,
            'locked_at' => now(),
        ]);
        $auditCount = AuditLog::where('action', 'STUDENT_ATTENDANCE_DRAFT_SAVED')->count();

        $this->expectException(InvalidArgumentException::class);
        try {
            $service->save($session, $participant, $homeroom, $actor->id, ['attendance_status' => 'ABSENT']);
        } finally {
            $this->assertSame('PRESENT', $attendance->fresh()->attendance_status);
            $this->assertSame('DRAFT', $attendance->fresh()->workflow_status);
            $this->assertSame(1, $attendance->fresh()->version_no);
            $this->assertSame($auditCount, AuditLog::where('action', 'STUDENT_ATTENDANCE_DRAFT_SAVED')->count());
        }
    }

    public function test_cancelled_and_rescheduled_sessions_are_rejected(): void
    {
        [$session, $participant, $homeroom, $actor] = $this->fixtures();

        foreach (['CANCELLED', 'RESCHEDULED'] as $status) {
            $session->update(['session_status' => $status]);

            try {
                app(StudentAttendanceDraftService::class)->save($session, $participant, $homeroom, $actor->id, ['attendance_status' => 'PRESENT']);
                $this->fail("A {$status} session should reject draft attendance.");
            } catch (InvalidArgumentException) {
                // Expected rejection.
            }

            $this->assertSame($status, $session->fresh()->session_status);
        }
    }

    public function test_non_homeroom_and_invalid_session_participants_are_rejected(): void
    {
        [$session, $participant, , $actor, $otherStaff] = $this->fixtures();
        $this->expectException(AuthorizationException::class);
        app(StudentAttendanceDraftService::class)->save($session, $participant, $otherStaff, $actor->id);
    }

    private function fixtures(): array
    {
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-SA', 'unit_name' => 'Student Attendance Unit', 'unit_type' => 'SCHOOL']);
        $grade = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '1', 'display_name' => 'Tingkat 1', 'sequence_no' => 1]);
        $year = AcademicYear::create(['year_code' => '2026-SA', 'display_name' => '2026/2027', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $semester = Semester::create(['academic_year_id' => $year->id, 'semester_code' => 'ODD', 'display_name' => 'Semester Ganjil', 'sequence_no' => 1, 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-31']);
        $class = AcademicClass::create(['class_code' => 'CLASS-SA-A', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id, 'section_code' => 'A', 'display_name' => 'Kelas 1 A']);
        $subject = Subject::create(['subject_code' => 'SUBJ-SA', 'subject_name' => 'Student Attendance Subject']);
        $teacher = Staff::create(['staff_code' => 'STAFF-SA-001', 'full_name' => 'Teacher']);
        $homeroom = Staff::create(['staff_code' => 'STAFF-SA-002', 'full_name' => 'Wali Kelas']);
        $otherStaff = Staff::create(['staff_code' => 'STAFF-SA-003', 'full_name' => 'Other Staff']);
        $actor = User::factory()->create();
        ClassHomeroomAssignment::create(['class_id' => $class->id, 'staff_id' => $homeroom->id, 'effective_from' => '2026-07-01']);
        $assignment = TeachingAssignment::create(['assignment_code' => 'TA-SA-001', 'semester_id' => $semester->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $teacher->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);
        $session = ClassSession::create(['session_code' => 'SESSION-SA-001', 'teaching_assignment_id' => $assignment->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'planned_start_at' => '2026-07-06 08:00:00', 'planned_end_at' => '2026-07-06 09:30:00', 'session_source' => 'SCHEDULED', 'participant_scope' => 'FULL_CLASS', 'session_status' => 'PLANNED']);
        $student = Student::create(['student_code' => 'STU-SA-001', 'full_name' => 'Student']);
        $participant = SessionStudentParticipant::create(['class_session_id' => $session->id, 'student_id' => $student->id, 'participant_basis' => 'CLASS_ENROLLMENT']);

        return [$session, $participant, $homeroom, $actor, $otherStaff];
    }
}
