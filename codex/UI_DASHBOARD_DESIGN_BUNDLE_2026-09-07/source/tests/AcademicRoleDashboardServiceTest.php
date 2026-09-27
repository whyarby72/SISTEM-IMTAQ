<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\ClassHomeroomAssignment;
use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\GradeLevel;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Models\TeachingAssignment;
use App\Domains\Academic\Services\AcademicDashboardExportService;
use App\Domains\Academic\Services\AcademicRoleDashboardService;
use App\Models\User;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use App\Shared\Core\Models\Staff;
use App\Shared\Platform\Authorization\Models\Permission;
use App\Shared\Platform\Authorization\Models\Role;
use App\Shared\Platform\Authorization\Models\UserRoleAssignment;
use App\Shared\Platform\Authorization\Models\UserStaffLink;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AcademicRoleDashboardServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_wali_sees_only_assigned_class_and_waka_sees_all_classes(): void
    {
        [$first, $second, $wali, $waka] = $this->fixture();
        $service = app(AcademicRoleDashboardService::class);
        $from = Carbon::parse('2026-07-01');
        $to = Carbon::parse('2026-07-31')->endOfDay();

        $this->assertCount(1, $service->forUser($wali, $from, $to)['classes']);
        $this->assertSame($first->id, $service->forUser($wali, $from, $to)['classes']->first()['class']->id);
        $this->assertCount(2, $service->forUser($waka, $from, $to)['classes']);
    }

    public function test_grade_level_dashboard_aggregates_classes_by_canonical_grade_level_id(): void
    {
        [$first, $second, , $waka] = $this->fixture();
        $summary = app(AcademicRoleDashboardService::class)->forUser($waka, Carbon::parse('2026-07-01'), Carbon::parse('2026-07-31')->endOfDay())['grade_levels'];

        $this->assertCount(1, $summary);
        $this->assertSame($first->grade_level_id, $summary->first()['grade_level_id']);
        $this->assertSame(2, $summary->first()['class_count']);
        $this->assertEqualsCanonicalizing([$first->id, $second->id], $summary->first()['class_ids']->all());
    }

    public function test_dashboard_route_returns_authorized_academic_view(): void
    {
        [$first, , $wali] = $this->fixture();

        $this->actingAs($wali)
            ->get('/academic/dashboard?from=2026-07-01&to=2026-07-31')
            ->assertOk()
            ->assertSee('Ringkasan Akademik')
            ->assertSee($first->display_name)
            ->assertSee('Peran: Wali Kelas')
            ->assertDontSee('Perlu Perhatian Kehadiran');
    }

    public function test_dashboard_two_class_fixture_stays_within_query_count_baseline(): void
    {
        [, , , $waka] = $this->fixture();
        $queries = 0;
        DB::listen(static function () use (&$queries): void {
            $queries++;
        });

        app(AcademicRoleDashboardService::class)->forUser($waka, Carbon::parse('2026-07-01'), Carbon::parse('2026-07-31')->endOfDay());

        $this->assertLessThanOrEqual(20, $queries);
    }

    public function test_super_admin_can_view_all_academic_classes(): void
    {
        [$first, $second] = $this->fixture();
        $superAdmin = User::factory()->create();
        $role = Role::create(['code' => 'SUPER_ADMIN', 'name' => 'Super Admin']);
        UserRoleAssignment::create(['user_id' => $superAdmin->id, 'role_id' => $role->id]);

        $dashboard = app(AcademicRoleDashboardService::class)->forUser($superAdmin, Carbon::parse('2026-07-01'), Carbon::parse('2026-07-31')->endOfDay());

        $this->assertSame('SUPER_ADMIN', $dashboard['role']);
        $this->assertEqualsCanonicalizing([$first->id, $second->id], $dashboard['classes']->pluck('class.id')->all());
    }

    public function test_unrelated_role_is_denied(): void
    {
        $fixture = $this->fixture();
        $first = $fixture[0];
        $guru = User::factory()->create();
        $role = Role::create(['code' => 'GURU', 'name' => 'Guru']);
        UserRoleAssignment::create(['user_id' => $guru->id, 'role_id' => $role->id]);
        $this->expectException(AuthorizationException::class);
        app(AcademicRoleDashboardService::class)->forUser($guru, Carbon::parse('2026-07-01'), Carbon::parse('2026-07-31'));
    }

    public function test_wali_scope_ends_exclusively_at_effective_until(): void
    {
        [$first, , $wali] = $this->fixture();
        $assignment = ClassHomeroomAssignment::where('class_id', $first->id)->firstOrFail();
        $assignment->update(['effective_until' => '2026-07-15']);

        $classes = app(AcademicRoleDashboardService::class)->forUser($wali, Carbon::parse('2026-07-15'), Carbon::parse('2026-07-31'))['classes'];

        $this->assertCount(0, $classes);
    }

    public function test_wali_role_is_evaluated_at_requested_period_start(): void
    {
        [, , $wali] = $this->fixture();
        UserRoleAssignment::where('user_id', $wali->id)->update(['effective_until' => '2026-07-15']);

        $this->expectException(AuthorizationException::class);
        app(AcademicRoleDashboardService::class)->forUser($wali, Carbon::parse('2026-07-16'), Carbon::parse('2026-07-31'));
    }

    public function test_export_requires_explicit_permission_and_reuses_dashboard_metrics(): void
    {
        [$first, , $wali] = $this->fixture();
        $role = UserRoleAssignment::where('user_id', $wali->id)->firstOrFail()->role;
        $permission = Permission::create(['code' => 'academic.dashboard.export', 'name' => 'Export Academic dashboard']);
        $role->permissions()->attach($permission);

        $csv = app(AcademicDashboardExportService::class)->csv($wali, Carbon::parse('2026-07-01'), Carbon::parse('2026-07-31')->endOfDay());

        $this->assertStringContainsString('attendance_completeness_pct', $csv);
        $this->assertStringContainsString($first->display_name, $csv);
        $semesterCsv = app(AcademicDashboardExportService::class)->csv($wali, Carbon::parse('2026-07-01'), Carbon::parse('2026-07-31')->endOfDay(), Semester::firstOrFail());
        $this->assertStringContainsString('Ganjil', $semesterCsv);
        $this->assertStringContainsString($first->display_name, $semesterCsv);
    }

    public function test_export_without_permission_is_denied(): void
    {
        [, , $wali] = $this->fixture();
        $this->expectException(AuthorizationException::class);
        app(AcademicDashboardExportService::class)->csv($wali, Carbon::parse('2026-07-01'), Carbon::parse('2026-07-31')->endOfDay());
    }

    private function fixture(): array
    {
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-DASH', 'unit_name' => 'Dashboard Unit', 'unit_type' => 'SCHOOL']);
        $level = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '1', 'display_name' => 'Tingkat 1', 'sequence_no' => 1]);
        $year = AcademicYear::create(['year_code' => '2026-DASH', 'display_name' => '2026/2027', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $semester = Semester::create(['academic_year_id' => $year->id, 'semester_code' => 'ODD', 'display_name' => 'Ganjil', 'sequence_no' => 1, 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-31']);
        $subject = Subject::create(['subject_code' => 'DASH-SUBJ', 'subject_name' => 'Dashboard']);
        $teacher = Staff::create(['staff_code' => 'DASH-TEACHER', 'full_name' => 'Teacher']);
        $first = AcademicClass::create(['class_code' => 'DASH-A', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $level->id, 'section_code' => 'A', 'display_name' => 'Kelas A']);
        $second = AcademicClass::create(['class_code' => 'DASH-B', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $level->id, 'section_code' => 'B', 'display_name' => 'Kelas B']);
        $assignment = TeachingAssignment::create(['assignment_code' => 'DASH-TA', 'semester_id' => $semester->id, 'class_id' => $first->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $teacher->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);
        TeachingAssignment::create(['assignment_code' => 'DASH-TA-B', 'semester_id' => $semester->id, 'class_id' => $second->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $teacher->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);
        foreach ([[$first, $assignment], [$second, TeachingAssignment::where('assignment_code', 'DASH-TA-B')->firstOrFail()]] as [$class, $classAssignment]) {
            ClassSession::create(['session_code' => 'DASH-SESSION-'.$class->class_code, 'teaching_assignment_id' => $classAssignment->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'planned_start_at' => '2026-07-10 08:00:00', 'planned_end_at' => '2026-07-10 09:00:00', 'session_source' => 'SCHEDULED', 'participant_scope' => 'FULL_CLASS', 'session_status' => 'COMPLETED']);
        }
        $wali = User::factory()->create();
        $waka = User::factory()->create();
        $waliRole = Role::create(['code' => 'WALI_KELAS', 'name' => 'Wali']);
        $wakaRole = Role::create(['code' => 'WAKA_AKADEMIK', 'name' => 'Waka']);
        UserRoleAssignment::create(['user_id' => $wali->id, 'role_id' => $waliRole->id]);
        UserRoleAssignment::create(['user_id' => $waka->id, 'role_id' => $wakaRole->id]);
        UserStaffLink::create(['user_id' => $wali->id, 'staff_id' => $teacher->id, 'effective_from' => '2026-07-01']);
        ClassHomeroomAssignment::create(['class_id' => $first->id, 'staff_id' => $teacher->id, 'effective_from' => '2026-07-01', 'status' => 'ACTIVE']);

        return [$first, $second, $wali, $waka, $assignment];
    }
}
