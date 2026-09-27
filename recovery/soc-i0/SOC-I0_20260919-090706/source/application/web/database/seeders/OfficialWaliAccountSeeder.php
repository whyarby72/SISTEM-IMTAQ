<?php

namespace Database\Seeders;

use App\Models\User;
use App\Shared\Core\Models\Staff;
use App\Shared\Platform\Authorization\Models\Role;
use App\Shared\Platform\Authorization\Models\UserRoleAssignment;
use App\Shared\Platform\Authorization\Models\UserStaffLink;
use Illuminate\Database\Seeder;

class OfficialWaliAccountSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            'ALW' => ['name' => 'Ust. Alwan', 'email' => 'alwan.wali@example.test'],
            'AFW' => ['name' => 'Ust. Afwa', 'email' => 'afwa.wali@example.test'],
            'AZH' => ['name' => 'Ust. Azhar', 'email' => 'azhar.wali@example.test'],
            'MLN' => ['name' => 'Ust. Maulana', 'email' => 'maulana.wali@example.test'],
        ];

        $role = Role::query()->firstOrCreate(
            ['code' => 'WALI_KELAS'],
            ['name' => 'Wali Kelas', 'is_system' => true]
        );

        foreach ($accounts as $staffCode => $account) {
            $staff = Staff::query()->where('staff_code', $staffCode)->firstOrFail();
            $user = User::query()->updateOrCreate(
                ['email' => $account['email']],
                ['name' => $account['name'], 'password' => 'password']
            );

            UserStaffLink::query()->updateOrCreate(
                ['user_id' => $user->id],
                ['staff_id' => $staff->id, 'effective_from' => '2026-07-01', 'effective_until' => null]
            );
            UserRoleAssignment::query()->firstOrCreate(
                ['user_id' => $user->id, 'role_id' => $role->id, 'scope_type' => 'INSTITUTION'],
                ['effective_from' => '2026-07-01', 'assignment_reason' => 'LOCAL-DEV-OFFICIAL-WALI', 'version_no' => 1]
            );
        }
    }
}
