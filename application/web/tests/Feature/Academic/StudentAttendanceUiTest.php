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
use App\Domains\Academic\Models\SessionTeacherParticipation;
use App\Domains\Academic\Models\StudentAttendance;
use App\Domains\Academic\Models\StudentClassEnrollment;
use App\Domains\Academic\Models\StudentSessionGroomingNote;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Models\TeachingAssignment;
use App\Domains\Academic\Services\SessionAttendanceScopeResolver;
use App\Domains\Academic\Services\SessionOccurrenceAuthorizationService;
use App\Models\User;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use App\Shared\Core\Models\Staff;
use App\Shared\Core\Models\Student;
use App\Shared\Platform\Audit\Models\AuditLog;
use App\Shared\Platform\Authorization\Models\Role;
use App\Shared\Platform\Authorization\Models\UserRoleAssignment;
use App\Shared\Platform\Authorization\Models\UserStaffLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class StudentAttendanceUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_bulk_present_is_blank_only_non_submitting_and_double_submit_guarded(): void
    {
        $source = file_get_contents(resource_path('views/academic/attendance/show.blade.php'));

        $this->assertStringContainsString('@disabled(! ($canSaveDraft ?? false))>Tandai semua hadir', $source);
        $this->assertStringContainsString("document.querySelectorAll('[data-attendance-select]:not(:disabled)')", $source);
        $this->assertStringContainsString('const blankSelects = selects.filter((select) => !select.value);', $source);
        $this->assertStringContainsString('Status kosong ditandai Hadir. Periksa kembali sebelum menyimpan.', $source);
        $this->assertStringNotContainsString('form.submit()', $source);
        $this->assertStringContainsString('let attendanceSubmissionPending = false;', $source);
        $this->assertStringContainsString("submitter.textContent = isFinalize ? 'Mengesahkan...' : 'Menyimpan...'", $source);
        $this->assertStringContainsString("submitter.setAttribute('aria-disabled', 'true')", $source);
    }

    public function test_finalized_attendance_feedback_provides_dashboard_return_and_preserves_stale_recovery(): void
    {
        $source = file_get_contents(resource_path('views/academic/attendance/show.blade.php'));
        $controller = file_get_contents(app_path('Http/Controllers/Academic/StudentAttendanceController.php'));

        $this->assertStringContainsString('Kembali ke Dashboard Akademik', $source);
        $this->assertStringContainsString('Data kehadiran telah berubah. Muat ulang halaman dan periksa kembali sebelum mengesahkan.', $controller);
        $this->assertStringNotContainsString('window.location', $source);
    }

    public function test_canonical_occurrence_write_route_is_disabled_by_default(): void
    {
        [$session, , $user] = $this->fixtures();

        config(['academic.session_occurrence_enabled' => false]);

        $this->actingAs($user)
            ->post(route('academic.attendance.occurrence.record', $session), ['action' => 'HELD'])
            ->assertForbidden();

        $this->assertDatabaseCount('session_occurrence_versions', 0);
    }

    public function test_wali_can_record_routine_occurrence_but_not_cancel_joint_session(): void
    {
        [$session, , $user] = $this->fixtures();
        $secondary = $session->academicClass->replicate();
        $secondary->class_code = 'CLASS-UI-JOINT';
        $secondary->section_code = 'J';
        $secondary->display_name = 'Kelas Joint';
        $secondary->save();
        ClassSessionGroup::create(['class_session_id' => $session->id, 'class_id' => $session->class_id, 'scope_role' => 'JOINT_SCOPE']);
        ClassSessionGroup::create(['class_session_id' => $session->id, 'class_id' => $secondary->id, 'scope_role' => 'JOINT_SCOPE']);

        $authorization = app(SessionOccurrenceAuthorizationService::class);

        $this->assertTrue($authorization->canManageRoutine($user, $session, 'HELD'));
        $this->assertTrue($authorization->canManageRoutine($user, $session, 'PARTIAL_HELD'));
        $this->assertFalse($authorization->canManageRoutine($user, $session, 'CANCELLED'));
        $this->assertFalse($authorization->canManageRoutine($user, $session, 'RESCHEDULED'));

        config([
            'academic.session_occurrence_enabled' => true,
            'academic.session_occurrence_cutover_at' => '2026-06-01T00:00:00+07:00',
        ]);
        $this->actingAs($user)
            ->post(route('academic.attendance.occurrence.record', $session), [
                'action' => 'CANCELLED',
                'reason' => 'Tidak jadi',
            ])
            ->assertForbidden();
        $this->actingAs($user)
            ->post(route('academic.attendance.occurrence.record', $session), [
                'action' => 'RESCHEDULED',
                'reason' => 'Pindah jadwal',
                'new_start_at' => '2026-07-06T10:00',
                'new_end_at' => '2026-07-06T11:30',
            ])
            ->assertForbidden();
        $waka = User::factory()->create();
        $wakaRole = Role::create(['code' => 'WAKA_AKADEMIK', 'name' => 'Waka Akademik']);
        UserRoleAssignment::create(['user_id' => $waka->id, 'role_id' => $wakaRole->id, 'effective_from' => '2026-07-01']);
        $this->assertTrue($authorization->canManageRoutine($waka, $session, 'CANCELLED'));
        $this->assertTrue($authorization->canManageRoutine($waka, $session, 'RESCHEDULED'));
    }

    public function test_future_canonical_session_hides_routine_actions_and_preserves_waka_physical_actions(): void
    {
        [$session, , $wali] = $this->fixtures();
        $start = Carbon::parse('2026-10-05 10:00:00', 'Asia/Jakarta');
        $session->update([
            'planned_start_at' => $start->copy()->utc(),
            'planned_end_at' => $start->copy()->addHour()->utc(),
        ]);
        config([
            'academic.session_occurrence_enabled' => true,
            'academic.session_occurrence_cutover_at' => '2026-06-01T00:00:00+07:00',
        ]);
        Carbon::setTestNow($start->copy()->subMinute());

        try {
            $this->actingAs($wali)->get(route('academic.attendance.show', $session))
                ->assertOk()
                ->assertSee('Sesi akan datang')
                ->assertDontSee('Pelaksanaan KBM belum dicatat')
                ->assertDontSee('Catat KBM berlangsung')
                ->assertDontSee('KBM berlangsung sebagian')
                ->assertDontSee('Catat pelaksanaan');

            foreach (['HELD', 'PARTIAL_HELD'] as $action) {
                $this->actingAs($wali)->from(route('academic.attendance.show', $session))
                    ->post(route('academic.attendance.occurrence.record', $session), [
                        'action' => $action,
                        'partial_reason' => $action === 'PARTIAL_HELD' ? 'Kegiatan terbatas' : null,
                    ])
                    ->assertRedirect(route('academic.attendance.show', $session))
                    ->assertSessionHasErrors('occurrence');
            }

            $waka = User::factory()->create();
            $wakaRole = Role::create(['code' => 'WAKA_AKADEMIK', 'name' => 'Waka Akademik']);
            UserRoleAssignment::create(['user_id' => $waka->id, 'role_id' => $wakaRole->id, 'effective_from' => '2026-07-01']);

            $this->actingAs($waka)->get(route('academic.attendance.show', $session))
                ->assertOk()
                ->assertSee('Sesi akan datang')
                ->assertDontSee('Catat KBM berlangsung')
                ->assertSee('Batalkan KBM')
                ->assertSee('Jadwal ulang');

            $this->actingAs($waka)->from(route('academic.attendance.show', $session))
                ->post(route('academic.attendance.occurrence.record', $session), ['action' => 'HELD'])
                ->assertRedirect(route('academic.attendance.show', $session))
                ->assertSessionHasErrors('occurrence');
        } finally {
            Carbon::setTestNow();
        }

        $this->assertDatabaseCount('session_occurrence_versions', 0);
        $this->assertDatabaseCount('session_student_participants', 1);
    }

    public function test_wali_kelas_can_view_and_save_attendance_from_responsive_page(): void
    {
        [$session, $participant, $user] = $this->fixtures();

        $this->actingAs($user)->get(route('academic.attendance.show', $session))
            ->assertOk()
            ->assertSee('Kehadiran Santri')
            ->assertSee('Kelas 1 A')
            ->assertSee('Sesi Kehadiran')
            ->assertSee('Senin, 06 Jul 2026')
            ->assertSee('08:00–09:30')
            ->assertSee('Guru terjadwal')
            ->assertSee('UI Subject')
            ->assertSee('Teacher')
            ->assertSee('Dicatat oleh')
            ->assertSee('Dijadwalkan')
            ->assertSee(htmlspecialchars(route('academic.attendance.exceptions', ['list_month' => '2026-07', 'list_class_id' => $session->class_id, 'list_sort' => 'newest'])), false)
            ->assertDontSee($session->session_code)
            ->assertSee('Tandai semua hadir')
            ->assertDontSee('Tandai semua tertib')
            ->assertSee('Ketertiban')
            ->assertSee('Belum dibuat')
            ->assertSee('Simpan draf')
            ->assertSee('Cari nama santri...', false)
            ->assertSee('for="student-search"', false)
            ->assertSee('data-student-search', false)
            ->assertSee('data-student-row', false)
            ->assertSee('data-student-name="UI Student"', false)
            ->assertSee('data-student-empty-state', false)
            ->assertSee('name="participants[', false)
            ->assertSee('data-label="Catatan"', false)
            ->assertSee('data-label="Ketertiban"', false)
            ->assertSee('data-attendance-action-bar', false)
            ->assertSee('top-attendance-actions', false)
            ->assertSee('top-attendance-utility', false)
            ->assertSee('data-attendance-progress', false)
            ->assertSee('data-attendance-meter', false)
            ->assertSee('data-attendance-percent', false)
            ->assertSee('data-finalize-button', false)
            ->assertSee('aria-disabled="true"', false)
            ->assertDontSee('@if')
            ->assertDontSee('@endif')
            ->assertDontSee('{{')
            ->assertDontSee('$scopeIsLocked');

        Staff::create(['staff_code' => 'PILOT-AZHAR', 'full_name' => 'Azhar']);
        Staff::create(['staff_code' => 'PILOT-GURU-3A', 'full_name' => 'Guru Sample 3A']);
        Staff::create(['staff_code' => 'PILOT-GURU-3B', 'full_name' => 'Guru Sample 3B']);

        $this->actingAs($user)->get(route('academic.attendance.show', $session))
            ->assertDontSee('Azhar')
            ->assertDontSee('Guru Sample 3A')
            ->assertDontSee('Guru Sample 3B');

        $this->actingAs($user)->post(route('academic.attendance.draft', $session), [
            'historical_session_ack' => '1',
            'participants' => [$participant->id => ['attendance_status' => 'PRESENT', 'notes' => 'Masuk di tengah sesi', 'discipline_code' => 'TIDAK_BERSERAGAM', 'grooming_note' => 'Seragam putih tidak dikenakan']],
        ])->assertRedirect(route('academic.attendance.show', $session));

        $this->assertSame('PRESENT', StudentAttendance::first()->attendance_status);
        $this->assertSame('TIDAK_BERSERAGAM', StudentSessionGroomingNote::first()->discipline_code);
        $this->assertSame('Seragam putih tidak dikenakan', StudentSessionGroomingNote::first()->note_text);
    }

    public function test_opening_attendance_flow_materializes_missing_snapshot_lazily(): void
    {
        [$session, $participant, $user] = $this->fixtures();
        $session->studentParticipants()->delete();
        StudentClassEnrollment::create(['student_id' => $participant->student_id, 'class_id' => $session->class_id, 'effective_from' => '2026-07-01', 'status' => 'ACTIVE']);

        $this->actingAs($user)->get(route('academic.attendance.show', $session))->assertOk();

        $this->assertSame(1, $session->studentParticipants()->count());
        $this->assertSame($participant->student_id, $session->studentParticipants()->first()->student_id);
    }

    public function test_wali_historical_attendance_write_requires_explicit_acknowledgement(): void
    {
        [$session, $participant, $user] = $this->fixtures();

        $this->actingAs($user)->from(route('academic.attendance.show', $session))
            ->post(route('academic.attendance.draft', $session), [
                'participants' => [$participant->id => ['attendance_status' => 'PRESENT']],
            ])
            ->assertRedirect(route('academic.attendance.show', $session))
            ->assertSessionHasErrors('historical_session_ack');

        $this->assertDatabaseCount('student_attendance', 0);
    }

    public function test_joint_session_shows_scope_identity_and_roster_breakdown(): void
    {
        [$session, $participant, $user] = $this->fixtures();
        $anchor = $session->academicClass;
        $secondary = $anchor->replicate();
        $secondary->class_code = 'CLASS-UI-B';
        $secondary->section_code = 'B';
        $secondary->display_name = 'Kelas 1 B';
        $secondary->save();
        $session->scopeGroups()->createMany([
            ['class_id' => $anchor->id, 'scope_role' => 'JOINT_SCOPE'],
            ['class_id' => $secondary->id, 'scope_role' => 'JOINT_SCOPE'],
        ]);
        StudentClassEnrollment::create(['student_id' => $participant->student_id, 'class_id' => $anchor->id, 'effective_from' => '2026-07-01', 'status' => 'ACTIVE']);
        $secondaryStudent = Student::create(['student_code' => 'STU-UI-002', 'full_name' => 'Joint UI Student']);
        SessionStudentParticipant::create(['class_session_id' => $session->id, 'student_id' => $secondaryStudent->id, 'participant_basis' => 'CLASS_ENROLLMENT']);
        StudentClassEnrollment::create(['student_id' => $secondaryStudent->id, 'class_id' => $secondary->id, 'effective_from' => '2026-07-01', 'status' => 'ACTIVE']);

        $this->actingAs($user)->get(route('academic.attendance.show', $session))
            ->assertOk()
            ->assertSee('Kelas gabungan')
            ->assertSee('Kelas 1 A')
            ->assertDontSee('Kelas 1 A + Kelas 1 B')
            ->assertSee('← Kembali ke daftar sesi')
            ->assertDontSee('Kembali ke daftar sesi kelas ini')
            ->assertSee('Kelas 1 A · 1 santri')
            ->assertDontSee('Kelas 1 B · 1 santri')
            ->assertDontSee('Joint UI Student')
            ->assertSee('Total santri');
    }

    public function test_joint_session_is_partitioned_for_each_wali_and_full_authority_sees_one_full_session(): void
    {
        [$session, $anchorParticipant, $anchorUser] = $this->fixtures();
        $anchor = $session->academicClass;
        $secondary = $anchor->replicate();
        $secondary->class_code = 'CLASS-UI-PARTITION-B';
        $secondary->section_code = 'B';
        $secondary->display_name = 'Kelas Partition B';
        $secondary->save();
        $session->scopeGroups()->createMany([
            ['class_id' => $anchor->id, 'scope_role' => 'JOINT_SCOPE'],
            ['class_id' => $secondary->id, 'scope_role' => 'JOINT_SCOPE'],
        ]);
        StudentClassEnrollment::create(['student_id' => $anchorParticipant->student_id, 'class_id' => $anchor->id, 'effective_from' => '2026-07-01', 'status' => 'ACTIVE']);
        $secondaryStudent = Student::create(['student_code' => 'STU-UI-PARTITION-B', 'full_name' => 'Partition B Student']);
        $secondaryParticipant = SessionStudentParticipant::create(['class_session_id' => $session->id, 'student_id' => $secondaryStudent->id, 'participant_basis' => 'CLASS_ENROLLMENT']);
        StudentClassEnrollment::create(['student_id' => $secondaryStudent->id, 'class_id' => $secondary->id, 'effective_from' => '2026-07-01', 'status' => 'ACTIVE']);

        $secondaryStaff = Staff::create(['staff_code' => 'STAFF-UI-PARTITION-B', 'full_name' => 'Wali Partition B']);
        $secondaryUser = User::factory()->create();
        $waliRole = Role::where('code', 'WALI_KELAS')->firstOrFail();
        UserRoleAssignment::create(['user_id' => $secondaryUser->id, 'role_id' => $waliRole->id, 'effective_from' => '2026-07-01']);
        UserStaffLink::create(['user_id' => $secondaryUser->id, 'staff_id' => $secondaryStaff->id, 'effective_from' => '2026-07-01']);
        ClassHomeroomAssignment::create(['class_id' => $secondary->id, 'staff_id' => $secondaryStaff->id, 'effective_from' => '2026-07-01']);

        $waka = User::factory()->create();
        $wakaRole = Role::create(['code' => 'WAKA_AKADEMIK', 'name' => 'Waka Akademik']);
        UserRoleAssignment::create(['user_id' => $waka->id, 'role_id' => $wakaRole->id, 'effective_from' => '2026-07-01']);
        UserStaffLink::create(['user_id' => $waka->id, 'staff_id' => $session->teachingAssignment->teacher_staff_id, 'effective_from' => '2026-07-01']);

        $this->actingAs($anchorUser)->get(route('academic.attendance.show', $session))
            ->assertOk()
            ->assertSee('UI Student')
            ->assertDontSee('Partition B Student')
            ->assertDontSee('Kelas Partition B · 1 santri');
        $this->actingAs($secondaryUser)->get(route('academic.attendance.show', $session))
            ->assertOk()
            ->assertSee('Partition B Student')
            ->assertDontSee('UI Student')
            ->assertDontSee('Kelas 1 A · 1 santri');
        $this->actingAs($waka)->get(route('academic.attendance.show', $session))
            ->assertOk()
            ->assertSee('UI Student')
            ->assertSee('Partition B Student');

        $this->assertSame($secondaryParticipant->class_session_id, $session->id);
        $this->assertSame([(string) $secondary->id], app(SessionAttendanceScopeResolver::class)->resolve($secondaryUser, $session->fresh()->load('scopeGroups'), $session->studentParticipants()->with('student.classEnrollments')->get())['effective_class_ids']);
    }

    public function test_unrelated_wali_and_unmapped_joint_participant_fail_closed(): void
    {
        [$session, , $user] = $this->fixtures();
        $secondary = $session->academicClass->replicate();
        $secondary->class_code = 'CLASS-UI-FAIL-CLOSED';
        $secondary->section_code = 'F';
        $secondary->display_name = 'Kelas Fail Closed';
        $secondary->save();
        $session->scopeGroups()->createMany([
            ['class_id' => $session->class_id, 'scope_role' => 'JOINT_SCOPE'],
            ['class_id' => $secondary->id, 'scope_role' => 'JOINT_SCOPE'],
        ]);

        $unrelatedStaff = Staff::create(['staff_code' => 'STAFF-UI-UNRELATED', 'full_name' => 'Unrelated Wali']);
        $unrelated = User::factory()->create();
        $role = Role::where('code', 'WALI_KELAS')->firstOrFail();
        UserRoleAssignment::create(['user_id' => $unrelated->id, 'role_id' => $role->id, 'effective_from' => '2026-07-01']);
        UserStaffLink::create(['user_id' => $unrelated->id, 'staff_id' => $unrelatedStaff->id, 'effective_from' => '2026-07-01']);

        $this->actingAs($unrelated)->get(route('academic.attendance.show', $session))->assertForbidden();
        $this->actingAs($user)->get(route('academic.attendance.show', $session))->assertForbidden();
    }

    public function test_empty_new_rows_do_not_create_attendance_or_grooming_records(): void
    {
        [$session, $participant, $user] = $this->fixtures();

        $this->actingAs($user)->post(route('academic.attendance.draft', $session), [
            'historical_session_ack' => '1',
            'participants' => [$participant->id => ['attendance_status' => ' ', 'notes' => '  ', 'discipline_code' => null, 'grooming_note' => "\t"]],
        ])->assertRedirect(route('academic.attendance.show', $session));

        $this->assertDatabaseCount('student_attendance', 0);
        $this->assertDatabaseCount('student_session_grooming_notes', 0);
        $this->assertDatabaseMissing('audit_logs', ['action' => 'STUDENT_ATTENDANCE_DRAFT_SAVED']);
        $this->assertDatabaseMissing('audit_logs', ['action' => 'STUDENT_SESSION_GROOMING_NOTE_SAVED']);
    }

    public function test_new_participant_grooming_is_collapsed_with_exception_control(): void
    {
        [$session, , $user] = $this->fixtures();

        $this->actingAs($user)->get(route('academic.attendance.show', $session))
            ->assertOk()
            ->assertSee('<summary>Ketertiban</summary>', false)
            ->assertDontSee('<details class="grooming-details" open>', false);
    }

    public function test_existing_attendance_note_is_open_and_preserved(): void
    {
        [$session, $participant, $user] = $this->fixtures();
        StudentAttendance::create([
            'session_student_participant_id' => $participant->id,
            'attendance_status' => 'PRESENT',
            'workflow_status' => 'DRAFT',
            'version_no' => 1,
            'notes' => 'Datang setelah kegiatan dimulai',
            'entered_by' => $user->id,
            'entered_at' => now(),
            'updated_by' => $user->id,
            'updated_at' => now(),
        ]);

        $this->actingAs($user)->get(route('academic.attendance.show', $session))
            ->assertOk()
            ->assertSee('>Catatan</summary>', false)
            ->assertSee('Datang setelah kegiatan dimulai')
            ->assertSee('name="participants['.$participant->id.'][notes]"', false);
    }

    public function test_old_attendance_note_reopens_note_disclosure_after_validation_error(): void
    {
        [$session, $participant, $user] = $this->fixtures();

        $this->actingAs($user)->from(route('academic.attendance.show', $session))
            ->post(route('academic.attendance.draft', $session), [
                'historical_session_ack' => '1',
                'participants' => [$participant->id => ['attendance_status' => '', 'notes' => 'Catatan dari input gagal', 'discipline_code' => 'INVALID']],
            ])
            ->assertRedirect(route('academic.attendance.show', $session))
            ->assertSessionHasErrors('participants.'.$participant->id.'.discipline_code');

        $this->actingAs($user)->get(route('academic.attendance.show', $session))
            ->assertOk()
            ->assertSee('Catatan dari input gagal')
            ->assertSee('>Catatan</summary>', false);
    }

    public function test_existing_grooming_exception_is_open_and_preserved(): void
    {
        [$session, $participant, $user] = $this->fixtures();
        StudentSessionGroomingNote::create([
            'session_student_participant_id' => $participant->id,
            'discipline_code' => 'TIDAK_BERPECI',
            'note_text' => 'Peci belum digunakan',
            'created_by' => $user->id,
            'created_at' => now(),
            'updated_by' => $user->id,
            'updated_at' => now(),
        ]);

        $this->actingAs($user)->get(route('academic.attendance.show', $session))
            ->assertOk()
            ->assertSee('Edit catatan ketertiban')
            ->assertSee('<option value="TIDAK_BERPECI" selected>Tidak berpeci</option>', false)
            ->assertSee('Peci belum digunakan');
    }

    public function test_grooming_only_input_does_not_create_student_attendance(): void
    {
        [$session, $participant, $user] = $this->fixtures();

        $this->actingAs($user)->post(route('academic.attendance.draft', $session), [
            'historical_session_ack' => '1',
            'participants' => [$participant->id => ['attendance_status' => ' ', 'notes' => ' ', 'discipline_code' => 'TIDAK_BERPECI', 'grooming_note' => 'Peci belum digunakan']],
        ])->assertRedirect(route('academic.attendance.show', $session));

        $this->assertDatabaseCount('student_attendance', 0);
        $this->assertDatabaseHas('student_session_grooming_notes', [
            'session_student_participant_id' => $participant->id,
            'discipline_code' => 'TIDAK_BERPECI',
            'note_text' => 'Peci belum digunakan',
        ]);
    }

    public function test_attendance_and_grooming_inputs_are_saved_independently(): void
    {
        [$session, $participant, $user] = $this->fixtures();

        $this->actingAs($user)->post(route('academic.attendance.draft', $session), [
            'historical_session_ack' => '1',
            'participants' => [$participant->id => ['attendance_status' => 'PRESENT', 'notes' => null, 'discipline_code' => null, 'grooming_note' => ' ']],
        ])->assertRedirect(route('academic.attendance.show', $session));

        $this->assertDatabaseHas('student_attendance', ['session_student_participant_id' => $participant->id, 'attendance_status' => 'PRESENT']);
        $this->assertDatabaseCount('student_session_grooming_notes', 0);
    }

    public function test_finalize_with_empty_new_rows_does_not_create_drafts_before_validation(): void
    {
        [$session, $participant, $user] = $this->fixtures();

        $this->actingAs($user)->post(route('academic.attendance.finalize', $session), [
            'historical_session_ack' => '1',
            'participants' => [$participant->id => ['attendance_status' => ' ', 'notes' => ' ', 'discipline_code' => null, 'grooming_note' => ' ']],
        ])->assertRedirect(route('academic.attendance.show', $session))
            ->assertSessionHasErrors(['finalize' => 'Belum semua santri memiliki status kehadiran. Lengkapi status setiap santri terlebih dahulu.']);

        $this->assertDatabaseCount('student_attendance', 0);
        $this->assertDatabaseCount('student_session_grooming_notes', 0);
        $this->assertSame('PLANNED', $session->fresh()->session_status);
    }

    public function test_wali_can_record_teacher_sick_status_without_cancelling_student_session(): void
    {
        [$session, , $user] = $this->fixtures();
        $teacherParticipation = SessionTeacherParticipation::where('class_session_id', $session->id)->firstOrFail();

        $this->actingAs($user)->get(route('academic.attendance.show', $session))
            ->assertOk()
            ->assertSee('Rekap kehadiran guru')
            ->assertSee('Sakit');

        $this->actingAs($user)->post(route('academic.attendance.teacher-attendance', $session), [
            'historical_session_ack' => '1',
            'participation_id' => $teacherParticipation->id,
            'attendance_status' => 'SICK',
            'reason' => 'Surat sakit diterima',
        ])->assertRedirect(route('academic.attendance.show', $session).'#rekap-guru');

        $this->actingAs($user)->get(route('academic.attendance.show', $session))
            ->assertOk()
            ->assertSee('Kehadiran guru berhasil dicatat dan masuk rekap evaluasi.')
            ->assertSee('attendance-toast', false)
            ->assertSee('Tutup notifikasi');

        $this->assertDatabaseHas('session_teacher_participations', [
            'id' => $teacherParticipation->id,
            'attendance_status' => 'SICK',
            'reason' => 'Surat sakit diterima',
        ]);
        $this->assertSame('PLANNED', $session->fresh()->session_status);
    }

    public function test_unresolved_teacher_attendance_is_displayed_as_not_recorded(): void
    {
        [$session, , $user] = $this->fixtures();
        $primary = SessionTeacherParticipation::where('class_session_id', $session->id)->firstOrFail();
        $primary->update(['attendance_status' => null]);

        $this->actingAs($user)->get(route('academic.attendance.show', $session))
            ->assertOk()
            ->assertSee('<option value="" selected>Belum dicatat</option>', false)
            ->assertDontSee('<option value="PRESENT" selected>Hadir</option>', false);

        $primary->update(['attendance_status' => 'PRESENT']);
        $this->actingAs($user)->get(route('academic.attendance.show', $session))
            ->assertSee('<option value="PRESENT" selected>Hadir</option>', false);

        $primary->update(['attendance_status' => 'SICK']);
        $this->actingAs($user)->get(route('academic.attendance.show', $session))
            ->assertSee('<option value="SICK" selected>Sakit</option>', false);
        $this->assertSame('SICK', $primary->fresh()->attendance_status);
    }

    public function test_student_summary_shows_every_controlled_status_and_reconciles_to_total(): void
    {
        [$session, $participant, $user] = $this->fixtures();
        $statuses = ['PRESENT', 'LATE', 'SICK', 'IZIN', 'EXCUSED', 'ABSENT'];
        $participants = [$participant];

        foreach (array_slice($statuses, 1) as $index => $status) {
            $student = Student::create(['student_code' => 'STU-UI-SUMMARY-'.$index, 'full_name' => 'Summary Student '.$index]);
            $newParticipant = SessionStudentParticipant::create(['class_session_id' => $session->id, 'student_id' => $student->id, 'participant_basis' => 'CLASS_ENROLLMENT']);
            StudentAttendance::create(['session_student_participant_id' => $newParticipant->id, 'attendance_status' => $status, 'workflow_status' => 'DRAFT', 'version_no' => 1, 'entered_by' => $user->id, 'entered_at' => now(), 'updated_by' => $user->id, 'updated_at' => now()]);
            $participants[] = $newParticipant;
        }
        StudentAttendance::create(['session_student_participant_id' => $participant->id, 'attendance_status' => 'PRESENT', 'workflow_status' => 'DRAFT', 'version_no' => 1, 'entered_by' => $user->id, 'entered_at' => now(), 'updated_by' => $user->id, 'updated_at' => now()]);

        $response = $this->actingAs($user)->get(route('academic.attendance.show', $session))->assertOk();
        foreach (['Total santri', 'Hadir', 'Terlambat', 'Sakit', 'Izin', 'Dikecualikan', 'Tidak hadir', 'Belum diisi'] as $label) {
            $response->assertSee($label);
        }
        $response->assertSee('aria-label="Ringkasan kehadiran santri"', false)
            ->assertSee('summary-card--total', false)
            ->assertSee('summary-card--pending', false)
            ->assertSee('>6<', false)
            ->assertSee('>1<', false);
        $this->assertCount(6, $participants);
    }

    public function test_student_summary_counts_missing_record_and_null_status_as_not_filled(): void
    {
        [$session, $participant, $user] = $this->fixtures();
        StudentAttendance::create(['session_student_participant_id' => $participant->id, 'attendance_status' => null, 'workflow_status' => 'DRAFT', 'version_no' => 1, 'entered_by' => $user->id, 'entered_at' => now(), 'updated_by' => $user->id, 'updated_at' => now()]);

        $this->actingAs($user)->get(route('academic.attendance.show', $session))
            ->assertOk()
            ->assertSee('Belum diisi')
            ->assertSee('>1<', false);
    }

    public function test_unauthorized_get_cannot_create_primary_teacher_participation(): void
    {
        [$session] = $this->fixtures();
        SessionTeacherParticipation::where('class_session_id', $session->id)->delete();
        $unauthorized = User::factory()->create();

        $this->actingAs($unauthorized)->get(route('academic.attendance.show', $session))->assertForbidden();

        $this->assertSame(0, SessionTeacherParticipation::where('class_session_id', $session->id)->count());
    }

    public function test_authorized_waka_get_can_initialize_missing_primary_participation(): void
    {
        [$session] = $this->fixtures();
        SessionTeacherParticipation::where('class_session_id', $session->id)->delete();
        $waka = User::factory()->create();
        $wakaStaff = Staff::create(['staff_code' => 'STAFF-UI-ATTENDANCE-WAKA', 'full_name' => 'Waka Attendance']);
        $role = Role::create(['code' => 'WAKA_AKADEMIK', 'name' => 'Waka Akademik']);
        UserRoleAssignment::create(['user_id' => $waka->id, 'role_id' => $role->id, 'effective_from' => '2026-07-01']);
        UserStaffLink::create(['user_id' => $waka->id, 'staff_id' => $wakaStaff->id, 'effective_from' => '2026-07-01']);

        $this->actingAs($waka)->get(route('academic.attendance.show', $session))->assertOk();

        $this->assertSame(1, SessionTeacherParticipation::where('class_session_id', $session->id)->where('role', 'PRIMARY')->count());
    }

    public function test_repeated_authorized_get_does_not_duplicate_primary_participation(): void
    {
        [$session, , $user] = $this->fixtures();
        SessionTeacherParticipation::where('class_session_id', $session->id)->delete();

        $this->actingAs($user)->get(route('academic.attendance.show', $session))->assertOk();
        $this->actingAs($user)->get(route('academic.attendance.show', $session))->assertOk();

        $this->assertSame(1, SessionTeacherParticipation::where('class_session_id', $session->id)->where('role', 'PRIMARY')->count());
    }

    public function test_super_admin_can_open_attendance_session(): void
    {
        [$session] = $this->fixtures();
        $superAdmin = User::factory()->create();
        $staff = Staff::create(['staff_code' => 'STAFF-UI-ATTENDANCE-SUPER', 'full_name' => 'Super Admin']);
        $role = Role::create(['code' => 'SUPER_ADMIN', 'name' => 'Super Admin']);
        UserRoleAssignment::create(['user_id' => $superAdmin->id, 'role_id' => $role->id, 'effective_from' => '2026-07-01']);
        UserStaffLink::create(['user_id' => $superAdmin->id, 'staff_id' => $staff->id, 'effective_from' => '2026-07-01']);

        $this->actingAs($superAdmin)->get(route('academic.attendance.show', $session))->assertOk();
    }

    public function test_waka_can_record_teacher_attendance_for_any_class(): void
    {
        [$session] = $this->fixtures();
        $waka = User::factory()->create();
        $wakaStaff = Staff::create(['staff_code' => 'STAFF-UI-WAKA-TEACHER', 'full_name' => 'Waka Teacher Attendance']);
        $participation = SessionTeacherParticipation::where('class_session_id', $session->id)->firstOrFail();
        $role = Role::create(['code' => 'WAKA_AKADEMIK', 'name' => 'Waka Akademik']);
        UserRoleAssignment::create(['user_id' => $waka->id, 'role_id' => $role->id, 'effective_from' => '2026-07-01']);
        UserStaffLink::create(['user_id' => $waka->id, 'staff_id' => $wakaStaff->id, 'effective_from' => '2026-07-01']);

        $this->actingAs($waka)->post(route('academic.attendance.teacher-attendance', $session), [
            'participation_id' => $participation->id,
            'attendance_status' => 'ABSENT',
            'reason' => 'Guru berhalangan hadir',
        ])->assertRedirect(route('academic.attendance.show', $session).'#rekap-guru');

        $this->assertDatabaseHas('session_teacher_participations', ['id' => $participation->id, 'attendance_status' => 'ABSENT']);
    }

    public function test_waka_akademik_can_fill_and_finalize_attendance_for_any_class(): void
    {
        [$session, $participant] = $this->fixtures();
        $waka = User::factory()->create();
        $wakaStaff = Staff::create(['staff_code' => 'STAFF-UI-WAKA', 'full_name' => 'Waka Akademik']);
        $role = Role::create(['code' => 'WAKA_AKADEMIK', 'name' => 'Waka Akademik']);
        UserRoleAssignment::create(['user_id' => $waka->id, 'role_id' => $role->id, 'effective_from' => '2026-07-01']);
        UserStaffLink::create(['user_id' => $waka->id, 'staff_id' => $wakaStaff->id, 'effective_from' => '2026-07-01']);

        $this->actingAs($waka)->get(route('academic.attendance.show', $session))
            ->assertOk()
            ->assertSee('Simpan draf');

        $this->actingAs($waka)->post(route('academic.attendance.draft', $session), [
            'participants' => [$participant->id => ['attendance_status' => 'PRESENT']],
        ])->assertRedirect(route('academic.attendance.show', $session));

        $this->actingAs($waka)->post(route('academic.attendance.finalize', $session))
            ->assertRedirect(route('academic.attendance.show', $session));

        $this->assertSame('VALIDATED', StudentAttendance::first()->workflow_status);
        $this->assertSame('COMPLETED', $session->fresh()->session_status);
    }

    public function test_finalize_returns_to_attendance_page_when_a_status_is_missing(): void
    {
        [$session, , $user] = $this->fixtures();

        $this->actingAs($user)->post(route('academic.attendance.finalize', $session), ['historical_session_ack' => '1'])
            ->assertRedirect(route('academic.attendance.show', $session))
            ->assertSessionHasErrors(['finalize' => 'Belum semua santri memiliki status kehadiran. Lengkapi status setiap santri terlebih dahulu.']);

        $this->assertSame('PLANNED', $session->fresh()->session_status);
    }

    public function test_finalize_saves_statuses_submitted_from_the_attendance_form(): void
    {
        [$session, $participant, $user] = $this->fixtures();

        $this->actingAs($user)->post(route('academic.attendance.finalize', $session), [
            'historical_session_ack' => '1',
            'participants' => [$participant->id => ['attendance_status' => 'PRESENT']],
        ])->assertRedirect(route('academic.attendance.show', $session));

        $this->assertSame('PRESENT', StudentAttendance::first()->attendance_status);
        $this->assertSame('VALIDATED', StudentAttendance::first()->workflow_status);
        $this->assertSame('COMPLETED', $session->fresh()->session_status);
    }

    public function test_http_draft_rejects_completed_session_without_mutation(): void
    {
        [$session, $participant, $user] = $this->fixtures();
        $attendance = StudentAttendance::create(['session_student_participant_id' => $participant->id, 'attendance_status' => 'PRESENT', 'workflow_status' => 'VALIDATED', 'version_no' => 2, 'entered_by' => $user->id, 'entered_at' => now(), 'finalized_by' => $user->id, 'finalized_at' => now(), 'updated_by' => $user->id, 'updated_at' => now()]);
        $session->update(['session_status' => 'COMPLETED']);
        $auditCount = AuditLog::where('action', 'STUDENT_ATTENDANCE_DRAFT_SAVED')->count();

        $this->actingAs($user)->post(route('academic.attendance.draft', $session), [
            'historical_session_ack' => '1',
            'participants' => [$participant->id => ['attendance_status' => 'ABSENT']],
        ])->assertRedirect(route('academic.attendance.show', $session))
            ->assertSessionHasErrors('draft');

        $this->assertSame('PRESENT', $attendance->fresh()->attendance_status);
        $this->assertSame('VALIDATED', $attendance->fresh()->workflow_status);
        $this->assertSame(2, $attendance->fresh()->version_no);
        $this->assertSame('COMPLETED', $session->fresh()->session_status);
        $this->assertSame($auditCount, AuditLog::where('action', 'STUDENT_ATTENDANCE_DRAFT_SAVED')->count());
    }

    public function test_http_draft_rejects_locked_period_without_mutation(): void
    {
        [$session, $participant, $user] = $this->fixtures();
        $attendance = StudentAttendance::create(['session_student_participant_id' => $participant->id, 'attendance_status' => 'PRESENT', 'workflow_status' => 'DRAFT', 'version_no' => 1, 'entered_by' => $user->id, 'entered_at' => now(), 'updated_by' => $user->id, 'updated_at' => now()]);
        AttendancePeriodLock::create(['class_id' => $session->class_id, 'period_start' => '2026-07-01', 'period_end' => '2026-07-31', 'status' => 'LOCKED', 'locked_by' => $user->id, 'locked_at' => now()]);

        $this->actingAs($user)->post(route('academic.attendance.draft', $session), [
            'historical_session_ack' => '1',
            'participants' => [$participant->id => ['attendance_status' => 'ABSENT']],
        ])->assertRedirect(route('academic.attendance.show', $session))
            ->assertSessionHasErrors('draft');

        $this->assertSame('PRESENT', $attendance->fresh()->attendance_status);
        $this->assertSame('DRAFT', $attendance->fresh()->workflow_status);
        $this->assertSame(1, $attendance->fresh()->version_no);
    }

    public function test_http_finalize_rejects_missing_or_unresolved_primary_teacher(): void
    {
        [$session, $participant, $user] = $this->fixtures();
        $primary = SessionTeacherParticipation::where('class_session_id', $session->id)->firstOrFail();
        $payload = ['participants' => [$participant->id => ['attendance_status' => 'PRESENT']]];
        $payload['historical_session_ack'] = '1';

        $primary->delete();
        $this->actingAs($user)->post(route('academic.attendance.finalize', $session), $payload)
            ->assertRedirect(route('academic.attendance.show', $session))
            ->assertSessionHasErrors(['finalize' => 'Guru utama sesi belum tercatat; sesi belum dapat disahkan.']);
        $this->assertSame('PLANNED', $session->fresh()->session_status);
        $this->assertSame('DRAFT', StudentAttendance::first()->workflow_status);

        $primary = SessionTeacherParticipation::create(['class_session_id' => $session->id, 'teacher_staff_id' => $session->teachingAssignment->teacher_staff_id, 'role' => 'PRIMARY', 'obligation_type' => 'TEACHING_ASSIGNMENT']);
        $this->actingAs($user)->post(route('academic.attendance.finalize', $session), $payload)
            ->assertRedirect(route('academic.attendance.show', $session))
            ->assertSessionHasErrors(['finalize' => 'Status kehadiran guru utama harus dicatat sebelum kehadiran santri disahkan.']);
        $this->assertSame('PLANNED', $session->fresh()->session_status);
        $this->assertSame('DRAFT', StudentAttendance::first()->workflow_status);
    }

    public function test_http_finalize_requires_present_substitute_for_each_non_present_primary_status(): void
    {
        [$session, $participant, $user] = $this->fixtures();
        $primary = SessionTeacherParticipation::where('class_session_id', $session->id)->firstOrFail();
        $payload = ['participants' => [$participant->id => ['attendance_status' => 'PRESENT']]];
        $payload['historical_session_ack'] = '1';

        foreach (['ABSENT', 'SICK', 'IZIN', 'OTHER'] as $status) {
            $primary->update(['attendance_status' => $status]);
            $this->actingAs($user)->post(route('academic.attendance.finalize', $session), $payload)
                ->assertRedirect(route('academic.attendance.show', $session))
                ->assertSessionHasErrors('finalize');
            $this->assertSame('PLANNED', $session->fresh()->session_status);
            $this->assertSame('DRAFT', StudentAttendance::first()->workflow_status);
        }

        $homeroomStaffId = ClassHomeroomAssignment::where('class_id', $session->class_id)->value('staff_id');
        $substitute = SessionTeacherParticipation::create(['class_session_id' => $session->id, 'teacher_staff_id' => $homeroomStaffId, 'role' => 'SUBSTITUTE', 'obligation_type' => 'REPLACEMENT']);
        $this->actingAs($user)->post(route('academic.attendance.finalize', $session), $payload)
            ->assertRedirect(route('academic.attendance.show', $session))
            ->assertSessionHasErrors('finalize');
        $this->assertSame('PLANNED', $session->fresh()->session_status);

        $substitute->update(['attendance_status' => 'PRESENT']);
        $this->actingAs($user)->post(route('academic.attendance.finalize', $session), $payload)
            ->assertRedirect(route('academic.attendance.show', $session));
        $this->assertSame('COMPLETED', $session->fresh()->session_status);
        $this->assertSame('VALIDATED', StudentAttendance::first()->workflow_status);
    }

    public function test_waka_can_record_substitute_teacher_for_one_session(): void
    {
        [$session] = $this->fixtures();
        $waka = User::factory()->create();
        $wakaStaff = Staff::create(['staff_code' => 'STAFF-UI-SUB', 'full_name' => 'Waka Substitusi']);
        $replacement = Staff::create(['staff_code' => 'STAFF-UI-REPLACEMENT', 'full_name' => 'Ust. Dzaki']);
        $role = Role::create(['code' => 'WAKA_AKADEMIK', 'name' => 'Waka Akademik']);
        UserRoleAssignment::create(['user_id' => $waka->id, 'role_id' => $role->id, 'effective_from' => '2026-07-01']);
        UserStaffLink::create(['user_id' => $waka->id, 'staff_id' => $wakaStaff->id, 'effective_from' => '2026-07-01']);

        $this->actingAs($waka)->post(route('academic.attendance.substitution', $session), [
            'replacement_teacher_id' => $replacement->id,
            'reason' => 'Ust. Abu berhalangan hadir',
        ])->assertRedirect(route('academic.attendance.show', $session).'#rekap-guru');

        $this->assertDatabaseHas('schedule_changes', ['source_session_id' => $session->id, 'original_teacher_id' => $session->teachingAssignment->teacher_staff_id, 'replacement_teacher_id' => $replacement->id, 'change_type' => 'SUBSTITUTION', 'status' => 'APPLIED']);
        $this->assertDatabaseHas('session_teacher_participations', ['class_session_id' => $session->id, 'teacher_staff_id' => $replacement->id, 'role' => 'SUBSTITUTE']);
    }

    public function test_waka_can_use_active_homeroom_as_default_substitute(): void
    {
        [$session] = $this->fixtures();
        $waka = User::factory()->create();
        $wakaStaff = Staff::create(['staff_code' => 'STAFF-UI-WAKA-DEFAULT', 'full_name' => 'Waka Default']);
        $role = Role::create(['code' => 'WAKA_AKADEMIK', 'name' => 'Waka Akademik']);
        UserRoleAssignment::create(['user_id' => $waka->id, 'role_id' => $role->id, 'effective_from' => '2026-07-01']);
        UserStaffLink::create(['user_id' => $waka->id, 'staff_id' => $wakaStaff->id, 'effective_from' => '2026-07-01']);

        $this->actingAs($waka)->post(route('academic.attendance.substitution', $session), [
            'reason' => 'Ngaji di auditorium karena guru berhalangan hadir',
        ])->assertRedirect(route('academic.attendance.show', $session).'#rekap-guru');

        $homeroom = ClassHomeroomAssignment::where('class_id', $session->class_id)->first();
        $this->assertDatabaseHas('session_teacher_participations', ['class_session_id' => $session->id, 'teacher_staff_id' => $homeroom->staff_id, 'role' => 'SUBSTITUTE']);
    }

    public function test_unauthenticated_user_cannot_open_attendance_page(): void
    {
        [$session] = $this->fixtures();
        $this->get(route('academic.attendance.show', $session))->assertRedirect('/login');
    }

    public function test_waka_can_review_completed_session_without_edit_controls(): void
    {
        [$session, $participant] = $this->fixtures();
        $waka = User::factory()->create();
        $role = Role::create(['code' => 'WAKA_AKADEMIK', 'name' => 'Waka Akademik']);
        UserRoleAssignment::create(['user_id' => $waka->id, 'role_id' => $role->id, 'effective_from' => '2026-07-01']);
        StudentAttendance::create([
            'session_student_participant_id' => $participant->id,
            'attendance_status' => 'PRESENT',
            'workflow_status' => 'VALIDATED',
            'entered_by' => $waka->id,
            'entered_at' => now(),
            'finalized_by' => $waka->id,
            'finalized_at' => now(),
            'updated_by' => $waka->id,
            'updated_at' => now(),
        ]);
        $session->update(['session_status' => 'COMPLETED']);

        $this->actingAs($waka)->get(route('academic.attendance.review', $session))
            ->assertOk()
            ->assertSee('Pemeriksaan Waka Akademik')
            ->assertSee('UI Student')
            ->assertSee('Sudah diperiksa')
            ->assertDontSee('Simpan sementara')
            ->assertDontSee('Sahkan kehadiran');

        $this->actingAs($waka)->get(route('academic.attendance.review.csv', $session))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $detailPdf = $this->actingAs($waka)->get(route('academic.attendance.review.pdf', $session));
        $detailPdf->assertOk()->assertHeader('content-type', 'application/pdf')->assertSee('%PDF-1.4');
        $this->assertStringContainsString('DETAIL KEHADIRAN SANTRI', $detailPdf->getContent());
    }

    public function test_wali_cannot_open_waka_review_page(): void
    {
        [$session, , $wali] = $this->fixtures();

        $this->actingAs($wali)->get(route('academic.attendance.review', $session))
            ->assertForbidden();
    }

    public function test_waka_can_filter_completed_review_sessions(): void
    {
        [$session, $participant] = $this->fixtures();
        $waka = User::factory()->create();
        $role = Role::create(['code' => 'WAKA_AKADEMIK', 'name' => 'Waka Akademik']);
        UserRoleAssignment::create(['user_id' => $waka->id, 'role_id' => $role->id, 'effective_from' => '2026-07-01']);
        StudentAttendance::create([
            'session_student_participant_id' => $participant->id,
            'attendance_status' => 'PRESENT',
            'workflow_status' => 'VALIDATED',
            'entered_by' => $waka->id,
            'entered_at' => now(),
            'finalized_by' => $waka->id,
            'finalized_at' => now(),
            'updated_by' => $waka->id,
            'updated_at' => now(),
        ]);
        $session->update(['session_status' => 'COMPLETED']);
        $secondary = $session->academicClass->replicate();
        $secondary->class_code = 'CLASS-UI-B';
        $secondary->section_code = 'B';
        $secondary->display_name = 'Kelas 1 B';
        $secondary->save();
        ClassSessionGroup::create(['class_session_id' => $session->id, 'class_id' => $session->class_id, 'scope_role' => 'JOINT_SCOPE']);
        ClassSessionGroup::create(['class_session_id' => $session->id, 'class_id' => $secondary->id, 'scope_role' => 'JOINT_SCOPE']);

        $this->actingAs($waka)->get(route('academic.attendance.reviews', [
            'class_id' => $session->class_id,
            'from' => '2026-07-01',
            'to' => '2026-07-31',
        ]))
            ->assertOk()
            ->assertSee('1 sesi selesai')
            ->assertSee('Kelas 1 A')
            ->assertSee('Kelas 1 A + Kelas 1 B')
            ->assertSee('Periksa hasil');

        $this->actingAs($waka)->get(route('academic.attendance.reviews', [
            'class_id' => $secondary->id, 'from' => '2026-07-01', 'to' => '2026-07-31',
        ]))
            ->assertOk()
            ->assertSee('1 sesi selesai')
            ->assertSee('Kelas 1 A + Kelas 1 B')
            ->assertSee('Periksa hasil');

        $this->actingAs($waka)->get(route('academic.attendance.review', $session))
            ->assertOk()
            ->assertSee('← Kembali ke daftar sesi')
            ->assertDontSee('Kembali ke daftar sesi kelas ini');

        $this->actingAs($waka)->get(route('academic.attendance.reviews.csv', ['class_id' => $session->class_id]))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $pdf = $this->actingAs($waka)->get(route('academic.attendance.reviews.pdf', ['class_id' => $session->class_id]));
        $pdf
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertSee('%PDF-1.4');
        $this->assertStringContainsString('Halaman 1 dari 1', $pdf->getContent());
    }

    private function fixtures(): array
    {
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-UI', 'unit_name' => 'UI Unit', 'unit_type' => 'SCHOOL']);
        $grade = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '1', 'display_name' => 'Tingkat 1', 'sequence_no' => 1]);
        $year = AcademicYear::create(['year_code' => '2026-UI', 'display_name' => '2026/2027', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $semester = Semester::create(['academic_year_id' => $year->id, 'semester_code' => 'ODD', 'display_name' => 'Semester Ganjil', 'sequence_no' => 1, 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-31']);
        $class = AcademicClass::create(['class_code' => 'CLASS-UI-A', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id, 'section_code' => 'A', 'display_name' => 'Kelas 1 A']);
        $subject = Subject::create(['subject_code' => 'SUBJ-UI', 'subject_name' => 'UI Subject']);
        $teacher = Staff::create(['staff_code' => 'STAFF-UI-001', 'full_name' => 'Teacher']);
        $homeroom = Staff::create(['staff_code' => 'STAFF-UI-002', 'full_name' => 'Wali Kelas']);
        $user = User::factory()->create();
        $role = Role::create(['code' => 'WALI_KELAS', 'name' => 'Wali Kelas']);
        UserRoleAssignment::create(['user_id' => $user->id, 'role_id' => $role->id, 'effective_from' => '2026-07-01']);
        UserStaffLink::create(['user_id' => $user->id, 'staff_id' => $homeroom->id, 'effective_from' => '2026-07-01']);
        ClassHomeroomAssignment::create(['class_id' => $class->id, 'staff_id' => $homeroom->id, 'effective_from' => '2026-07-01']);
        $assignment = TeachingAssignment::create(['assignment_code' => 'TA-UI-001', 'semester_id' => $semester->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $teacher->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);
        $session = ClassSession::create(['session_code' => 'SESSION-UI-001', 'teaching_assignment_id' => $assignment->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'planned_start_at' => '2026-07-06 01:00:00+00:00', 'planned_end_at' => '2026-07-06 02:30:00+00:00', 'session_source' => 'SCHEDULED', 'participant_scope' => 'FULL_CLASS', 'session_status' => 'PLANNED']);
        $participant = SessionStudentParticipant::create(['class_session_id' => $session->id, 'student_id' => Student::create(['student_code' => 'STU-UI-001', 'full_name' => 'UI Student'])->id, 'participant_basis' => 'CLASS_ENROLLMENT']);
        SessionTeacherParticipation::create(['class_session_id' => $session->id, 'teacher_staff_id' => $teacher->id, 'role' => 'PRIMARY', 'obligation_type' => 'TEACHING_ASSIGNMENT', 'attendance_status' => 'PRESENT']);

        return [$session, $participant, $user];
    }
}
