<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\ClassHomeroomAssignment;
use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\GradeLevel;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\SessionStudentParticipant;
use App\Domains\Academic\Models\StudentAttendance;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Models\TeachingAssignment;
use App\Domains\Academic\Services\StudentAttendanceCorrectionService;
use App\Models\User;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use App\Shared\Core\Models\Staff;
use App\Shared\Core\Models\Student;
use App\Shared\Platform\Audit\Models\AuditLog;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class StudentAttendanceCorrectionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_wali_kelas_corrects_validated_attendance_with_reason_and_audit(): void
    {
        [$attendance, $homeroom, $actor] = $this->fixtures('VALIDATED');

        $corrected = app(StudentAttendanceCorrectionService::class)->correct($attendance, $homeroom, $actor->id, 2, 'Salah input awal', ['attendance_status' => 'IZIN', 'notes' => 'Izin sesi ini']);

        $this->assertSame('IZIN', $corrected->attendance_status);
        $this->assertSame('VALIDATED', $corrected->workflow_status);
        $this->assertSame(3, $corrected->version_no);
        $this->assertSame(1, AuditLog::where('action', 'STUDENT_ATTENDANCE_CORRECTED')->count());
    }

    public function test_correction_requires_validated_row_reason_and_current_version(): void
    {
        [$attendance, $homeroom, $actor] = $this->fixtures('DRAFT');
        $this->expectException(InvalidArgumentException::class);
        app(StudentAttendanceCorrectionService::class)->correct($attendance, $homeroom, $actor->id, 1, '', ['attendance_status' => 'ABSENT']);
    }

    public function test_non_homeroom_and_stale_version_are_rejected(): void
    {
        [$attendance, , $actor, $otherStaff] = $this->fixtures('VALIDATED');
        $this->expectException(AuthorizationException::class);
        app(StudentAttendanceCorrectionService::class)->correct($attendance, $otherStaff, $actor->id, 2, 'Correction', ['attendance_status' => 'ABSENT']);
    }

    private function fixtures(string $workflowStatus): array
    {
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-CORR', 'unit_name' => 'Correction Unit', 'unit_type' => 'SCHOOL']);
        $grade = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '1', 'display_name' => 'Tingkat 1', 'sequence_no' => 1]);
        $year = AcademicYear::create(['year_code' => '2026-CORR', 'display_name' => '2026/2027', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $semester = Semester::create(['academic_year_id' => $year->id, 'semester_code' => 'ODD', 'display_name' => 'Semester Ganjil', 'sequence_no' => 1, 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-31']);
        $class = AcademicClass::create(['class_code' => 'CLASS-CORR-A', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id, 'section_code' => 'A', 'display_name' => 'Kelas 1 A']);
        $subject = Subject::create(['subject_code' => 'SUBJ-CORR', 'subject_name' => 'Correction Subject']);
        $teacher = Staff::create(['staff_code' => 'STAFF-CORR-001', 'full_name' => 'Teacher']);
        $homeroom = Staff::create(['staff_code' => 'STAFF-CORR-002', 'full_name' => 'Wali Kelas']);
        $otherStaff = Staff::create(['staff_code' => 'STAFF-CORR-003', 'full_name' => 'Other Staff']);
        $actor = User::factory()->create();
        ClassHomeroomAssignment::create(['class_id' => $class->id, 'staff_id' => $homeroom->id, 'effective_from' => '2026-07-01']);
        $assignment = TeachingAssignment::create(['assignment_code' => 'TA-CORR-001', 'semester_id' => $semester->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $teacher->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);
        $session = ClassSession::create(['session_code' => 'SESSION-CORR-001', 'teaching_assignment_id' => $assignment->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'planned_start_at' => '2026-07-06 08:00:00', 'planned_end_at' => '2026-07-06 09:30:00', 'session_source' => 'SCHEDULED', 'participant_scope' => 'FULL_CLASS', 'session_status' => 'PLANNED']);
        $participant = SessionStudentParticipant::create(['class_session_id' => $session->id, 'student_id' => Student::create(['student_code' => 'STU-CORR-001', 'full_name' => 'Student'])->id, 'participant_basis' => 'CLASS_ENROLLMENT']);
        $attendance = StudentAttendance::create(['session_student_participant_id' => $participant->id, 'attendance_status' => 'PRESENT', 'workflow_status' => $workflowStatus, 'version_no' => 2, 'entered_by' => $actor->id, 'entered_at' => now(), 'finalized_by' => $workflowStatus === 'VALIDATED' ? $actor->id : null, 'finalized_at' => $workflowStatus === 'VALIDATED' ? now() : null, 'updated_by' => $actor->id, 'updated_at' => now()]);

        return [$attendance, $homeroom, $actor, $otherStaff];
    }
}
