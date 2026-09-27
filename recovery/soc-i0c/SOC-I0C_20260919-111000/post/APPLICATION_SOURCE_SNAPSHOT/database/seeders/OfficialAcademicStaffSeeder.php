<?php

namespace Database\Seeders;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\ClassHomeroomAssignment;
use App\Models\User;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\Staff;
use App\Shared\Platform\Authorization\Models\Role;
use App\Shared\Platform\Authorization\Models\UserRoleAssignment;
use App\Shared\Platform\Authorization\Models\UserStaffLink;
use Illuminate\Database\Seeder;

class OfficialAcademicStaffSeeder extends Seeder
{
    public function run(): void
    {
        $staff = collect([
            'ALW' => 'Ust. Alwan',
            'AFW' => 'Ust. Afwa',
            'AZH' => 'Ust. Azhar',
            'MLN' => 'Ust. Maulana',
            'WAWAN' => 'Ust. Wawan',
        ])->mapWithKeys(fn (string $name, string $code) => [
            $code => Staff::query()->updateOrCreate(
                ['staff_code' => $code],
                ['full_name' => $name, 'record_status' => 'ACTIVE', 'active_from' => '2026-07-01', 'version_no' => 1]
            ),
        ]);

        $year = AcademicYear::query()->where('year_code', '2026/2027')->firstOrFail();
        $assignments = [
            'IMTAQ-2026-1' => 'ALW',
            'IMTAQ-2026-2A' => 'AFW',
            'IMTAQ-2026-2B' => 'MLN',
            'IMTAQ-2026-3A' => 'AZH',
            'IMTAQ-2026-3B' => 'MLN',
        ];

        foreach ($assignments as $classCode => $staffCode) {
            $class = AcademicClass::query()->where('academic_year_id', $year->id)->where('class_code', $classCode)->firstOrFail();
            ClassHomeroomAssignment::query()->firstOrCreate(
                ['class_id' => $class->id, 'staff_id' => $staff[$staffCode]->id, 'effective_from' => '2026-07-01'],
                ['status' => 'ACTIVE', 'assigned_at' => now(), 'reason' => 'MASTER-DATA-OWNER-APPROVED', 'version_no' => 1]
            );
        }

        $wakaUser = User::query()->updateOrCreate(
            ['email' => 'whyarby72@gmail.com'],
            ['name' => 'Ust. Wawan', 'password' => 'password']
        );
        $wakaRole = Role::query()->firstOrCreate(
            ['code' => 'WAKA_AKADEMIK'],
            ['name' => 'Waka Akademik', 'is_system' => true]
        );
        UserStaffLink::query()->firstOrCreate(
            ['user_id' => $wakaUser->id],
            ['staff_id' => $staff['WAWAN']->id, 'effective_from' => '2026-07-01']
        );
        UserRoleAssignment::query()->firstOrCreate(
            ['user_id' => $wakaUser->id, 'role_id' => $wakaRole->id, 'scope_type' => 'INSTITUTION'],
            ['effective_from' => '2026-07-01', 'assignment_reason' => 'MASTER-DATA-OWNER-APPROVED', 'version_no' => 1]
        );
    }
}
