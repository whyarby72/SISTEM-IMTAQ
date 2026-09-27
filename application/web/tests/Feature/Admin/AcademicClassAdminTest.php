<?php

namespace Tests\Feature\Admin;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\GradeLevel;
use App\Models\User;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use App\Shared\Platform\Authorization\Models\Role;
use App\Shared\Platform\Authorization\Models\UserRoleAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicClassAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_academic_admin_can_view_and_create_a_class(): void
    {
        [$user, $year, $unit, $grade] = $this->adminFixture();

        $this->actingAs($user)->get(route('admin.academic.classes.index'))->assertOk()->assertSee('Data Kelas');
        $this->actingAs($user)->post(route('admin.academic.classes.store'), [
            'class_code' => 'CLASS-ADMIN-01', 'display_name' => 'Kelas Admin 01', 'section_code' => 'A',
            'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id,
        ])->assertRedirect(route('admin.academic.classes.index'));

        $this->assertDatabaseHas('classes', ['class_code' => 'CLASS-ADMIN-01', 'display_name' => 'Kelas Admin 01']);
    }

    public function test_non_admin_cannot_manage_classes(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('admin.academic.classes.index'))->assertForbidden();
    }

    public function test_academic_admin_can_edit_only_safe_class_fields(): void
    {
        [$user, $year, $unit, $grade] = $this->adminFixture();
        $this->actingAs($user)->post(route('admin.academic.classes.store'), [
            'class_code' => 'CLASS-ADMIN-EDIT', 'display_name' => 'Kelas Lama', 'section_code' => 'A',
            'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id,
        ]);
        $class = AcademicClass::where('class_code', 'CLASS-ADMIN-EDIT')->firstOrFail();

        $this->actingAs($user)->get(route('admin.academic.classes.edit', $class))->assertOk()->assertSee('Identitas kode dan struktur kelas dikunci');
        $this->actingAs($user)->put(route('admin.academic.classes.update', $class), ['display_name' => 'Kelas Baru', 'status' => 'INACTIVE'])
            ->assertRedirect(route('admin.academic.classes.index'));

        $this->assertDatabaseHas('classes', ['id' => $class->id, 'class_code' => 'CLASS-ADMIN-EDIT', 'display_name' => 'Kelas Baru', 'status' => 'INACTIVE']);
    }

    private function adminFixture(): array
    {
        $user = User::factory()->create();
        $role = Role::create(['code' => 'WAKA_AKADEMIK', 'name' => 'Waka Akademik']);
        UserRoleAssignment::create(['user_id' => $user->id, 'role_id' => $role->id, 'effective_from' => '2026-07-01']);
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-ADMIN-CLASS', 'unit_name' => 'Unit Admin Class', 'unit_type' => 'SCHOOL']);
        $year = AcademicYear::create(['year_code' => 'YEAR-ADMIN-CLASS', 'display_name' => '2026/2027', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $grade = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '3', 'display_name' => 'Tingkat 3', 'sequence_no' => 3]);

        return [$user, $year, $unit, $grade];
    }
}
