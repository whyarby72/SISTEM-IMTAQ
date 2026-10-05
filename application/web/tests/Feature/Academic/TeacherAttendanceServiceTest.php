<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\AttendancePeriodLock;
use App\Domains\Academic\Models\ClassHomeroomAssignment;
use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\ClassSessionGroup;
use App\Domains\Academic\Models\GradeLevel;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\SessionTeacherParticipation;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Models\TeachingAssignment;
use App\Domains\Academic\Services\TeacherAttendanceService;
use App\Models\User;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use App\Shared\Core\Models\Staff;
use App\Shared\Platform\Audit\Models\AuditLog;
use App\Shared\Platform\Authorization\Models\Permission;
use App\Shared\Platform\Authorization\Models\Role;
use App\Shared\Platform\Authorization\Models\UserRoleAssignment;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class TeacherAttendanceServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_wali_kelas_records_and_corrects_teacher_attendance_without_changing_session_status(): void
    {
        [$session, $participation, $homeroom] = $this->fixtures();
        $service = app(TeacherAttendanceService::class);

        $recorded = $service->record($session, $participation, $homeroom, 'PRESENT');
        $corrected = $service->record($session, $recorded, $homeroom, 'ABSENT', null, 'Wali Kelas correction');

        $this->assertSame('ABSENT', $corrected->attendance_status);
        $this->assertSame('PLANNED', $session->fresh()->session_status);
        $this->assertSame(2, AuditLog::where('action', 'TEACHER_ATTENDANCE_RECORDED')->count());
    }

    public function test_non_wali_kelas_and_unsupported_status_are_rejected(): void
    {
        [$session, $participation, , $otherStaff] = $this->fixtures();
        $service = app(TeacherAttendanceService::class);

        $this->expectException(AuthorizationException::class);
        $service->record($session, $participation, $otherStaff, 'PRESENT');
    }

    public function test_late_unavailable_and_no_response_are_not_input_statuses(): void
    {
        [$session, $participation, $homeroom] = $this->fixtures();
        $this->expectException(InvalidArgumentException::class);

        app(TeacherAttendanceService::class)->record($session, $participation, $homeroom, 'LATE');
    }

    public function test_sick_and_permission_are_recorded_for_teacher_evaluation(): void
    {
        [$session, $participation, $homeroom] = $this->fixtures();
        $service = app(TeacherAttendanceService::class);

        $sick = $service->record($session, $participation, $homeroom, 'SICK', null, 'Surat sakit diterima');
        $this->assertSame('SICK', $sick->attendance_status);
        $izin = $service->record($session, $participation, $homeroom, 'IZIN', null, 'Izin keluarga');
        $this->assertSame('IZIN', $izin->attendance_status);
    }

    public function test_completed_session_rejects_direct_teacher_attendance_mutation(): void
    {
        [$session, $participation, $homeroom] = $this->fixtures();
        $session->update(['session_status' => 'COMPLETED']);

        $this->expectException(InvalidArgumentException::class);
        app(TeacherAttendanceService::class)->record($session, $participation, $homeroom, 'PRESENT');
    }

    public function test_locked_ordinary_period_rejects_teacher_attendance_without_audit_or_mutation(): void
    {
        [$session, $participation, $homeroom] = $this->fixtures();
        AttendancePeriodLock::create([
            'class_id' => $session->class_id,
            'period_start' => '2026-07-01',
            'period_end' => '2026-07-31',
            'status' => 'LOCKED',
        ]);

        try {
            app(TeacherAttendanceService::class)->record($session, $participation, $homeroom, 'PRESENT');
            $this->fail('A locked period must reject normal teacher attendance writes.');
        } catch (InvalidArgumentException $exception) {
            $this->assertStringContainsString('periode kehadiran sudah dikunci', strtolower($exception->getMessage()));
        }

        $this->assertNull($participation->fresh()->attendance_status);
        $this->assertSame(0, AuditLog::where('action', 'TEACHER_ATTENDANCE_RECORDED')->count());
    }

    public function test_joint_teacher_attendance_is_blocked_when_anchor_class_is_locked(): void
    {
        [$session, $participation, $homeroom] = $this->jointFixtures(true, false);

        $this->expectException(InvalidArgumentException::class);
        app(TeacherAttendanceService::class)->record($session, $participation, $homeroom, 'PRESENT');
    }

    public function test_joint_teacher_attendance_is_blocked_when_non_anchor_class_is_locked(): void
    {
        [$session, $participation, $homeroom] = $this->jointFixtures(false, true);

        $this->expectException(InvalidArgumentException::class);
        app(TeacherAttendanceService::class)->record($session, $participation, $homeroom, 'PRESENT');
    }

    public function test_cancelled_and_rescheduled_sessions_reject_without_audit_or_mutation(): void
    {
        foreach (['CANCELLED', 'RESCHEDULED'] as $status) {
            [$session, $participation, $homeroom] = $this->fixtures($status);
            $session->update(['session_status' => $status]);

            try {
                app(TeacherAttendanceService::class)->record($session, $participation, $homeroom, 'PRESENT');
                $this->fail("Expected {$status} session to be rejected.");
            } catch (InvalidArgumentException $exception) {
                $this->assertStringContainsString('tidak dapat menerima', $exception->getMessage());
            }

            $this->assertNull($participation->fresh()->attendance_status);
            $this->assertSame(0, AuditLog::where('action', 'TEACHER_ATTENDANCE_RECORDED')->count());
        }
    }

    public function test_stale_planned_caller_is_rechecked_against_completed_database_state(): void
    {
        [$session, $participation, $homeroom] = $this->fixtures('STALE');
        ClassSession::query()->whereKey($session->id)->update(['session_status' => 'COMPLETED']);

        try {
            app(TeacherAttendanceService::class)->record($session, $participation, $homeroom, 'PRESENT');
            $this->fail('Expected stale caller to be rejected.');
        } catch (InvalidArgumentException $exception) {
            $this->assertStringContainsString('tidak dapat menerima', $exception->getMessage());
        }

        $this->assertNull($participation->fresh()->attendance_status);
        $this->assertSame(0, AuditLog::where('action', 'TEACHER_ATTENDANCE_RECORDED')->count());
    }

    public function test_participation_from_another_session_is_rejected_without_mutation(): void
    {
        [$session, $participation, $homeroom] = $this->fixtures('MISMATCH');
        $otherSession = $session->replicate(['id']);
        $otherSession->session_code = 'SESSION-TA-002';
        $otherSession->planned_start_at = '2026-07-06 10:00:00';
        $otherSession->planned_end_at = '2026-07-06 11:00:00';
        $otherSession->save();

        $this->expectException(InvalidArgumentException::class);
        app(TeacherAttendanceService::class)->record($otherSession, $participation, $homeroom, 'PRESENT');

        $this->assertNull($participation->fresh()->attendance_status);
        $this->assertSame(0, AuditLog::where('action', 'TEACHER_ATTENDANCE_RECORDED')->count());
    }

    public function test_waka_and_super_admin_can_record_on_writable_session(): void
    {
        foreach ([['WAKA_AKADEMIK', 'academic.domain.manage'], ['SUPER_ADMIN', 'platform.institution.manage']] as [$roleCode, $permissionCode]) {
            [$session, $participation, $homeroom] = $this->fixtures($roleCode);
            $actor = User::factory()->create();
            $role = Role::create(['code' => $roleCode, 'name' => $roleCode]);
            $permission = Permission::create(['code' => $permissionCode, 'name' => $permissionCode]);
            $role->permissions()->attach($permission->id);
            UserRoleAssignment::create(['user_id' => $actor->id, 'role_id' => $role->id, 'effective_from' => '2026-07-01']);

            $recorded = app(TeacherAttendanceService::class)->record($session, $participation, $homeroom, 'PRESENT', $actor->id);
            $this->assertSame('PRESENT', $recorded->attendance_status);
        }
    }

    private function fixtures(string $suffix = ''): array
    {
        $suffix = $suffix === '' ? '' : '-'.$suffix;
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-TA'.$suffix, 'unit_name' => 'Teacher Attendance Unit'.$suffix, 'unit_type' => 'SCHOOL']);
        $grade = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '1', 'display_name' => 'Tingkat 1', 'sequence_no' => 1]);
        $year = AcademicYear::create(['year_code' => '2026-TA'.$suffix, 'display_name' => '2026/2027'.$suffix, 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $semester = Semester::create(['academic_year_id' => $year->id, 'semester_code' => 'ODD', 'display_name' => 'Semester Ganjil', 'sequence_no' => 1, 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-31']);
        $class = AcademicClass::create(['class_code' => 'CLASS-TA-A'.$suffix, 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id, 'section_code' => 'A', 'display_name' => 'Kelas 1 A'.$suffix]);
        $subject = Subject::create(['subject_code' => 'SUBJ-TA'.$suffix, 'subject_name' => 'Teacher Attendance Subject'.$suffix]);
        $teacher = Staff::create(['staff_code' => 'STAFF-TA-001'.$suffix, 'full_name' => 'Teacher'.$suffix]);
        $homeroom = Staff::create(['staff_code' => 'STAFF-TA-002'.$suffix, 'full_name' => 'Wali Kelas'.$suffix]);
        $otherStaff = Staff::create(['staff_code' => 'STAFF-TA-003'.$suffix, 'full_name' => 'Other Staff'.$suffix]);
        ClassHomeroomAssignment::create(['class_id' => $class->id, 'staff_id' => $homeroom->id, 'effective_from' => '2026-07-01']);
        $assignment = TeachingAssignment::create(['assignment_code' => 'TA-TA-001'.$suffix, 'semester_id' => $semester->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $teacher->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);
        $session = ClassSession::create(['session_code' => 'SESSION-TA-001'.$suffix, 'teaching_assignment_id' => $assignment->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'planned_start_at' => '2026-07-06 08:00:00', 'planned_end_at' => '2026-07-06 09:30:00', 'session_source' => 'SCHEDULED', 'participant_scope' => 'FULL_CLASS', 'session_status' => 'PLANNED']);
        $participation = SessionTeacherParticipation::create(['class_session_id' => $session->id, 'teacher_staff_id' => $teacher->id, 'role' => 'PRIMARY', 'obligation_type' => 'TEACHING_ASSIGNMENT']);

        return [$session, $participation, $homeroom, $otherStaff];
    }

    private function jointFixtures(bool $lockAnchor, bool $lockNonAnchor): array
    {
        [$session, $participation, $homeroom] = $this->fixtures('JOINT');
        $anchor = $session->academicClass()->firstOrFail();
        $nonAnchor = AcademicClass::create([
            'class_code' => 'CLASS-TA-B-JOINT',
            'academic_year_id' => $anchor->academic_year_id,
            'organizational_unit_id' => $anchor->organizational_unit_id,
            'grade_level_id' => $anchor->grade_level_id,
            'section_code' => 'B',
            'display_name' => 'Kelas 1 B Joint',
        ]);
        ClassSessionGroup::create(['class_session_id' => $session->id, 'class_id' => $anchor->id, 'scope_role' => 'PRIMARY']);
        ClassSessionGroup::create(['class_session_id' => $session->id, 'class_id' => $nonAnchor->id, 'scope_role' => 'JOINT']);

        foreach ([$anchor->id => $lockAnchor, $nonAnchor->id => $lockNonAnchor] as $classId => $locked) {
            if ($locked) {
                AttendancePeriodLock::create([
                    'class_id' => $classId,
                    'period_start' => '2026-07-01',
                    'period_end' => '2026-07-31',
                    'status' => 'LOCKED',
                ]);
            }
        }

        return [$session, $participation, $homeroom];
    }
}
