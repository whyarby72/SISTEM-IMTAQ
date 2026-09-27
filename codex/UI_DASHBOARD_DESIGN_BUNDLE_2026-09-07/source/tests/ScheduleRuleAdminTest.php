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
        $this->actingAs($user)->get(route('admin.academic.schedules.index'))->assertOk()->assertSee('Data Jadwal');
        $this->actingAs($user)->post(route('admin.academic.schedules.store'), [
            'teaching_assignment_id' => $assignment->id, 'weekday' => 1, 'start_time' => '08:00', 'end_time' => '09:00',
            'recurrence_type' => 'EVERY_WEEK', 'effective_from' => '2026-07-01', 'effective_until' => '2026-07-15',
        ])->assertRedirect(route('admin.academic.schedules.index'));

        $this->assertDatabaseHas('schedule_rules', ['teaching_assignment_id' => $assignment->id, 'workflow_status' => 'APPROVED']);
        $this->assertDatabaseCount('class_sessions', 2);
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

    public function test_schedule_with_existing_sessions_cannot_be_edited(): void
    {
        [$user, $assignment] = $this->fixture();
        $this->actingAs($user)->post(route('admin.academic.schedules.store'), ['teaching_assignment_id' => $assignment->id, 'weekday' => 1, 'start_time' => '08:00', 'end_time' => '09:00', 'recurrence_type' => 'EVERY_WEEK', 'effective_from' => '2026-07-01', 'effective_until' => '2026-07-15']);
        $rule = ScheduleRule::where('teaching_assignment_id', $assignment->id)->firstOrFail();

        $this->actingAs($user)->put(route('admin.academic.schedules.update', $rule), ['weekday' => 2, 'start_time' => '09:00', 'end_time' => '10:00', 'recurrence_type' => 'EVERY_WEEK', 'effective_from' => '2026-07-01', 'effective_until' => '2026-07-31'])
            ->assertSessionHasErrors('schedule');
    }

    private function fixture(): array
    {
        $user = User::factory()->create();
        $role = Role::create(['code' => 'ADMIN_AKADEMIK', 'name' => 'Admin Akademik']);
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
