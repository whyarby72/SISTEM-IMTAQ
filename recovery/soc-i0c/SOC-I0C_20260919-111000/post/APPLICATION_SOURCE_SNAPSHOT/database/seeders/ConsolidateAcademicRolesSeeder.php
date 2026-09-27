<?php

namespace Database\Seeders;

use App\Shared\Platform\Authorization\Models\Permission;
use App\Shared\Platform\Authorization\Models\Role;
use Illuminate\Database\Seeder;

class ConsolidateAcademicRolesSeeder extends Seeder
{
    public function run(): void
    {
        $wakaRole = Role::query()->firstOrCreate(
            ['code' => 'WAKA_AKADEMIK'],
            ['name' => 'Waka Akademik', 'is_system' => true]
        );

        foreach ([
            ['code' => 'academic.dashboard.export', 'name' => 'Export Academic dashboard'],
            ['code' => 'academic.student_attendance.handover_complete', 'name' => 'Complete historical attendance handover'],
            ['code' => 'academic.attendance.source_certify', 'name' => 'Certify academic attendance source'],
            ['code' => 'academic.domain.manage', 'name' => 'Manage Academic domain'],
            ['code' => 'platform.institution.manage', 'name' => 'Manage enabled institution domains'],
        ] as $permissionData) {
            $permission = Permission::query()->firstOrCreate(
                ['code' => $permissionData['code']],
                ['name' => $permissionData['name']]
            );
            if ($permissionData['code'] !== 'platform.institution.manage') {
                $wakaRole->permissions()->syncWithoutDetaching($permission->id);
            }
        }

        $institutionPermission = Permission::query()->where('code', 'platform.institution.manage')->firstOrFail();
        $superAdminRole = Role::query()->where('code', 'SUPER_ADMIN')->first();
        $superAdminRole?->permissions()->syncWithoutDetaching($institutionPermission->id);

        // Legacy role/assignment cleanup is intentionally not part of authority bootstrap.
        // LEGACY_RBAC_CLEANUP_DEFERRED: any future cleanup requires a separate approved task.
    }
}
