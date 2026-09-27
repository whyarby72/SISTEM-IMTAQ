<?php

namespace Tests\Feature\Admin;

use App\Models\User;
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
        $role = Role::create(['code' => 'ADMIN_AKADEMIK', 'name' => 'Admin Akademik']);
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

    public function test_academic_admin_can_open_add_student_form(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['code' => 'ADMIN_AKADEMIK', 'name' => 'Admin Akademik']);
        UserRoleAssignment::create(['user_id' => $user->id, 'role_id' => $role->id, 'effective_from' => '2026-07-01']);

        $this->actingAs($user)->get(route('admin.academic.students.create'))
            ->assertOk()
            ->assertSee('Tambah Santri')
            ->assertSee('ID internal dibuat otomatis');
    }

    public function test_academic_admin_can_update_nis_and_nisn_without_changing_internal_identity(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['code' => 'ADMIN_AKADEMIK', 'name' => 'Admin Akademik']);
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
