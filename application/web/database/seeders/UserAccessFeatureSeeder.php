<?php

namespace Database\Seeders;

use App\Shared\Platform\Authorization\Models\Feature;
use App\Shared\Platform\Authorization\Models\Permission;
use App\Shared\Platform\Authorization\Models\Role;
use Illuminate\Database\Seeder;

class UserAccessFeatureSeeder extends Seeder
{
    public function run(): void
    {
        $permission = Permission::query()->firstOrCreate(['code' => 'platform.user.manage'], ['name' => 'Manage user access and preferences']);
        Role::query()->where('code', 'SUPER_ADMIN')->first()?->permissions()->syncWithoutDetaching($permission->id);
        foreach ([
            ['code' => 'academic.dashboard', 'name' => 'Dashboard Akademik', 'module' => 'Academic', 'required_permission' => null],
            ['code' => 'academic.attendance', 'name' => 'Kontrol Kehadiran', 'module' => 'Academic', 'required_permission' => null],
            ['code' => 'academic.attendance_review', 'name' => 'Hasil dan Koreksi', 'module' => 'Academic', 'required_permission' => null],
            ['code' => 'academic.reports', 'name' => 'Laporan Akademik', 'module' => 'Academic', 'required_permission' => null],
            ['code' => 'academic.students', 'name' => 'Santri', 'module' => 'Academic', 'required_permission' => 'academic.domain.manage'],
            ['code' => 'academic.classes', 'name' => 'Kelas', 'module' => 'Academic', 'required_permission' => 'academic.domain.manage'],
            ['code' => 'academic.structure', 'name' => 'Struktur', 'module' => 'Academic', 'required_permission' => 'academic.domain.manage'],
            ['code' => 'academic.schedules', 'name' => 'Jadwal', 'module' => 'Academic', 'required_permission' => 'academic.domain.manage'],
            ['code' => 'academic.subjects', 'name' => 'Mata Pelajaran', 'module' => 'Academic', 'required_permission' => 'academic.domain.manage'],
            ['code' => 'academic.staff', 'name' => 'Guru dan Staf', 'module' => 'Academic', 'required_permission' => 'academic.domain.manage'],
            ['code' => 'platform.user_access', 'name' => 'User dan Akses', 'module' => 'Platform', 'required_permission' => 'platform.user.manage'],
            ['code' => 'platform.system_settings', 'name' => 'Pengaturan Sistem', 'module' => 'Platform', 'required_permission' => 'platform.institution.manage'],
            ['code' => 'ai.academic_assistant', 'name' => 'AI Academic Assistant', 'module' => 'AI', 'required_permission' => 'platform.institution.manage'],
        ] as $order => $data) {
            Feature::query()->updateOrCreate(['code' => $data['code']], [...$data, 'default_enabled' => true, 'system_enabled' => true, 'is_toggleable' => true, 'sort_order' => $order]);
        }
    }
}
