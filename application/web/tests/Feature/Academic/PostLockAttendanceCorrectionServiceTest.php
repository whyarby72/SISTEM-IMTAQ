<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\AttendancePeriodLock;
use App\Domains\Academic\Models\ClassHomeroomAssignment;
use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\ClassSessionGroup;
use App\Domains\Academic\Models\GradeLevel;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\SessionStudentParticipant;
use App\Domains\Academic\Models\StudentAttendance;
use App\Domains\Academic\Models\StudentClassEnrollment;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Models\TeachingAssignment;
use App\Domains\Academic\Services\PostLockAttendanceCorrectionService;
use App\Models\User;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use App\Shared\Core\Models\Staff;
use App\Shared\Core\Models\Student;
use App\Shared\Platform\Authorization\Models\Role;
use App\Shared\Platform\Authorization\Models\UserRoleAssignment;
use App\Shared\Platform\Authorization\Models\UserStaffLink;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use Tests\TestCase;

class PostLockAttendanceCorrectionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_wali_requests_waka_approves_and_applies_targeted_post_lock_correction(): void
    {
        [$attendance, $session, $wali, $waka] = $this->fixtures();
        $service = app(PostLockAttendanceCorrectionService::class);

        $request = $service->submit($attendance, $wali, 2, 'Perlu koreksi setelah rekap', ['attendance_status' => 'IZIN']);
        $this->assertSame('PENDING', $request->status);
        $approved = $service->review($request, $waka, true);
        $corrected = $service->applyApproved($approved, $waka);

        $this->assertTrue(Schema::hasTable('correction_requests'));
        $this->assertSame('APPLIED', $approved->fresh()->status);
        $this->assertSame('IZIN', $corrected->attendance_status);
        $this->assertSame(3, $corrected->version_no);
        $this->assertSame('PLANNED', $session->fresh()->session_status);
    }

    public function test_post_lock_request_requires_locked_period_and_waka_review(): void
    {
        [$attendance, , $wali, $waka] = $this->fixtures(false);
        $service = app(PostLockAttendanceCorrectionService::class);

        $this->expectException(InvalidArgumentException::class);
        $service->submit($attendance, $wali, 2, 'Correction', ['attendance_status' => 'ABSENT']);
    }

    public function test_super_admin_cannot_directly_override_locked_attendance(): void
    {
        [$attendance, , , , $superAdmin] = $this->fixtures();
        try {
            app(PostLockAttendanceCorrectionService::class)->superAdminOverride($attendance, $superAdmin, 2, 'Emergency correction', ['attendance_status' => 'ABSENT']);
            $this->fail('Locked direct override must be rejected.');
        } catch (InvalidArgumentException $exception) {
            $this->assertStringContainsString('post-lock correction workflow', $exception->getMessage());
        }

        $this->assertSame('PRESENT', $attendance->fresh()->attendance_status);
        $this->assertSame(2, $attendance->fresh()->version_no);
    }

    public function test_waka_cannot_directly_correct_locked_attendance(): void
    {
        [$attendance, , , $waka] = $this->fixtures();
        try {
            app(PostLockAttendanceCorrectionService::class)->wakaOverride($attendance, $waka, 2, 'Koreksi status oleh Waka', ['attendance_status' => 'ABSENT']);
            $this->fail('Locked direct override must be rejected.');
        } catch (InvalidArgumentException $exception) {
            $this->assertStringContainsString('post-lock correction workflow', $exception->getMessage());
        }

        $this->assertSame('PRESENT', $attendance->fresh()->attendance_status);
        $this->assertSame(2, $attendance->fresh()->version_no);
    }

    public function test_joint_participant_does_not_inherit_anchor_lock_for_submission(): void
    {
        [$attendanceB, , $waliB] = $this->jointFixtures(true, false);

        $this->expectException(InvalidArgumentException::class);
        app(PostLockAttendanceCorrectionService::class)->submit($attendanceB, $waliB, 2, 'Correction', ['attendance_status' => 'ABSENT']);
    }

    public function test_joint_participant_uses_its_effective_class_lock_for_submission(): void
    {
        [$attendanceB, , $waliB] = $this->jointFixtures(false, true);

        $request = app(PostLockAttendanceCorrectionService::class)->submit($attendanceB, $waliB, 2, 'Correction', ['attendance_status' => 'ABSENT']);

        $this->assertSame('PENDING', $request->status);
    }

    public function test_approved_joint_correction_rechecks_participant_lock_on_apply(): void
    {
        [$attendanceB, , $waliB, $waka] = $this->jointFixtures(false, true);
        $service = app(PostLockAttendanceCorrectionService::class);
        $request = $service->submit($attendanceB, $waliB, 2, 'Correction', ['attendance_status' => 'ABSENT']);
        $approved = $service->review($request, $waka, true);

        AttendancePeriodLock::query()->where('class_id', $attendanceB->participant->student->classEnrollments->first()->class_id)->delete();

        $this->expectException(InvalidArgumentException::class);
        $service->applyApproved($approved, $waka);
    }

    public function test_wali_cannot_review_and_super_admin_cannot_be_replaced_by_client_input(): void
    {
        [$attendance, , $wali, $waka] = $this->fixtures();
        $request = app(PostLockAttendanceCorrectionService::class)->submit($attendance, $wali, 2, 'Correction', ['attendance_status' => 'ABSENT']);

        $this->expectException(AuthorizationException::class);
        app(PostLockAttendanceCorrectionService::class)->review($request, $wali, true);
    }

    public function test_review_is_single_decision_and_second_review_is_rejected(): void
    {
        [$attendance, , $wali, $waka] = $this->fixtures();
        $service = app(PostLockAttendanceCorrectionService::class);
        $request = $service->submit($attendance, $wali, 2, 'Correction', ['attendance_status' => 'ABSENT']);
        $approved = $service->review($request, $waka, true);

        try {
            $service->review($request, $waka, false, 'Late rejection');
            $this->fail('A decided request must not be reviewed again.');
        } catch (InvalidArgumentException $exception) {
            $this->assertStringContainsString('not pending', $exception->getMessage());
        }

        $this->assertSame('APPROVED', $request->fresh()->status);
        $this->assertSame($approved->version_no, $request->fresh()->version_no);
    }

    public function test_approved_request_can_only_be_applied_once(): void
    {
        [$attendance, , $wali, $waka] = $this->fixtures();
        $service = app(PostLockAttendanceCorrectionService::class);
        $request = $service->submit($attendance, $wali, 2, 'Correction', ['attendance_status' => 'IZIN']);
        $service->review($request, $waka, true);
        $service->applyApproved($request, $waka);

        try {
            $service->applyApproved($request, $waka);
            $this->fail('An applied request must not be applied again.');
        } catch (InvalidArgumentException $exception) {
            $this->assertStringContainsString('approved correction requests', $exception->getMessage());
        }

        $this->assertSame('APPLIED', $request->fresh()->status);
        $this->assertSame(3, $attendance->fresh()->version_no);
        $this->assertSame('IZIN', $attendance->fresh()->attendance_status);
    }

    private function fixtures(bool $locked = true): array
    {
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-POST', 'unit_name' => 'Post Lock Unit', 'unit_type' => 'SCHOOL']);
        $grade = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '1', 'display_name' => 'Tingkat 1', 'sequence_no' => 1]);
        $year = AcademicYear::create(['year_code' => '2026-POST', 'display_name' => '2026/2027', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $semester = Semester::create(['academic_year_id' => $year->id, 'semester_code' => 'ODD', 'display_name' => 'Semester Ganjil', 'sequence_no' => 1, 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-31']);
        $class = AcademicClass::create(['class_code' => 'CLASS-POST-A', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id, 'section_code' => 'A', 'display_name' => 'Kelas 1 A']);
        $subject = Subject::create(['subject_code' => 'SUBJ-POST', 'subject_name' => 'Post Lock Subject']);
        $teacher = Staff::create(['staff_code' => 'STAFF-POST-001', 'full_name' => 'Teacher']);
        $homeroom = Staff::create(['staff_code' => 'STAFF-POST-002', 'full_name' => 'Wali Kelas']);
        $wali = User::factory()->create();
        $waka = User::factory()->create();
        $superAdmin = User::factory()->create();
        $waliRole = Role::create(['code' => 'WALI_KELAS', 'name' => 'Wali Kelas']);
        $wakaRole = Role::create(['code' => 'WAKA_AKADEMIK', 'name' => 'Waka Akademik']);
        $superRole = Role::create(['code' => 'SUPER_ADMIN', 'name' => 'Super Admin']);
        UserRoleAssignment::create(['user_id' => $wali->id, 'role_id' => $waliRole->id, 'effective_from' => '2026-07-01']);
        UserRoleAssignment::create(['user_id' => $waka->id, 'role_id' => $wakaRole->id, 'effective_from' => '2026-07-01']);
        UserRoleAssignment::create(['user_id' => $superAdmin->id, 'role_id' => $superRole->id, 'effective_from' => '2026-07-01']);
        UserStaffLink::create(['user_id' => $wali->id, 'staff_id' => $homeroom->id, 'effective_from' => '2026-07-01']);
        ClassHomeroomAssignment::create(['class_id' => $class->id, 'staff_id' => $homeroom->id, 'effective_from' => '2026-07-01']);
        $assignment = TeachingAssignment::create(['assignment_code' => 'TA-POST-001', 'semester_id' => $semester->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $teacher->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);
        $session = ClassSession::create(['session_code' => 'SESSION-POST-001', 'teaching_assignment_id' => $assignment->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'planned_start_at' => '2026-07-06 08:00:00', 'planned_end_at' => '2026-07-06 09:30:00', 'session_source' => 'SCHEDULED', 'participant_scope' => 'FULL_CLASS', 'session_status' => 'PLANNED']);
        $participant = SessionStudentParticipant::create(['class_session_id' => $session->id, 'student_id' => Student::create(['student_code' => 'STU-POST-001', 'full_name' => 'Student'])->id, 'participant_basis' => 'CLASS_ENROLLMENT']);
        StudentClassEnrollment::create(['student_id' => $participant->student_id, 'class_id' => $class->id, 'effective_from' => '2026-07-01', 'status' => 'ACTIVE']);
        $attendance = StudentAttendance::create(['session_student_participant_id' => $participant->id, 'attendance_status' => 'PRESENT', 'workflow_status' => 'VALIDATED', 'version_no' => 2, 'entered_by' => $wali->id, 'entered_at' => now(), 'finalized_by' => $wali->id, 'finalized_at' => now(), 'updated_by' => $wali->id, 'updated_at' => now()]);
        if ($locked) {
            AttendancePeriodLock::create(['class_id' => $class->id, 'period_start' => '2026-07-01', 'period_end' => '2026-07-31', 'status' => 'LOCKED', 'locked_by' => $waka->id, 'locked_at' => Carbon::parse('2026-08-15'), 'version_no' => 1]);
        }

        return [$attendance, $session, $wali, $waka, $superAdmin];
    }

    private function jointFixtures(bool $lockAnchor, bool $lockNonAnchor): array
    {
        [$attendanceA, $session, $waliA, $waka] = $this->fixtures(false);
        $anchor = $session->academicClass()->firstOrFail();
        $nonAnchor = AcademicClass::create([
            'class_code' => 'CLASS-POST-B',
            'academic_year_id' => $anchor->academic_year_id,
            'organizational_unit_id' => $anchor->organizational_unit_id,
            'grade_level_id' => $anchor->grade_level_id,
            'section_code' => 'B',
            'display_name' => 'Kelas 1 B',
        ]);
        ClassSessionGroup::create(['class_session_id' => $session->id, 'class_id' => $anchor->id, 'scope_role' => 'PRIMARY']);
        ClassSessionGroup::create(['class_session_id' => $session->id, 'class_id' => $nonAnchor->id, 'scope_role' => 'JOINT']);

        $homeroomB = Staff::create(['staff_code' => 'STAFF-POST-003', 'full_name' => 'Wali Kelas B']);
        $waliB = User::factory()->create();
        $waliRole = Role::where('code', 'WALI_KELAS')->firstOrFail();
        UserRoleAssignment::create(['user_id' => $waliB->id, 'role_id' => $waliRole->id, 'effective_from' => '2026-07-01']);
        UserStaffLink::create(['user_id' => $waliB->id, 'staff_id' => $homeroomB->id, 'effective_from' => '2026-07-01']);
        ClassHomeroomAssignment::create(['class_id' => $nonAnchor->id, 'staff_id' => $homeroomB->id, 'effective_from' => '2026-07-01']);

        $studentB = Student::create(['student_code' => 'STU-POST-002', 'full_name' => 'Student B']);
        $participantB = SessionStudentParticipant::create(['class_session_id' => $session->id, 'student_id' => $studentB->id, 'participant_basis' => 'CLASS_ENROLLMENT']);
        StudentClassEnrollment::create(['student_id' => $studentB->id, 'class_id' => $nonAnchor->id, 'effective_from' => '2026-07-01', 'status' => 'ACTIVE']);
        $attendanceB = StudentAttendance::create(['session_student_participant_id' => $participantB->id, 'attendance_status' => 'PRESENT', 'workflow_status' => 'VALIDATED', 'version_no' => 2, 'entered_by' => $waliA->id, 'entered_at' => now(), 'finalized_by' => $waliA->id, 'finalized_at' => now(), 'updated_by' => $waliA->id, 'updated_at' => now()]);

        foreach ([$anchor->id => $lockAnchor, $nonAnchor->id => $lockNonAnchor] as $classId => $locked) {
            if ($locked) {
                AttendancePeriodLock::create(['class_id' => $classId, 'period_start' => '2026-07-01', 'period_end' => '2026-07-31', 'status' => 'LOCKED', 'locked_by' => $waka->id, 'locked_at' => Carbon::parse('2026-08-15'), 'version_no' => 1]);
            }
        }

        return [$attendanceB, $attendanceA, $waliB, $waka];
    }
}
