<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\ClassHomeroomAssignment;
use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\GradeLevel;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Models\TeachingAssignment;
use App\Domains\Academic\Services\AcademicAuthorizationService;
use App\Models\User;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use App\Shared\Core\Models\Staff;
use App\Shared\Platform\Authorization\Models\Permission;
use App\Shared\Platform\Authorization\Models\Role;
use App\Shared\Platform\Authorization\Models\UserRoleAssignment;
use App\Shared\Platform\Authorization\Models\UserStaffLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AcademicAuthorizationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_effective_waka_with_academic_permission_has_full_academic_authority(): void
    {
        $user = $this->userWithPermission('WAKA_AKADEMIK', 'academic.domain.manage');

        $this->assertTrue(app(AcademicAuthorizationService::class)->hasAcademicFullAuthority($user, Carbon::parse('2026-07-15')));
    }

    public function test_effective_super_admin_with_institution_permission_has_full_academic_authority(): void
    {
        $user = $this->userWithPermission('SUPER_ADMIN', 'platform.institution.manage');

        $this->assertTrue(app(AcademicAuthorizationService::class)->hasAcademicFullAuthority($user, Carbon::parse('2026-07-15')));
        $this->assertTrue(app(AcademicAuthorizationService::class)->hasInstitutionWideAuthority($user, Carbon::parse('2026-07-15')));
    }

    public function test_wali_has_routine_own_class_scope_but_not_full_academic_authority(): void
    {
        [$user, $session, $otherSession] = $this->waliFixture();
        $authorization = app(AcademicAuthorizationService::class);

        $this->assertFalse($authorization->hasAcademicFullAuthority($user, Carbon::parse('2026-07-15')));
        $this->assertTrue($authorization->canManageAcademicSession($user, $session));
        $this->assertFalse($authorization->canManageAcademicSession($user, $otherSession));
    }

    public function test_expired_and_future_elevated_assignments_are_not_authorized(): void
    {
        $expired = $this->userWithPermission('WAKA_AKADEMIK', 'academic.domain.manage', '2026-01-01', '2026-06-30');
        $future = $this->userWithPermission('SUPER_ADMIN', 'platform.institution.manage', '2026-08-01');
        $authorization = app(AcademicAuthorizationService::class);

        $this->assertFalse($authorization->hasAcademicFullAuthority($expired, Carbon::parse('2026-07-15')));
        $this->assertFalse($authorization->hasAcademicFullAuthority($future, Carbon::parse('2026-07-15')));
    }

    public function test_role_code_without_required_permission_is_not_elevated_once_permission_model_is_configured(): void
    {
        Permission::create(['code' => 'academic.domain.manage', 'name' => 'Manage Academic domain']);
        Permission::create(['code' => 'platform.institution.manage', 'name' => 'Manage enabled institution domains']);
        $user = User::factory()->create();
        $role = Role::create(['code' => 'WAKA_AKADEMIK', 'name' => 'Waka Akademik']);
        UserRoleAssignment::create(['user_id' => $user->id, 'role_id' => $role->id, 'effective_from' => '2026-07-01']);

        $this->assertFalse(app(AcademicAuthorizationService::class)->hasAcademicFullAuthority($user, Carbon::parse('2026-07-15')));
    }

    public function test_retired_admin_academic_role_without_authority_permission_is_denied(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['code' => 'ADMIN_AKADEMIK', 'name' => 'Retired Academic Admin']);
        UserRoleAssignment::create(['user_id' => $user->id, 'role_id' => $role->id, 'effective_from' => '2026-07-01']);

        Permission::create(['code' => 'academic.domain.manage', 'name' => 'Manage Academic domain']);
        Permission::create(['code' => 'platform.institution.manage', 'name' => 'Manage enabled institution domains']);

        $this->assertFalse(app(AcademicAuthorizationService::class)->hasAcademicFullAuthority($user, Carbon::parse('2026-07-15')));
    }

    private function userWithPermission(string $roleCode, string $permissionCode, string $effectiveFrom = '2026-07-01', ?string $effectiveUntil = null): User
    {
        $user = User::factory()->create();
        $role = Role::create(['code' => $roleCode, 'name' => $roleCode]);
        $permission = Permission::create(['code' => $permissionCode, 'name' => $permissionCode]);
        $role->permissions()->attach($permission);
        UserRoleAssignment::create(['user_id' => $user->id, 'role_id' => $role->id, 'effective_from' => $effectiveFrom, 'effective_until' => $effectiveUntil]);

        return $user;
    }

    private function waliFixture(): array
    {
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-AUTHZ', 'unit_name' => 'Authorization Unit', 'unit_type' => 'SCHOOL']);
        $grade = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '1', 'display_name' => 'Tingkat 1', 'sequence_no' => 1]);
        $year = AcademicYear::create(['year_code' => '2026-AUTHZ', 'display_name' => '2026/2027', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $semester = Semester::create(['academic_year_id' => $year->id, 'semester_code' => 'ODD', 'display_name' => 'Semester Ganjil', 'sequence_no' => 1, 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-31']);
        $class = AcademicClass::create(['class_code' => 'AUTHZ-A', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id, 'section_code' => 'A', 'display_name' => 'Kelas Auth A']);
        $otherClass = AcademicClass::create(['class_code' => 'AUTHZ-B', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id, 'section_code' => 'B', 'display_name' => 'Kelas Auth B']);
        $subject = Subject::create(['subject_code' => 'AUTHZ-SUBJ', 'subject_name' => 'Authorization']);
        $teacher = Staff::create(['staff_code' => 'AUTHZ-TEACHER', 'full_name' => 'Teacher']);
        $waliStaff = Staff::create(['staff_code' => 'AUTHZ-WALI', 'full_name' => 'Wali']);
        $user = User::factory()->create();
        $role = Role::create(['code' => 'WALI_KELAS', 'name' => 'Wali Kelas']);
        UserRoleAssignment::create(['user_id' => $user->id, 'role_id' => $role->id, 'effective_from' => '2026-07-01']);
        UserStaffLink::create(['user_id' => $user->id, 'staff_id' => $waliStaff->id, 'effective_from' => '2026-07-01']);
        ClassHomeroomAssignment::create(['class_id' => $class->id, 'staff_id' => $waliStaff->id, 'effective_from' => '2026-07-01']);
        $assignment = TeachingAssignment::create(['assignment_code' => 'AUTHZ-TA-A', 'semester_id' => $semester->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $teacher->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);
        $otherAssignment = TeachingAssignment::create(['assignment_code' => 'AUTHZ-TA-B', 'semester_id' => $semester->id, 'class_id' => $otherClass->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $teacher->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);
        $session = ClassSession::create(['session_code' => 'AUTHZ-SESSION-A', 'teaching_assignment_id' => $assignment->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'planned_start_at' => '2026-07-15 08:00:00', 'planned_end_at' => '2026-07-15 09:30:00', 'session_source' => 'SCHEDULED', 'participant_scope' => 'FULL_CLASS', 'session_status' => 'PLANNED']);
        $otherSession = ClassSession::create(['session_code' => 'AUTHZ-SESSION-B', 'teaching_assignment_id' => $otherAssignment->id, 'class_id' => $otherClass->id, 'subject_id' => $subject->id, 'planned_start_at' => '2026-07-15 08:00:00', 'planned_end_at' => '2026-07-15 09:30:00', 'session_source' => 'SCHEDULED', 'participant_scope' => 'FULL_CLASS', 'session_status' => 'PLANNED']);

        return [$user, $session, $otherSession];
    }
}
