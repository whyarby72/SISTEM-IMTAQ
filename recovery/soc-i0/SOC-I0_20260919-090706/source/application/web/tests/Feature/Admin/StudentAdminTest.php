<?php

namespace Tests\Feature\Admin;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\GradeLevel;
use App\Domains\Academic\Models\StudentClassEnrollment;
use App\Models\User;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use App\Shared\Core\Models\Student;
use App\Shared\Platform\Authorization\Models\Role;
use App\Shared\Platform\Authorization\Models\UserRoleAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_academic_admin_can_open_master_student_list(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['code' => 'WAKA_AKADEMIK', 'name' => 'Waka Akademik']);
        UserRoleAssignment::create([
            'user_id' => $user->id,
            'role_id' => $role->id,
            'effective_from' => '2026-07-01',
        ]);

        $this->actingAs($user)->get(route('admin.academic.students.index'))
            ->assertOk()
            ->assertSee('Data Santri')
            ->assertSee('NIS/NISN dapat dilengkapi kemudian')
            ->assertSee('Cari nama')
            ->assertSee('Semua kelas')
            ->assertSee('Tambah santri');
    }

    public function test_non_admin_cannot_open_master_student_list(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.academic.students.index'))
            ->assertForbidden();
    }

    public function test_master_student_list_includes_reconciled_official_roster_enrollment(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['code' => 'WAKA_AKADEMIK', 'name' => 'Waka Akademik']);
        UserRoleAssignment::create(['user_id' => $user->id, 'role_id' => $role->id, 'effective_from' => '2026-07-01']);
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-STUDENT-LIST', 'unit_name' => 'Student List Unit', 'unit_type' => 'SCHOOL']);
        $year = AcademicYear::create(['year_code' => '2026/2027', 'display_name' => '2026/2027', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $grade = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '1', 'display_name' => 'Tingkat 1', 'sequence_no' => 1]);
        $class = AcademicClass::create(['class_code' => 'IMTAQ-2026-1', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id, 'section_code' => '1', 'display_name' => 'Kelas 1']);
        $student = Student::create(['student_code' => null, 'full_name' => 'Santri Rekonsiliasi']);
        StudentClassEnrollment::create(['student_id' => $student->id, 'class_id' => $class->id, 'effective_from' => '2026-07-01', 'reason' => 'RECONCILIATION-OFFICIAL-2026']);

        $this->actingAs($user)->get(route('admin.academic.students.index'))
            ->assertOk()
            ->assertSee('Santri Rekonsiliasi')
            ->assertSee('Kelas 1');
    }

    public function test_academic_admin_can_open_add_student_form(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['code' => 'WAKA_AKADEMIK', 'name' => 'Waka Akademik']);
        UserRoleAssignment::create(['user_id' => $user->id, 'role_id' => $role->id, 'effective_from' => '2026-07-01']);

        $this->actingAs($user)->get(route('admin.academic.students.create'))
            ->assertOk()
            ->assertSee('Tambah Santri')
            ->assertSee('ID internal dibuat otomatis');
    }

    public function test_academic_admin_can_update_nis_and_nisn_without_changing_internal_identity(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['code' => 'WAKA_AKADEMIK', 'name' => 'Waka Akademik']);
        UserRoleAssignment::create(['user_id' => $user->id, 'role_id' => $role->id, 'effective_from' => '2026-07-01']);
        $student = Student::create(['student_code' => null, 'full_name' => 'Santri Uji']);
        $internalId = $student->id;

        $this->actingAs($user)->put(route('admin.academic.students.update', $student), [
            'nis' => 'NIS-001',
            'nisn' => '0012345678',
        ])->assertRedirect(route('admin.academic.students.index'));

        $student->refresh();
        $this->assertSame($internalId, $student->id);
        $this->assertSame('NIS-001', $student->nis);
        $this->assertSame('0012345678', $student->nisn);
    }
}
