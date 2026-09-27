<?php

namespace Database\Seeders;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\GradeLevel;
use App\Domains\Academic\Models\Semester;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use Illuminate\Database\Seeder;

class OfficialAcademicStructure2026Seeder extends Seeder
{
    public function run(): void
    {
        $unit = OrganizationalUnit::query()->updateOrCreate(
            ['unit_code' => 'IMTAQ-ISY-KARIMA'],
            ['unit_name' => 'IMTAQ ISY KARIMA', 'unit_type' => 'SCHOOL', 'record_status' => 'ACTIVE', 'version_no' => 1]
        );

        $year = AcademicYear::query()->updateOrCreate(
            ['year_code' => '2026/2027'],
            ['display_name' => 'Tahun Ajaran 2026/2027', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30', 'status' => 'ACTIVE', 'version_no' => 1]
        );

        Semester::query()->updateOrCreate(
            ['academic_year_id' => $year->id, 'semester_code' => 'S1-2026-2027'],
            ['display_name' => 'Semester 1', 'sequence_no' => 1, 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-31', 'status' => 'ACTIVE', 'version_no' => 1]
        );

        Semester::query()->updateOrCreate(
            ['academic_year_id' => $year->id, 'semester_code' => 'S2-2026-2027'],
            ['display_name' => 'Semester 2', 'sequence_no' => 2, 'starts_on' => '2027-01-01', 'ends_on' => '2027-06-30', 'status' => 'ACTIVE', 'version_no' => 1]
        );

        $grades = [];
        foreach ([1, 2, 3] as $level) {
            $grades[$level] = GradeLevel::query()->updateOrCreate(
                ['organizational_unit_id' => $unit->id, 'level_code' => (string) $level],
                ['display_name' => 'Tingkat '.$level, 'sequence_no' => $level, 'status' => 'ACTIVE', 'version_no' => 1]
            );
        }

        foreach ([
            ['code' => 'IMTAQ-2026-1', 'display' => 'Kelas 1', 'grade' => 1, 'section' => '1'],
            ['code' => 'IMTAQ-2026-2A', 'display' => 'Kelas 2A', 'grade' => 2, 'section' => 'A'],
            ['code' => 'IMTAQ-2026-2B', 'display' => 'Kelas 2B', 'grade' => 2, 'section' => 'B'],
            ['code' => 'IMTAQ-2026-3A', 'display' => 'Kelas 3A', 'grade' => 3, 'section' => 'A'],
            ['code' => 'IMTAQ-2026-3B', 'display' => 'Kelas 3B', 'grade' => 3, 'section' => 'B'],
        ] as $class) {
            AcademicClass::query()->updateOrCreate(
                ['class_code' => $class['code']],
                ['academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grades[$class['grade']]->id, 'section_code' => $class['section'], 'display_name' => $class['display'], 'status' => 'ACTIVE', 'version_no' => 1]
            );
        }
    }
}
