<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\GradeLevel;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\SessionStudentParticipant;
use App\Domains\Academic\Models\StudentClassEnrollment;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Models\TeachingAssignment;
use App\Models\User;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use App\Shared\Core\Models\Staff;
use App\Shared\Core\Models\Student;
use App\Shared\Platform\Authorization\Models\Role;
use App\Shared\Platform\Authorization\Models\UserRoleAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceExceptionUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_academic_can_view_missing_attendance_read_only(): void
    {
        [$session, $admin] = $this->fixtures('WAKA_AKADEMIK');

        $this->actingAs($admin)->get(route('academic.attendance.exceptions'))
            ->assertOk()
            ->assertSee('Perlu Perhatian Kehadiran')
            ->assertSee('Sesi Kelas Exception')
            ->assertDontSee($session->session_code)
            ->assertSee('belum dibuat')
            ->assertSee(route('academic.attendance.show', $session), false)
            ->assertSee('Isi Kehadiran');
    }

    public function test_non_admin_cannot_view_attendance_exceptions(): void
    {
        [, $user] = $this->fixtures('GURU');

        $this->actingAs($user)->get(route('academic.attendance.exceptions'))->assertForbidden();
    }

    public function test_waka_akademik_can_view_attendance_exceptions(): void
    {
        [$session, $waka] = $this->fixtures('WAKA_AKADEMIK');

        $this->actingAs($waka)->get(route('academic.attendance.exceptions'))
            ->assertOk()
            ->assertSee('Perlu Perhatian Kehadiran')
            ->assertSee('Sesi Kelas Exception')
            ->assertDontSee($session->session_code)
            ->assertSee(route('academic.attendance.show', $session), false);
    }

    public function test_session_without_roster_is_visible_as_an_exception(): void
    {
        [$session, $waka] = $this->fixtures('WAKA_AKADEMIK');
        $session->studentParticipants()->delete();

        $this->actingAs($waka)->get(route('academic.attendance.exceptions'))
            ->assertOk()
            ->assertSee('Roster santri belum dibuat');
    }

    public function test_waka_can_create_a_session_roster_snapshot(): void
    {
        [$session, $waka] = $this->fixtures('WAKA_AKADEMIK');

        $this->actingAs($waka)->post(route('academic.attendance.snapshot-participants', $session))
            ->assertRedirect(route('academic.attendance.show', $session));

        $this->assertSame(1, $session->studentParticipants()->count());
    }

    public function test_waka_can_bulk_create_rosters_for_empty_sessions(): void
    {
        [$session, $waka] = $this->fixtures('WAKA_AKADEMIK');
        $session->studentParticipants()->delete();
        $student = Student::firstOrFail();
        StudentClassEnrollment::create(['student_id' => $student->id, 'class_id' => $session->class_id, 'effective_from' => '2026-07-01']);

        $this->actingAs($waka)->post(route('academic.attendance.exceptions.bulk-snapshot'), [
            'session_ids' => [$session->id], 'list_month' => '2026-07', 'list_class_id' => $session->class_id, 'list_sort' => 'newest',
        ])->assertRedirect(route('academic.attendance.exceptions', ['list_month' => '2026-07', 'list_class_id' => $session->class_id, 'list_sort' => 'newest']))
            ->assertSessionHas('status', '1 roster sesi diproses. 1 peserta berhasil dibuat.');

        $this->assertSame(1, $session->studentParticipants()->count());
    }

    public function test_waka_can_preview_and_bulk_cancel_planned_sessions_by_class_and_date(): void
    {
        [$session, $waka] = $this->fixtures('WAKA_AKADEMIK');

        $this->actingAs($waka)->get(route('academic.attendance.exceptions', [
            'class_id' => $session->class_id,
            'from' => '2026-07-01',
            'to' => '2026-07-06',
        ]))
            ->assertOk()
            ->assertSee('1 sesi')
            ->assertSee('memenuhi syarat');

        $this->actingAs($waka)->get(route('academic.attendance.exceptions', [
            'list_class_id' => $session->class_id,
            'list_month' => '2026-07',
            'list_sort' => 'oldest',
        ]))
            ->assertOk()
            ->assertSee('Fokus daftar temuan')
            ->assertSee('Senin')
            ->assertSee('Sesi Kelas Exception');

        $this->actingAs($waka)->post(route('academic.attendance.exceptions.bulk-cancel'), [
            'class_id' => $session->class_id,
            'from' => '2026-07-01',
            'to' => '2026-07-06',
            'reason' => 'KBM baru dimulai 7 Juli 2026',
        ])->assertRedirect();

        $this->assertDatabaseHas('class_sessions', ['id' => $session->id, 'session_status' => 'CANCELLED']);
        $this->assertDatabaseHas('schedule_changes', [
            'source_session_id' => $session->id,
            'change_type' => 'CANCELLATION',
            'status' => 'APPLIED',
        ]);
    }

    public function test_bulk_cancel_form_prefills_month_and_class_from_exception_focus_filters(): void
    {
        [$session, $waka] = $this->fixtures('WAKA_AKADEMIK');

        $this->actingAs($waka)->get(route('academic.attendance.exceptions', [
            'list_class_id' => $session->class_id,
            'list_month' => '2026-07',
            'list_sort' => 'newest',
        ]))
            ->assertOk()
            ->assertSee('value="2026-07-01"', false)
            ->assertSee('value="2026-07-31"', false)
            ->assertSee('value="'.$session->class_id.'" selected', false)
            ->assertSee('action="'.route('academic.attendance.exceptions').'#bulk-cancel"', false);
    }

    private function fixtures(string $roleCode): array
    {
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-AEX', 'unit_name' => 'Exception Unit', 'unit_type' => 'SCHOOL']);
        $grade = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '1', 'display_name' => 'Tingkat 1', 'sequence_no' => 1]);
        $year = AcademicYear::create(['year_code' => '2026-AEX', 'display_name' => '2026/2027', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $semester = Semester::create(['academic_year_id' => $year->id, 'semester_code' => 'ODD', 'display_name' => 'Ganjil', 'sequence_no' => 1, 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-31']);
        $class = AcademicClass::create(['class_code' => 'CLASS-AEX-A', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id, 'section_code' => 'A', 'display_name' => 'Kelas Exception']);
        $subject = Subject::create(['subject_code' => 'SUBJ-AEX', 'subject_name' => 'Exception']);
        $teacher = Staff::create(['staff_code' => 'STAFF-AEX', 'full_name' => 'Teacher']);
        $user = User::factory()->create();
        $role = Role::create(['code' => $roleCode, 'name' => $roleCode === 'WAKA_AKADEMIK' ? 'Waka Akademik' : 'Guru']);
        UserRoleAssignment::create(['user_id' => $user->id, 'role_id' => $role->id, 'effective_from' => '2026-07-01']);
        $assignment = TeachingAssignment::create(['assignment_code' => 'TA-AEX', 'semester_id' => $semester->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $teacher->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);
        $session = ClassSession::create(['session_code' => 'SESSION-AEX', 'teaching_assignment_id' => $assignment->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'planned_start_at' => '2026-07-06 08:00:00', 'planned_end_at' => '2026-07-06 09:30:00', 'session_source' => 'SCHEDULED', 'participant_scope' => 'FULL_CLASS', 'session_status' => 'PLANNED']);
        SessionStudentParticipant::create(['class_session_id' => $session->id, 'student_id' => Student::create(['student_code' => 'STU-AEX', 'full_name' => 'Exception Student'])->id, 'participant_basis' => 'CLASS_ENROLLMENT']);

        return [$session, $user];
    }
}
