<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\AttendancePeriodLock;
use App\Domains\Academic\Models\ClassHomeroomAssignment;
use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\GradeLevel;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\SessionStudentParticipant;
use App\Domains\Academic\Models\SessionTeacherParticipation;
use App\Domains\Academic\Models\StudentAttendance;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Models\TeachingAssignment;
use App\Domains\Academic\Services\HistoricalHomeroomHandoverService;
use App\Models\User;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use App\Shared\Core\Models\Staff;
use App\Shared\Core\Models\Student;
use App\Shared\Platform\Audit\Models\AuditLog;
use App\Shared\Platform\Authorization\Models\Permission;
use App\Shared\Platform\Authorization\Models\Role;
use App\Shared\Platform\Authorization\Models\UserRoleAssignment;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class HistoricalHomeroomHandoverServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_academic_completes_historical_session_with_explicit_permission_and_audit(): void
    {
        [$session, $participant, $admin] = $this->fixtures(true);

        $result = app(HistoricalHomeroomHandoverService::class)->complete($session, $admin, 'Serah terima wali kelas periode lama.');

        $this->assertSame('COMPLETED', $result->session_status);
        $this->assertSame('VALIDATED', StudentAttendance::where('session_student_participant_id', $participant->id)->value('workflow_status'));
        $this->assertSame(1, AuditLog::where('action', 'HISTORICAL_HOMEROOM_HANDOVER_COMPLETED')->count());
        $this->assertSame('Serah terima wali kelas periode lama.', AuditLog::where('action', 'HISTORICAL_HOMEROOM_HANDOVER_COMPLETED')->value('reason'));
    }

    public function test_handover_requires_permission_and_does_not_bypass_locked_period(): void
    {
        [$session, , $admin] = $this->fixtures(false);
        $this->expectException(AuthorizationException::class);
        app(HistoricalHomeroomHandoverService::class)->complete($session, $admin, 'Tidak berwenang.');
    }

    public function test_handover_rejects_locked_period(): void
    {
        [$session, , $admin] = $this->fixtures(true);
        AttendancePeriodLock::create([
            'class_id' => $session->class_id,
            'period_start' => '2026-07-01',
            'period_end' => '2026-07-31',
            'status' => 'LOCKED',
        ]);

        $this->expectException(InvalidArgumentException::class);
        app(HistoricalHomeroomHandoverService::class)->complete($session, $admin, 'Locked period must use correction workflow.');
    }

    public function test_handover_requires_reason(): void
    {
        [$session, , $admin] = $this->fixtures(true);
        $this->expectException(InvalidArgumentException::class);
        app(HistoricalHomeroomHandoverService::class)->complete($session, $admin, '   ');
    }

    private function fixtures(bool $withPermission): array
    {
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-HHO', 'unit_name' => 'Handover Unit', 'unit_type' => 'SCHOOL']);
        $grade = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '1', 'display_name' => 'Tingkat 1', 'sequence_no' => 1]);
        $year = AcademicYear::create(['year_code' => '2026-HHO', 'display_name' => '2026/2027', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $class = AcademicClass::create(['class_code' => 'CLASS-HHO-A', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id, 'section_code' => 'A', 'display_name' => 'Kelas Handover']);
        $subject = Subject::create(['subject_code' => 'SUBJ-HHO', 'subject_name' => 'Handover']);
        $staff = Staff::create(['staff_code' => 'STAFF-HHO', 'full_name' => 'Wali Historis']);
        $admin = User::factory()->create();
        ClassHomeroomAssignment::create(['class_id' => $class->id, 'staff_id' => $staff->id, 'effective_from' => '2026-07-01']);
        $assignment = TeachingAssignment::create(['assignment_code' => 'TA-HHO', 'semester_id' => Semester::create(['academic_year_id' => $year->id, 'semester_code' => 'ODD', 'display_name' => 'Ganjil', 'sequence_no' => 1, 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-31'])->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $staff->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);
        $session = ClassSession::create(['session_code' => 'SESSION-HHO', 'teaching_assignment_id' => $assignment->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'planned_start_at' => '2026-07-06 08:00:00', 'planned_end_at' => '2026-07-06 09:30:00', 'session_source' => 'SCHEDULED', 'participant_scope' => 'FULL_CLASS', 'session_status' => 'PLANNED']);
        $participant = SessionStudentParticipant::create(['class_session_id' => $session->id, 'student_id' => Student::create(['student_code' => 'STU-HHO', 'full_name' => 'Student Handover'])->id, 'participant_basis' => 'CLASS_ENROLLMENT']);
        SessionTeacherParticipation::create(['class_session_id' => $session->id, 'teacher_staff_id' => $staff->id, 'role' => 'PRIMARY', 'obligation_type' => 'TEACHING_ASSIGNMENT', 'attendance_status' => 'PRESENT']);
        StudentAttendance::create(['session_student_participant_id' => $participant->id, 'attendance_status' => 'PRESENT', 'entered_by' => $admin->id, 'entered_at' => now(), 'updated_by' => $admin->id, 'updated_at' => now()]);

        $role = Role::create(['code' => 'WAKA_AKADEMIK', 'name' => 'Waka Akademik']);
        if ($withPermission) {
            $permission = Permission::create(['code' => 'academic.student_attendance.handover_complete', 'name' => 'Complete handover']);
            $role->permissions()->attach($permission);
        }
        UserRoleAssignment::create(['user_id' => $admin->id, 'role_id' => $role->id]);

        return [$session, $participant, $admin];
    }
}
