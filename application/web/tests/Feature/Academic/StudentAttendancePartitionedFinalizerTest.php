<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\ClassHomeroomAssignment;
use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\ClassSessionGroup;
use App\Domains\Academic\Models\GradeLevel;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\SessionStudentParticipant;
use App\Domains\Academic\Models\SessionTeacherParticipation;
use App\Domains\Academic\Models\StudentAttendance;
use App\Domains\Academic\Models\StudentClassEnrollment;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Models\TeachingAssignment;
use App\Domains\Academic\Services\AcademicRoleDashboardService;
use App\Domains\Academic\Services\StudentAttendanceFinalizer;
use App\Models\User;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use App\Shared\Core\Models\Staff;
use App\Shared\Core\Models\Student;
use App\Shared\Platform\Audit\Models\AuditLog;
use App\Shared\Platform\Authorization\Models\Role;
use App\Shared\Platform\Authorization\Models\UserRoleAssignment;
use App\Shared\Platform\Authorization\Models\UserStaffLink;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use InvalidArgumentException;
use Tests\TestCase;

class StudentAttendancePartitionedFinalizerTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_wali_finalizes_only_their_partition_and_session_completes_after_both_partitions(): void
    {
        $fixture = $this->jointFixture();

        $this->finalizeDraft($fixture['anchorParticipant'], $fixture['anchorUser'], 'PRESENT');
        $this->finalizeDraft($fixture['secondaryParticipant'], $fixture['secondaryUser'], 'ABSENT');
        $firstResult = app(StudentAttendanceFinalizer::class)->finalize(
            $fixture['session'],
            $fixture['anchorStaff'],
            $fixture['anchorUser']->id,
        );

        $this->assertSame('PLANNED', $firstResult->session_status);
        $this->assertSame('VALIDATED', StudentAttendance::where('session_student_participant_id', $fixture['anchorParticipant']->id)->value('workflow_status'));
        $this->assertSame('DRAFT', StudentAttendance::where('session_student_participant_id', $fixture['secondaryParticipant']->id)->value('workflow_status'));

        $secondResult = app(StudentAttendanceFinalizer::class)->finalize(
            $fixture['session']->fresh(),
            $fixture['secondaryStaff'],
            $fixture['secondaryUser']->id,
        );

        $this->assertSame('COMPLETED', $secondResult->session_status);
        $this->assertSame(2, StudentAttendance::where('workflow_status', 'VALIDATED')->count());
        $this->assertSame(2, AuditLog::where('action', 'STUDENT_ATTENDANCE_FINALIZED')->count());
    }

    public function test_wali_cannot_supply_expected_version_for_another_joint_partition(): void
    {
        $fixture = $this->jointFixture();
        $this->finalizeDraft($fixture['anchorParticipant'], $fixture['anchorUser'], 'PRESENT');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('outside the authorized finalization scope');
        app(StudentAttendanceFinalizer::class)->finalize(
            $fixture['session'],
            $fixture['anchorStaff'],
            $fixture['anchorUser']->id,
            [$fixture['secondaryParticipant']->id => 1],
        );
    }

    public function test_unmapped_joint_participant_fails_closed_before_finalization(): void
    {
        $fixture = $this->jointFixture();
        $unmappedStudent = Student::create(['student_code' => 'R2BC-UNMAPPED', 'full_name' => 'Unmapped']);
        $unmapped = SessionStudentParticipant::create([
            'class_session_id' => $fixture['session']->id,
            'student_id' => $unmappedStudent->id,
            'participant_basis' => 'CLASS_ENROLLMENT',
        ]);
        StudentAttendance::create([
            'session_student_participant_id' => $unmapped->id,
            'attendance_status' => 'PRESENT',
            'entered_by' => $fixture['anchorUser']->id,
            'entered_at' => now(),
            'updated_by' => $fixture['anchorUser']->id,
            'updated_at' => now(),
        ]);

        $this->expectException(AuthorizationException::class);
        app(StudentAttendanceFinalizer::class)->finalize(
            $fixture['session'],
            $fixture['anchorStaff'],
            $fixture['anchorUser']->id,
        );
    }

    public function test_wali_http_finalization_validates_only_their_joint_partition(): void
    {
        $fixture = $this->jointFixture();

        $this->actingAs($fixture['anchorUser'])
            ->post(route('academic.attendance.finalize', $fixture['session']), [
                'participants' => [$fixture['anchorParticipant']->id => ['attendance_status' => 'PRESENT']],
            ])
            ->assertRedirect(route('academic.attendance.show', $fixture['session']))
            ->assertSessionHas('status', 'Kehadiran berhasil disahkan.');

        $this->assertDatabaseHas('student_attendance', [
            'session_student_participant_id' => $fixture['anchorParticipant']->id,
            'workflow_status' => 'VALIDATED',
        ]);
        $this->assertDatabaseMissing('student_attendance', [
            'session_student_participant_id' => $fixture['secondaryParticipant']->id,
        ]);
        $this->assertSame('PLANNED', $fixture['session']->fresh()->session_status);

        $this->actingAs($fixture['anchorUser'])
            ->get(route('academic.attendance.show', $fixture['session']))
            ->assertOk()
            ->assertSee('Sudah disahkan untuk kelas Anda.')
            ->assertSee('Sesi gabungan masih menunggu pengesahan kelas lain.');

        $dashboard = app(AcademicRoleDashboardService::class)->forUser(
            $fixture['anchorUser'],
            Carbon::parse('2026-07-01'),
            Carbon::parse('2026-07-31')->endOfDay(),
        );
        $dashboardSession = $dashboard['attendance_sessions']->firstWhere('id', $fixture['session']->id);
        $this->assertSame('Sudah disahkan', $dashboardSession?->attendance_label);
    }

    public function test_wali_http_finalization_rejects_cross_partition_participant_before_write(): void
    {
        $fixture = $this->jointFixture();

        $this->actingAs($fixture['anchorUser'])
            ->post(route('academic.attendance.finalize', $fixture['session']), [
                'participants' => [
                    $fixture['anchorParticipant']->id => ['attendance_status' => 'PRESENT'],
                    $fixture['secondaryParticipant']->id => ['attendance_status' => 'ABSENT'],
                ],
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('student_attendance', 0);
        $this->assertSame('PLANNED', $fixture['session']->fresh()->session_status);
    }

    public function test_wali_http_finalization_rejects_stale_attendance_version_without_partial_write(): void
    {
        $fixture = $this->jointFixture();
        $attendance = StudentAttendance::create([
            'session_student_participant_id' => $fixture['anchorParticipant']->id,
            'attendance_status' => 'PRESENT',
            'entered_by' => $fixture['anchorUser']->id,
            'entered_at' => now(),
            'updated_by' => $fixture['anchorUser']->id,
            'updated_at' => now(),
        ]);
        $attendance->update(['version_no' => 2]);

        $this->actingAs($fixture['anchorUser'])
            ->post(route('academic.attendance.finalize', $fixture['session']), [
                'participants' => [$fixture['anchorParticipant']->id => ['attendance_status' => 'PRESENT']],
                'attendance_versions' => [$fixture['anchorParticipant']->id => 1],
            ])
            ->assertRedirect(route('academic.attendance.show', $fixture['session']))
            ->assertSessionHasErrors(['finalize' => 'Data kehadiran telah berubah. Muat ulang halaman dan periksa kembali sebelum mengesahkan.']);

        $this->assertDatabaseHas('student_attendance', [
            'session_student_participant_id' => $fixture['anchorParticipant']->id,
            'version_no' => 2,
            'workflow_status' => 'DRAFT',
        ]);
        $this->assertSame('PLANNED', $fixture['session']->fresh()->session_status);
    }

    private function finalizeDraft(SessionStudentParticipant $participant, User $user, string $status): void
    {
        StudentAttendance::create([
            'session_student_participant_id' => $participant->id,
            'attendance_status' => $status,
            'entered_by' => $user->id,
            'entered_at' => now(),
            'updated_by' => $user->id,
            'updated_at' => now(),
        ]);
    }

    private function jointFixture(): array
    {
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-R2BC', 'unit_name' => 'R2B-C Unit', 'unit_type' => 'SCHOOL']);
        $grade = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '2', 'display_name' => 'Tingkat 2', 'sequence_no' => 2]);
        $year = AcademicYear::create(['year_code' => '2026-R2BC', 'display_name' => '2026/2027', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $semester = Semester::create(['academic_year_id' => $year->id, 'semester_code' => 'ODD', 'display_name' => 'Semester Ganjil', 'sequence_no' => 1, 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-31']);
        $anchor = AcademicClass::create(['class_code' => 'R2BC-A', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id, 'section_code' => 'A', 'display_name' => 'R2B-C A']);
        $secondary = AcademicClass::create(['class_code' => 'R2BC-B', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id, 'section_code' => 'B', 'display_name' => 'R2B-C B']);
        $subject = Subject::create(['subject_code' => 'R2BC-SUBJECT', 'subject_name' => 'R2B-C Subject']);
        $teacher = Staff::create(['staff_code' => 'R2BC-TEACHER', 'full_name' => 'R2B-C Teacher']);
        $anchorStaff = Staff::create(['staff_code' => 'R2BC-WALI-A', 'full_name' => 'R2B-C Wali A']);
        $secondaryStaff = Staff::create(['staff_code' => 'R2BC-WALI-B', 'full_name' => 'R2B-C Wali B']);
        $anchorUser = User::factory()->create();
        $secondaryUser = User::factory()->create();
        $role = Role::create(['code' => 'WALI_KELAS', 'name' => 'Wali Kelas']);
        foreach ([[$anchorUser, $anchorStaff], [$secondaryUser, $secondaryStaff]] as [$user, $staff]) {
            UserRoleAssignment::create(['user_id' => $user->id, 'role_id' => $role->id, 'effective_from' => '2026-07-01']);
            UserStaffLink::create(['user_id' => $user->id, 'staff_id' => $staff->id, 'effective_from' => '2026-07-01']);
        }
        ClassHomeroomAssignment::create(['class_id' => $anchor->id, 'staff_id' => $anchorStaff->id, 'effective_from' => '2026-07-01']);
        ClassHomeroomAssignment::create(['class_id' => $secondary->id, 'staff_id' => $secondaryStaff->id, 'effective_from' => '2026-07-01']);
        $assignment = TeachingAssignment::create(['assignment_code' => 'R2BC-ASSIGNMENT', 'semester_id' => $semester->id, 'class_id' => $anchor->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $teacher->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);
        $session = ClassSession::create(['session_code' => 'R2BC-SESSION', 'teaching_assignment_id' => $assignment->id, 'class_id' => $anchor->id, 'subject_id' => $subject->id, 'planned_start_at' => '2026-07-06 08:00:00', 'planned_end_at' => '2026-07-06 09:30:00', 'session_source' => 'SCHEDULED', 'participant_scope' => 'FULL_CLASS', 'session_status' => 'PLANNED']);
        ClassSessionGroup::create(['class_session_id' => $session->id, 'class_id' => $anchor->id, 'scope_role' => 'JOINT_SCOPE']);
        ClassSessionGroup::create(['class_session_id' => $session->id, 'class_id' => $secondary->id, 'scope_role' => 'JOINT_SCOPE']);
        SessionTeacherParticipation::create(['class_session_id' => $session->id, 'teacher_staff_id' => $teacher->id, 'role' => 'PRIMARY', 'obligation_type' => 'TEACHING_ASSIGNMENT', 'attendance_status' => 'PRESENT']);
        $anchorStudent = Student::create(['student_code' => 'R2BC-STUDENT-A', 'full_name' => 'R2B-C Student A']);
        $secondaryStudent = Student::create(['student_code' => 'R2BC-STUDENT-B', 'full_name' => 'R2B-C Student B']);
        StudentClassEnrollment::create(['student_id' => $anchorStudent->id, 'class_id' => $anchor->id, 'effective_from' => '2026-07-01', 'status' => 'ACTIVE']);
        StudentClassEnrollment::create(['student_id' => $secondaryStudent->id, 'class_id' => $secondary->id, 'effective_from' => '2026-07-01', 'status' => 'ACTIVE']);
        $anchorParticipant = SessionStudentParticipant::create(['class_session_id' => $session->id, 'student_id' => $anchorStudent->id, 'participant_basis' => 'CLASS_ENROLLMENT']);
        $secondaryParticipant = SessionStudentParticipant::create(['class_session_id' => $session->id, 'student_id' => $secondaryStudent->id, 'participant_basis' => 'CLASS_ENROLLMENT']);

        return compact('session', 'anchorParticipant', 'secondaryParticipant', 'anchorUser', 'secondaryUser', 'anchorStaff', 'secondaryStaff');
    }
}
