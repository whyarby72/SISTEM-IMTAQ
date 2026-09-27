<?php

namespace Tests\Feature\Admin;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\GradeLevel;
use App\Domains\Academic\Models\ScheduleRule;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Models\TeachingAssignment;
use App\Models\User;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use App\Shared\Core\Models\Staff;
use App\Shared\Platform\Authorization\Models\Role;
use App\Shared\Platform\Authorization\Models\UserRoleAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleRuleAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_academic_admin_can_view_and_create_schedule_with_sessions(): void
    {
        [$user, $assignment] = $this->fixture();
        $this->actingAs($user)->get(route('admin.academic.schedules.index'))->assertOk()->assertSee('Jadwal Akademik');
        $this->actingAs($user)->post(route('admin.academic.schedules.store'), [
            'teaching_assignment_id' => $assignment->id, 'weekday' => 1, 'start_time' => '08:00', 'end_time' => '09:00',
            'recurrence_type' => 'EVERY_WEEK', 'effective_from' => '2026-07-01', 'effective_until' => '2026-07-15',
        ])->assertRedirect(route('admin.academic.schedules.index'));

        $this->assertDatabaseHas('schedule_rules', ['teaching_assignment_id' => $assignment->id, 'workflow_status' => 'APPROVED']);
        $this->assertDatabaseCount('class_sessions', 2);
    }

    public function test_weekly_view_positions_actual_times_and_omits_friday_column(): void
    {
        [$user, $assignment] = $this->fixture();
        ScheduleRule::create(['teaching_assignment_id' => $assignment->id, 'weekday' => 3, 'start_time' => '07:00', 'end_time' => '09:30', 'recurrence_type' => 'EVERY_WEEK', 'effective_from' => '2026-07-01', 'effective_until' => '2026-12-31', 'workflow_status' => 'APPROVED']);

        $this->actingAs($user)->get(route('admin.academic.schedules.index', ['view' => 'weekly']))
            ->assertOk()
            ->assertSee('Grid jadwal mingguan')
            ->assertSee('Rabu')
            ->assertSee('07:00–09:30')
            ->assertSee('Jumat libur kegiatan akademik reguler.')
            ->assertDontSee('>Jumat</div>');
    }

    public function test_academic_admin_can_create_week_of_month_rule_with_selected_weeks(): void
    {
        [$user, $assignment] = $this->fixture();

        $this->actingAs($user)->post(route('admin.academic.schedules.store'), [
            'teaching_assignment_id' => $assignment->id, 'weekday' => 3, 'start_time' => '08:00', 'end_time' => '09:30',
            'recurrence_type' => 'WEEK_OF_MONTH', 'week_numbers' => [2, 4, 5],
            'effective_from' => '2026-09-01', 'effective_until' => '2026-10-01',
        ])->assertRedirect(route('admin.academic.schedules.index'));

        $rule = ScheduleRule::where('teaching_assignment_id', $assignment->id)->firstOrFail();
        $this->assertSame([2, 4, 5], $rule->weekNumbers()->orderBy('week_no')->pluck('week_no')->all());
        $this->assertDatabaseCount('class_sessions', 3);
        $this->assertDatabaseHas('class_sessions', ['schedule_rule_id' => $rule->id, 'planned_start_at' => '2026-09-30 08:00:00']);
    }

    public function test_create_schedule_only_lists_official_non_pilot_assignments(): void
    {
        [$user, $assignment] = $this->fixture();
        $pilotYear = AcademicYear::create(['year_code' => '2026/2027-PILOT', 'display_name' => '2026/2027 Pilot', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $pilotClass = AcademicClass::create(['class_code' => 'PILOT-SCHEDULE', 'academic_year_id' => $pilotYear->id, 'organizational_unit_id' => OrganizationalUnit::first()->id, 'grade_level_id' => GradeLevel::first()->id, 'section_code' => 'P', 'display_name' => 'Kelas Pilot']);
        TeachingAssignment::create(['assignment_code' => 'PILOT-ASSIGNMENT', 'semester_id' => Semester::firstOrFail()->id, 'class_id' => $pilotClass->id, 'subject_id' => $assignment->subject_id, 'teacher_staff_id' => $assignment->teacher_staff_id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);

        $this->actingAs($user)->get(route('admin.academic.schedules.create'))
            ->assertOk()
            ->assertSee($assignment->assignment_code)
            ->assertSee($assignment->teacher->full_name)
            ->assertDontSee('PILOT-ASSIGNMENT')
            ->assertDontSee('Kelas Pilot');
    }

    public function test_schedule_preparation_forms_hide_pilot_semesters(): void
    {
        [$user, $assignment] = $this->fixture();
        $pilotYear = AcademicYear::create(['year_code' => '2026/2027-PILOT', 'display_name' => '2026/2027 Pilot', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        Semester::create(['academic_year_id' => $pilotYear->id, 'semester_code' => 'S1-PILOT', 'display_name' => 'Pilot', 'sequence_no' => 1, 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-31']);

        $this->actingAs($user)->get(route('admin.academic.schedules.create'))
            ->assertOk()
            ->assertSee('Semester I YEAR-ADMIN-SCHEDULE')
            ->assertDontSee('S1-PILOT');
        $this->actingAs($user)->get(route('admin.academic.teaching-assignments.create'))
            ->assertOk()
            ->assertSee('Tambah Penugasan Mengajar')
            ->assertDontSee('S1-PILOT');
    }

    public function test_create_schedule_lists_published_official_assignment(): void
    {
        [$user, $assignment] = $this->fixture();
        $assignment->update(['workflow_status' => 'PUBLISHED']);

        $this->actingAs($user)->get(route('admin.academic.schedules.create'))
            ->assertOk()
            ->assertSee($assignment->assignment_code);
    }

    public function test_create_schedule_hides_assignment_when_all_its_rules_are_archived(): void
    {
        [$user, $assignment] = $this->fixture();
        ScheduleRule::create([
            'teaching_assignment_id' => $assignment->id, 'weekday' => 1, 'start_time' => '08:00', 'end_time' => '09:00',
            'recurrence_type' => 'EVERY_WEEK', 'effective_from' => '2026-07-01', 'effective_until' => '2026-07-31', 'workflow_status' => 'ARCHIVED',
        ]);

        $this->actingAs($user)->get(route('admin.academic.schedules.create'))
            ->assertOk()
            ->assertDontSee($assignment->assignment_code);
    }

    public function test_create_schedule_shows_one_option_for_duplicate_assignment_identity(): void
    {
        [$user, $assignment] = $this->fixture();
        $duplicate = TeachingAssignment::create([
            'assignment_code' => 'TA-DUPLICATE-OPTION', 'semester_id' => $assignment->semester_id,
            'class_id' => $assignment->class_id, 'subject_id' => $assignment->subject_id,
            'teacher_staff_id' => $assignment->teacher_staff_id, 'effective_from' => '2026-07-02', 'workflow_status' => 'ACTIVE',
        ]);

        $response = $this->actingAs($user)->get(route('admin.academic.schedules.create'));

        $response->assertOk()->assertSee($duplicate->assignment_code);
        $this->assertSame(1, substr_count($response->getContent(), 'title="Kode: '.$duplicate->assignment_code.'"'));
        $this->assertSame(0, substr_count($response->getContent(), 'title="Kode: '.$assignment->assignment_code.'"'));
    }

    public function test_admin_can_open_and_store_a_new_teaching_assignment(): void
    {
        [$user, $assignment] = $this->fixture();

        $this->actingAs($user)->get(route('admin.academic.teaching-assignments.create'))
            ->assertOk()
            ->assertSee('Tambah Penugasan Mengajar');

        $this->actingAs($user)->post(route('admin.academic.teaching-assignments.store'), [
            'semester_id' => $assignment->semester_id, 'class_id' => $assignment->class_id,
            'subject_id' => $assignment->subject_id, 'teacher_staff_id' => $assignment->teacher_staff_id,
            'effective_from' => '2026-07-28', 'effective_until' => '2026-12-31',
        ])->assertRedirect(route('admin.academic.schedules.create'));

        $this->assertDatabaseHas('teaching_assignments', [
            'semester_id' => $assignment->semester_id, 'class_id' => $assignment->class_id,
            'subject_id' => $assignment->subject_id, 'teacher_staff_id' => $assignment->teacher_staff_id,
            'source_reference' => 'ADMIN-MANUAL', 'workflow_status' => 'APPROVED',
        ]);
    }

    public function test_admin_can_manage_subjects_from_the_schedule_preparation_flow(): void
    {
        [$user] = $this->fixture();

        $this->actingAs($user)->get(route('admin.academic.schedules.create'))
            ->assertOk()
            ->assertSee(route('admin.academic.subjects.create'), false);

        $this->actingAs($user)->post(route('admin.academic.subjects.store'), [
            'subject_code' => 'SUBJ-NEW-ADMIN', 'subject_name' => 'Akhlak',
        ])->assertRedirect(route('admin.academic.subjects.index'));

        $this->assertDatabaseHas('subjects', ['subject_code' => 'SUBJ-NEW-ADMIN', 'subject_name' => 'Akhlak', 'status' => 'ACTIVE']);
    }

    public function test_admin_can_create_schedule_by_selecting_simple_academic_fields(): void
    {
        [$user, $assignment] = $this->fixture();

        $this->actingAs($user)->post(route('admin.academic.schedules.store'), [
            'semester_id' => $assignment->semester_id, 'class_id' => $assignment->class_id,
            'subject_id' => $assignment->subject_id, 'teacher_staff_id' => $assignment->teacher_staff_id,
            'weekday' => 3, 'start_time' => '10:00', 'end_time' => '11:00',
            'recurrence_type' => 'EVERY_WEEK', 'effective_from' => '2026-07-01', 'effective_until' => '2026-07-15',
        ])->assertRedirect(route('admin.academic.schedules.index'));

        $this->assertDatabaseHas('schedule_rules', ['teaching_assignment_id' => $assignment->id, 'weekday' => 3, 'start_time' => '10:00']);
    }

    public function test_schedule_page_excludes_pilot_teachers_from_data_and_filters(): void
    {
        [$user, $assignment] = $this->fixture();
        $pilotTeacher = Staff::create(['staff_code' => 'PILOT-GURU-3A', 'full_name' => 'Guru Sample 3A']);
        $pilotAssignment = TeachingAssignment::create([
            'assignment_code' => 'TA-PILOT-TEACHER', 'semester_id' => $assignment->semester_id,
            'class_id' => $assignment->class_id, 'subject_id' => $assignment->subject_id,
            'teacher_staff_id' => $pilotTeacher->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE',
        ]);
        ScheduleRule::create(['teaching_assignment_id' => $pilotAssignment->id, 'weekday' => 1, 'start_time' => '08:00', 'end_time' => '09:00', 'recurrence_type' => 'EVERY_WEEK', 'effective_from' => '2026-07-01', 'effective_until' => '2026-12-31', 'workflow_status' => 'APPROVED']);

        $this->actingAs($user)->get(route('admin.academic.schedules.index'))
            ->assertDontSee('Guru Sample 3A')
            ->assertDontSee('TA-PILOT-TEACHER');
    }

    public function test_non_admin_cannot_manage_schedules(): void
    {
        $this->actingAs(User::factory()->create())->get(route('admin.academic.schedules.index'))->assertForbidden();
    }

    public function test_academic_admin_can_edit_schedule_without_sessions(): void
    {
        [$user, $assignment] = $this->fixture();
        $rule = ScheduleRule::create(['teaching_assignment_id' => $assignment->id, 'weekday' => 1, 'start_time' => '08:00', 'end_time' => '09:00', 'recurrence_type' => 'EVERY_WEEK', 'effective_from' => '2027-01-01', 'effective_until' => '2027-01-15', 'workflow_status' => 'APPROVED']);

        $this->actingAs($user)->put(route('admin.academic.schedules.update', $rule), ['weekday' => 2, 'start_time' => '09:00', 'end_time' => '10:00', 'recurrence_type' => 'EVERY_WEEK', 'effective_from' => '2027-01-01', 'effective_until' => '2027-01-31'])
            ->assertRedirect(route('admin.academic.schedules.index'));
        $this->assertDatabaseHas('schedule_rules', ['id' => $rule->id, 'weekday' => 2, 'start_time' => '09:00', 'end_time' => '10:00']);
    }

    public function test_schedule_with_existing_sessions_requires_revision_mode(): void
    {
        [$user, $assignment] = $this->fixture();
        $this->actingAs($user)->post(route('admin.academic.schedules.store'), ['teaching_assignment_id' => $assignment->id, 'weekday' => 1, 'start_time' => '08:00', 'end_time' => '09:00', 'recurrence_type' => 'EVERY_WEEK', 'effective_from' => '2026-07-01', 'effective_until' => '2026-07-15']);
        $rule = ScheduleRule::where('teaching_assignment_id', $assignment->id)->firstOrFail();

        $this->actingAs($user)->put(route('admin.academic.schedules.update', $rule), ['weekday' => 2, 'start_time' => '09:00', 'end_time' => '10:00', 'recurrence_type' => 'EVERY_WEEK', 'effective_from' => '2026-07-01', 'effective_until' => '2026-07-31'])
            ->assertSessionHasErrors('schedule');
    }

    public function test_academic_admin_can_create_schedule_revision_from_selected_date(): void
    {
        [$user, $assignment] = $this->fixture();
        $this->actingAs($user)->post(route('admin.academic.schedules.store'), ['teaching_assignment_id' => $assignment->id, 'weekday' => 1, 'start_time' => '08:00', 'end_time' => '09:00', 'recurrence_type' => 'EVERY_WEEK', 'effective_from' => '2026-07-01', 'effective_until' => '2026-07-31']);
        $rule = ScheduleRule::where('teaching_assignment_id', $assignment->id)->firstOrFail();

        $this->actingAs($user)->put(route('admin.academic.schedules.update', $rule), ['weekday' => 2, 'start_time' => '09:00', 'end_time' => '10:00', 'recurrence_type' => 'EVERY_WEEK', 'effective_from' => '2026-07-01', 'revision_from' => '2026-07-28', 'effective_until' => '2026-07-31', 'teacher_staff_id' => $assignment->teacher_staff_id, 'subject_id' => $assignment->subject_id, 'revision_reason' => 'KBM Kelas 1 dimulai 28 Juli 2026'])
            ->assertRedirect(route('admin.academic.schedules.index'));
        $this->assertSame('2026-07-27', ScheduleRule::findOrFail($rule->id)->effective_until->toDateString());
        $this->assertTrue(ScheduleRule::query()->where('teaching_assignment_id', $assignment->id)->where('weekday', 2)->whereDate('effective_from', '2026-07-28')->exists());
    }

    public function test_schedule_revision_can_assign_a_new_teacher_without_rewriting_history(): void
    {
        [$user, $assignment] = $this->fixture();
        $newTeacher = Staff::create(['staff_code' => 'GURU-REV-001', 'full_name' => 'Guru Revisi']);
        $this->actingAs($user)->post(route('admin.academic.schedules.store'), ['teaching_assignment_id' => $assignment->id, 'weekday' => 1, 'start_time' => '08:00', 'end_time' => '09:00', 'recurrence_type' => 'EVERY_WEEK', 'effective_from' => '2026-07-01', 'effective_until' => '2026-07-31']);
        $rule = ScheduleRule::where('teaching_assignment_id', $assignment->id)->firstOrFail();

        $this->actingAs($user)->put(route('admin.academic.schedules.update', $rule), ['weekday' => 1, 'start_time' => '08:00', 'end_time' => '09:00', 'recurrence_type' => 'EVERY_WEEK', 'effective_from' => '2026-07-01', 'revision_from' => '2026-07-28', 'effective_until' => '2026-07-31', 'teacher_staff_id' => $newTeacher->id, 'subject_id' => $assignment->subject_id, 'revision_reason' => 'Guru pengajar disesuaikan'])
            ->assertRedirect(route('admin.academic.schedules.index'));
        $this->assertDatabaseHas('teaching_assignments', ['class_id' => $assignment->class_id, 'teacher_staff_id' => $newTeacher->id]);
        $this->assertDatabaseHas('schedule_rules', ['teaching_assignment_id' => $assignment->id]);
    }

    public function test_academic_admin_can_delete_schedule_without_sessions(): void
    {
        [$user, $assignment] = $this->fixture();
        $rule = ScheduleRule::create(['teaching_assignment_id' => $assignment->id, 'weekday' => 1, 'start_time' => '08:00', 'end_time' => '09:00', 'recurrence_type' => 'EVERY_WEEK', 'effective_from' => '2027-01-01', 'effective_until' => '2027-01-31', 'workflow_status' => 'APPROVED']);

        $this->actingAs($user)->delete(route('admin.academic.schedules.destroy', $rule))->assertRedirect(route('admin.academic.schedules.index'));
        $this->assertDatabaseMissing('schedule_rules', ['id' => $rule->id]);
    }

    public function test_schedule_with_sessions_cannot_be_deleted(): void
    {
        [$user, $assignment] = $this->fixture();
        $this->actingAs($user)->post(route('admin.academic.schedules.store'), ['teaching_assignment_id' => $assignment->id, 'weekday' => 1, 'start_time' => '08:00', 'end_time' => '09:00', 'recurrence_type' => 'EVERY_WEEK', 'effective_from' => '2026-07-01', 'effective_until' => '2026-07-15']);
        $rule = ScheduleRule::where('teaching_assignment_id', $assignment->id)->firstOrFail();

        $this->actingAs($user)->delete(route('admin.academic.schedules.destroy', $rule))->assertSessionHasErrors('schedule');
        $this->assertDatabaseHas('schedule_rules', ['id' => $rule->id]);
    }

    public function test_academic_admin_can_archive_schedule_and_cancel_empty_sessions(): void
    {
        [$user, $assignment] = $this->fixture();
        $this->actingAs($user)->post(route('admin.academic.schedules.store'), ['teaching_assignment_id' => $assignment->id, 'weekday' => 1, 'start_time' => '08:00', 'end_time' => '09:00', 'recurrence_type' => 'EVERY_WEEK', 'effective_from' => '2026-07-01', 'effective_until' => '2026-07-15']);
        $rule = ScheduleRule::where('teaching_assignment_id', $assignment->id)->firstOrFail();

        $this->actingAs($user)->post(route('admin.academic.schedules.archive', $rule), ['archive_reason' => 'Jadwal uji dihentikan'])->assertRedirect(route('admin.academic.schedules.index'));
        $this->assertDatabaseHas('schedule_rules', ['id' => $rule->id, 'workflow_status' => 'ARCHIVED']);
        $this->assertDatabaseHas('class_sessions', ['schedule_rule_id' => $rule->id, 'session_status' => 'CANCELLED']);
        $this->assertDatabaseHas('schedule_changes', ['change_type' => 'SCHEDULE_ARCHIVE', 'status' => 'APPLIED']);
    }

    public function test_schedule_revision_can_replace_an_existing_revision_on_the_same_effective_date(): void
    {
        [$user, $assignment] = $this->fixture();
        $this->actingAs($user)->post(route('admin.academic.schedules.store'), ['teaching_assignment_id' => $assignment->id, 'weekday' => 2, 'start_time' => '08:00', 'end_time' => '09:00', 'recurrence_type' => 'EVERY_WEEK', 'effective_from' => '2026-07-28', 'effective_until' => '2026-07-31']);
        $rule = ScheduleRule::where('teaching_assignment_id', $assignment->id)->firstOrFail();

        $this->actingAs($user)->put(route('admin.academic.schedules.update', $rule), ['weekday' => 2, 'start_time' => '09:00', 'end_time' => '10:00', 'recurrence_type' => 'EVERY_WEEK', 'revision_from' => '2026-07-28', 'effective_until' => '2026-07-31', 'teacher_staff_id' => $assignment->teacher_staff_id, 'subject_id' => $assignment->subject_id, 'revision_reason' => 'Koreksi jadwal revisi'])
            ->assertRedirect(route('admin.academic.schedules.index'));
        $this->assertDatabaseHas('schedule_rules', ['id' => $rule->id, 'workflow_status' => 'ARCHIVED']);
        $this->assertTrue(ScheduleRule::query()->where('teaching_assignment_id', $assignment->id)->where('weekday', 2)->whereDate('effective_from', '2026-07-28')->exists());
    }

    public function test_schedule_revision_can_change_subject_without_changing_teacher(): void
    {
        [$user, $assignment] = $this->fixture();
        $newSubject = Subject::create(['subject_code' => 'SUBJ-REV-002', 'subject_name' => 'Bahasa Arab']);
        $this->actingAs($user)->post(route('admin.academic.schedules.store'), ['teaching_assignment_id' => $assignment->id, 'weekday' => 2, 'start_time' => '08:00', 'end_time' => '09:00', 'recurrence_type' => 'EVERY_WEEK', 'effective_from' => '2026-07-28', 'effective_until' => '2026-07-31']);
        $rule = ScheduleRule::where('teaching_assignment_id', $assignment->id)->firstOrFail();

        $this->actingAs($user)->put(route('admin.academic.schedules.update', $rule), ['weekday' => 2, 'start_time' => '08:00', 'end_time' => '09:00', 'recurrence_type' => 'EVERY_WEEK', 'revision_from' => '2026-07-28', 'effective_until' => '2026-07-31', 'teacher_staff_id' => $assignment->teacher_staff_id, 'subject_id' => $newSubject->id, 'revision_reason' => 'Koreksi mata pelajaran'])
            ->assertRedirect(route('admin.academic.schedules.index'));
        $this->assertDatabaseHas('teaching_assignments', ['class_id' => $assignment->class_id, 'subject_id' => $newSubject->id, 'teacher_staff_id' => $assignment->teacher_staff_id]);
    }

    public function test_class_index_keeps_pilot_history_out_of_official_operational_list(): void
    {
        [$user] = $this->fixture();
        $pilotYear = AcademicYear::create(['year_code' => '2026/2027-PILOT', 'display_name' => '2026/2027 Pilot', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        AcademicClass::create(['class_code' => 'PILOT-CLASS', 'academic_year_id' => $pilotYear->id, 'organizational_unit_id' => OrganizationalUnit::first()->id, 'grade_level_id' => GradeLevel::first()->id, 'section_code' => 'P', 'display_name' => 'Kelas Pilot']);

        $this->actingAs($user)->get(route('admin.academic.classes.index'))
            ->assertOk()
            ->assertSee('CLASS-ADMIN-SCHEDULE')
            ->assertDontSee('PILOT-CLASS')
            ->assertSee('Data pilot lama tetap tersimpan sebagai histori');
    }

    private function fixture(): array
    {
        $user = User::factory()->create();
        $role = Role::create(['code' => 'WAKA_AKADEMIK', 'name' => 'Waka Akademik']);
        UserRoleAssignment::create(['user_id' => $user->id, 'role_id' => $role->id, 'effective_from' => '2026-07-01']);
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-ADMIN-SCHEDULE', 'unit_name' => 'Unit Admin Schedule', 'unit_type' => 'SCHOOL']);
        $grade = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '3', 'display_name' => 'Tingkat 3', 'sequence_no' => 3]);
        $year = AcademicYear::create(['year_code' => 'YEAR-ADMIN-SCHEDULE', 'display_name' => '2026/2027', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $semester = Semester::create(['academic_year_id' => $year->id, 'semester_code' => 'S1-ADMIN', 'display_name' => 'Semester 1', 'sequence_no' => 1, 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-31']);
        $class = AcademicClass::create(['class_code' => 'CLASS-ADMIN-SCHEDULE', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id, 'section_code' => 'A', 'display_name' => 'Kelas Schedule']);
        $subject = Subject::create(['subject_code' => 'SUBJ-ADMIN-SCHEDULE', 'subject_name' => 'Subject Schedule']);
        $teacher = Staff::create(['staff_code' => 'STAFF-ADMIN-SCHEDULE', 'full_name' => 'Teacher Schedule']);
        $assignment = TeachingAssignment::create(['assignment_code' => 'TA-ADMIN-SCHEDULE', 'semester_id' => $semester->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $teacher->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);

        return [$user, $assignment];
    }
}
