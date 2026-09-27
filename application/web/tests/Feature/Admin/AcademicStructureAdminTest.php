<?php

namespace Tests\Feature\Admin;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\ClassHomeroomAssignment;
use App\Domains\Academic\Models\GradeLevel;
use App\Models\User;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use App\Shared\Core\Models\Staff;
use App\Shared\Platform\Authorization\Models\Role;
use App\Shared\Platform\Authorization\Models\UserRoleAssignment;
use Database\Seeders\OfficialAcademicStaffSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicStructureAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_grade_level_and_homeroom_assignment(): void
    {
        [$user, $year, $unit] = $this->fixture();
        $staff = Staff::create(['staff_code' => 'STAFF-ALWAN', 'full_name' => 'Ust. Alwan', 'record_status' => 'ACTIVE']);
        $this->actingAs($user)->post(route('admin.academic.structure.grade-levels.store'), ['organizational_unit_id' => $unit->id, 'level_code' => '1', 'display_name' => 'Tingkat 1', 'sequence_no' => 1])->assertRedirect(route('admin.academic.structure.index'));
        $grade = GradeLevel::where('level_code', '1')->firstOrFail();
        $class = AcademicClass::create(['class_code' => '1', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id, 'section_code' => '1', 'display_name' => 'Kelas 1']);

        $this->actingAs($user)->post(route('admin.academic.structure.homerooms.store'), ['class_id' => $class->id, 'staff_id' => $staff->id, 'effective_from' => '2026-07-01', 'reason' => 'MASTER-DATA-ADMIN'])->assertRedirect(route('admin.academic.structure.index'));
        $this->assertDatabaseHas('class_homeroom_assignments', ['class_id' => $class->id, 'staff_id' => $staff->id, 'status' => 'ACTIVE']);
    }

    public function test_non_admin_cannot_manage_academic_structure(): void
    {
        $this->actingAs(User::factory()->create())->get(route('admin.academic.structure.index'))->assertForbidden();
    }

    public function test_structure_staff_picker_excludes_pilot_staff(): void
    {
        [$user, $year, $unit] = $this->fixture();
        $grade = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '1', 'display_name' => 'Tingkat 1', 'sequence_no' => 1]);
        AcademicClass::create(['class_code' => '1', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id, 'section_code' => '1', 'display_name' => 'Kelas 1']);
        Staff::create(['staff_code' => 'STAFF-OFFICIAL', 'full_name' => 'Guru Resmi', 'record_status' => 'ACTIVE']);
        Staff::create(['staff_code' => 'PILOT-GURU', 'full_name' => 'Guru Pilot', 'record_status' => 'ACTIVE']);

        $this->actingAs($user)->get(route('admin.academic.structure.index'))
            ->assertOk()
            ->assertSee('Guru Resmi')
            ->assertDontSee('Guru Pilot');
    }

    public function test_official_staff_seeder_maps_homerooms_without_touching_pilot_records(): void
    {
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-OFFICIAL', 'unit_name' => 'IMTAQ ISY KARIMA', 'unit_type' => 'SCHOOL']);
        $year = AcademicYear::create(['year_code' => '2026/2027', 'display_name' => 'Tahun Ajaran 2026/2027', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $levels = collect([1, 2, 3])->mapWithKeys(fn (int $level) => [$level => GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => (string) $level, 'display_name' => 'Tingkat '.$level, 'sequence_no' => $level])]);
        foreach (['1' => 1, '2A' => 2, '2B' => 2, '3A' => 3, '3B' => 3] as $code => $level) {
            AcademicClass::create(['class_code' => 'IMTAQ-2026-'.$code, 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $levels[$level]->id, 'section_code' => $code, 'display_name' => 'Kelas '.$code]);
        }
        Staff::create(['staff_code' => 'PILOT-ALWAN', 'full_name' => 'Ust. Alwan', 'record_status' => 'ACTIVE']);

        $this->seed(OfficialAcademicStaffSeeder::class);

        $this->assertDatabaseHas('staff', ['staff_code' => 'ALW', 'full_name' => 'Ust. Alwan']);
        $this->assertDatabaseHas('staff', ['staff_code' => 'MLN', 'full_name' => 'Ust. Maulana']);
        $this->assertDatabaseHas('class_homeroom_assignments', ['reason' => 'MASTER-DATA-OWNER-APPROVED']);
        $this->assertDatabaseHas('staff', ['staff_code' => 'PILOT-ALWAN', 'full_name' => 'Ust. Alwan']);
        $this->assertDatabaseHas('users', ['email' => 'whyarby72@gmail.com', 'name' => 'Ust. Wawan']);
        $this->assertDatabaseHas('user_staff_links', ['staff_id' => Staff::where('staff_code', 'WAWAN')->value('id')]);
        $this->assertDatabaseHas('user_role_assignments', ['user_id' => User::where('email', 'whyarby72@gmail.com')->value('id')]);
    }

    public function test_admin_can_update_homeroom_without_creating_an_overlap(): void
    {
        [$user, $year, $unit] = $this->fixture();
        $grade = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '3', 'display_name' => 'Tingkat 3', 'sequence_no' => 3]);
        $class = AcademicClass::create(['class_code' => '3B', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id, 'section_code' => 'B', 'display_name' => 'Kelas 3B']);
        $old = Staff::create(['staff_code' => 'STAFF-OLD', 'full_name' => 'Ust. Lama', 'record_status' => 'ACTIVE']);
        $new = Staff::create(['staff_code' => 'STAFF-NEW', 'full_name' => 'Ust. Maulana', 'record_status' => 'ACTIVE']);
        $assignment = ClassHomeroomAssignment::create(['class_id' => $class->id, 'staff_id' => $old->id, 'effective_from' => '2026-07-01', 'reason' => 'SEED']);

        $this->actingAs($user)->put(route('admin.academic.structure.homerooms.update', $assignment), ['staff_id' => $new->id, 'effective_from' => '2026-07-01', 'reason' => 'PERUBAHAN-WALI-KELAS'])->assertRedirect(route('admin.academic.structure.index'));
        $this->assertDatabaseHas('class_homeroom_assignments', ['id' => $assignment->id, 'staff_id' => $new->id]);
        $this->assertDatabaseCount('class_homeroom_assignments', 1);
    }

    public function test_homeroom_dates_accept_indonesian_day_month_year_format(): void
    {
        [$user, $year, $unit] = $this->fixture();
        $grade = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '1', 'display_name' => 'Tingkat 1', 'sequence_no' => 1]);
        $class = AcademicClass::create(['class_code' => 'CLASS-DATE-FORMAT', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id, 'section_code' => 'A', 'display_name' => 'Kelas Format Tanggal']);
        $staff = Staff::create(['staff_code' => 'STAFF-DATE-FORMAT', 'full_name' => 'Guru Format Tanggal']);

        $this->actingAs($user)->post(route('admin.academic.structure.homerooms.store'), [
            'class_id' => $class->id, 'staff_id' => $staff->id,
            'effective_from' => '28/07/2026', 'effective_until' => '31/07/2026',
            'reason' => 'MASTER-DATA-ADMIN',
        ])->assertRedirect(route('admin.academic.structure.index'));

        $this->assertDatabaseHas('class_homeroom_assignments', [
            'class_id' => $class->id, 'effective_from' => '2026-07-28 00:00:00', 'effective_until' => '2026-07-31 00:00:00',
        ]);
    }

    private function fixture(): array
    {
        $user = User::factory()->create();
        $role = Role::create(['code' => 'WAKA_AKADEMIK', 'name' => 'Waka Akademik']);
        UserRoleAssignment::create(['user_id' => $user->id, 'role_id' => $role->id, 'effective_from' => '2026-07-01']);
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-STRUCTURE', 'unit_name' => 'Unit Struktur', 'unit_type' => 'SCHOOL']);
        $year = AcademicYear::create(['year_code' => 'YEAR-STRUCTURE', 'display_name' => '2026/2027', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);

        return [$user, $year, $unit];
    }
}
