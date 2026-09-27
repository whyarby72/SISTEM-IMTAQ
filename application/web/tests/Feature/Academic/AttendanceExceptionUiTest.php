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
            ->assertSee('Kelas Exception')
            ->assertDontSee($session->session_code)
            ->assertSee('belum dibuat')
            ->assertSee(route('academic.attendance.show', $session), false)
            ->assertSee('Isi kehadiran →');
    }

    public function test_exception_queue_uses_compact_four_column_semantics_and_mobile_hooks(): void
    {
        [$session, $waka] = $this->fixtures('WAKA_AKADEMIK');

        $this->actingAs($waka)->get(route('academic.attendance.exceptions'))
            ->assertOk()
            ->assertSee('<table class="exception-queue">', false)
            ->assertSee('<th>Sesi</th><th>Waktu</th><th>Temuan</th><th>Aksi</th>', false)
            ->assertDontSee('<th>Kelas</th>', false)
            ->assertSee('Kelas Exception')
            ->assertSee('Dijadwalkan')
            ->assertSee('1 belum diisi')
            ->assertDontSee('1 belum dibuat')
            ->assertSee('Isi kehadiran →')
            ->assertSee('data-label="Sesi"', false)
            ->assertSee('data-label="Aksi"', false)
            ->assertDontSee('Pada layar kecil, geser tabel ke kiri/kanan');
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
            ->assertSee('Kelas Exception')
            ->assertDontSee($session->session_code)
            ->assertSee(route('academic.attendance.show', $session), false);
    }

    public function test_exception_page_shows_filtered_session_summary_and_precise_hierarchy(): void
    {
        [$session, $waka] = $this->fixtures('WAKA_AKADEMIK');

        $this->actingAs($waka)->get(route('academic.attendance.exceptions', [
            'list_class_id' => $session->class_id,
            'list_month' => '2026-07',
            'list_sort' => 'oldest',
        ]))
            ->assertOk()
            ->assertSee('Kontrol kualitas data')
            ->assertSee('Perlu Perhatian Kehadiran')
            ->assertSee('Sesi dengan roster atau data kehadiran yang masih perlu dituntaskan.')
            ->assertSee('data-exception-summary="total" data-count="1"', false)
            ->assertSee('data-exception-summary="missing-attendance" data-count="1"', false)
            ->assertSee('data-exception-summary="missing-roster" data-count="0"', false)
            ->assertSee('data-exception-summary="unresolved-attendance" data-count="0"', false)
            ->assertSee('Juli 2026')
            ->assertSee('Kelas Exception')
            ->assertSee('Terlama dahulu')
            ->assertSee('1 sesi ditemukan')
            ->assertSee('name="list_month"', false)
            ->assertSee('name="list_class_id"', false)
            ->assertSee('name="list_sort"', false)
            ->assertSee('Reset filter')
            ->assertSee('href="'.route('academic.attendance.exceptions').'"', false)
            ->assertDontSee('Daftar sesi yang belum lengkap atau belum diperiksa.');
    }

    public function test_joint_session_is_visible_once_and_matches_each_scoped_class_in_exception_queue(): void
    {
        [$session, $waka] = $this->fixtures('WAKA_AKADEMIK');
        $anchor = $session->academicClass;
        $secondary = $anchor->replicate();
        $secondary->class_code = 'CLASS-AEX-B';
        $secondary->section_code = 'B';
        $secondary->display_name = 'Kelas 3B';
        $secondary->save();
        $session->scopeGroups()->createMany([
            ['class_id' => $anchor->id, 'scope_role' => 'JOINT_SCOPE'],
            ['class_id' => $secondary->id, 'scope_role' => 'JOINT_SCOPE'],
        ]);
        $unrelated = $anchor->replicate();
        $unrelated->class_code = 'CLASS-AEX-C';
        $unrelated->section_code = 'C';
        $unrelated->display_name = 'Kelas Tidak Terkait';
        $unrelated->save();

        $response = $this->actingAs($waka)->get(route('academic.attendance.exceptions'));
        $response->assertOk()->assertSee('Kelas 3B + Kelas Exception')->assertSee('Kelas gabungan');
        $this->assertSame(1, substr_count($response->getContent(), 'Kelas 3B + Kelas Exception'));

        foreach ([$anchor, $secondary] as $class) {
            $this->actingAs($waka)->get(route('academic.attendance.exceptions', [
                'list_class_id' => $class->id, 'list_month' => '2026-07', 'list_sort' => 'newest',
            ]))->assertOk()->assertSee('Kelas 3B + Kelas Exception')->assertSee('1 sesi ditemukan')
                ->assertSee(route('academic.attendance.show', $session), false);
        }

        $this->actingAs($waka)->get(route('academic.attendance.exceptions', [
            'list_class_id' => $unrelated->id, 'list_month' => '2026-07', 'list_sort' => 'newest',
        ]))->assertOk()->assertSee('0 sesi ditemukan')->assertDontSee('Kelas 3B + Kelas Exception');

        $this->actingAs($waka)->get(route('academic.attendance.exceptions', [
            'class_id' => $secondary->id, 'from' => '2026-07-01', 'to' => '2026-07-06',
        ]))->assertOk()->assertSee('1 sesi memenuhi syarat pembatalan')->assertSee('Kelas 3B + Kelas Exception');
    }

    public function test_exception_page_renders_action_feedback_and_validation_errors(): void
    {
        [, $waka] = $this->fixtures('WAKA_AKADEMIK');

        $this->actingAs($waka)->withSession(['status' => '1 sesi berhasil dibatalkan.'])
            ->get(route('academic.attendance.exceptions'))
            ->assertSee('1 sesi berhasil dibatalkan.')
            ->assertSee('role="status"', false);

        $this->actingAs($waka)->from(route('academic.attendance.exceptions'))->followingRedirects()
            ->post(route('academic.attendance.exceptions.bulk-cancel'))
            ->assertSee('Periksa kembali input berikut:')
            ->assertSee('role="alert"', false)
            ->assertSee('id="bulk-cancel" class="bulk-action-workspace bulk-cancel"', false)
            ->assertSee('open', false);
    }

    public function test_authorized_mass_action_workspace_is_collapsed_and_has_one_canonical_cancel_form(): void
    {
        [$session, $waka] = $this->fixtures('WAKA_AKADEMIK');

        $response = $this->actingAs($waka)->get(route('academic.attendance.exceptions'));

        $response->assertOk()
            ->assertSee('Aksi massal')
            ->assertSee('Kelola tindakan terhadap beberapa sesi')
            ->assertSee('<details id="bulk-cancel"', false)
            ->assertSee('<summary>', false)
            ->assertSee('Bulan<input', false)
            ->assertSee('Urutan<select', false)
            ->assertSee('Mulai <span', false)
            ->assertSee('Sampai <span', false)
            ->assertSee('name="class_id"', false)
            ->assertSee('name="from"', false)
            ->assertSee('name="to"', false)
            ->assertDontSee('id="bulk-cancel" class="bulk-action-workspace" open', false);

        $this->assertSame(1, substr_count($response->getContent(), 'id="bulk-cancel"'));
    }

    public function test_bulk_preview_shows_existing_candidates_and_opens_workspace(): void
    {
        [$session, $waka] = $this->fixtures('WAKA_AKADEMIK');

        $this->actingAs($waka)->get(route('academic.attendance.exceptions', [
            'class_id' => $session->class_id,
            'from' => '2026-07-01',
            'to' => '2026-07-06',
        ]))
            ->assertOk()
            ->assertSee('1 sesi memenuhi syarat pembatalan')
            ->assertSee('Hanya sesi yang masih memenuhi aturan sistem yang akan dibatalkan.')
            ->assertSee('Lihat 1 sesi')
            ->assertSee('Kelas Exception')
            ->assertSee('06 Jul 2026 · 08:00')
            ->assertSee('id="bulk-cancel" class="bulk-action-workspace bulk-cancel"', false)
            ->assertSee('open', false)
            ->assertSee('Alasan pembatalan (wajib diisi)')
            ->assertSee('Batalkan 1 sesi');
    }

    public function test_bulk_preview_with_no_candidates_is_neutral_and_has_no_destructive_submit(): void
    {
        [, $waka] = $this->fixtures('WAKA_AKADEMIK');

        $response = $this->actingAs($waka)->get(route('academic.attendance.exceptions', [
            'from' => '2027-01-01',
            'to' => '2027-01-02',
        ]));

        $response->assertOk()
            ->assertSee('Tidak ada sesi yang aman dibatalkan untuk rentang yang dipilih.')
            ->assertDontSee('class="bulk-submit"', false)
            ->assertSee('id="bulk-cancel" class="bulk-action-workspace bulk-cancel"', false)
            ->assertSee('open', false);
    }

    public function test_session_without_roster_is_visible_as_an_exception(): void
    {
        [$session, $waka] = $this->fixtures('WAKA_AKADEMIK');
        $session->studentParticipants()->delete();

        $this->actingAs($waka)->get(route('academic.attendance.exceptions'))
            ->assertOk()
            ->assertSee('Roster santri belum dibuat')
            ->assertSee('Buat roster');
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
            ->assertSee('Filter temuan')
            ->assertSee('Senin')
            ->assertSee('Kelas Exception');

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
