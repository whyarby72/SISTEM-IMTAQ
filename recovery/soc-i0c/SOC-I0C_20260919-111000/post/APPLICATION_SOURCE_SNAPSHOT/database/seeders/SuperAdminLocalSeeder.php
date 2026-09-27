<?php

namespace Database\Seeders;

use App\Models\User;
use App\Shared\Platform\Authorization\Models\Role;
use App\Shared\Platform\Authorization\Models\UserRoleAssignment;
use Illuminate\Database\Seeder;

class SuperAdminLocalSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'superadmin.pilot@example.test'],
            ['name' => 'Super Admin', 'password' => 'password']
        );
        $role = Role::firstOrCreate(['code' => 'SUPER_ADMIN'], ['name' => 'Super Admin', 'is_system' => true]);
        UserRoleAssignment::firstOrCreate(
            ['user_id' => $user->id, 'role_id' => $role->id, 'scope_type' => 'INSTITUTION'],
            ['effective_from' => '2026-07-01', 'assignment_reason' => 'LOCAL-ADMIN-SETUP']
        );
    }
}
