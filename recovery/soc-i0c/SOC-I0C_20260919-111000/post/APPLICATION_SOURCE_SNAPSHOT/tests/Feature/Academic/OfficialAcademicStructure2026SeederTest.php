<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\Semester;
use Database\Seeders\OfficialAcademicStructure2026Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfficialAcademicStructure2026SeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_official_structure_is_seeded_idempotently(): void
    {
        $this->seed(OfficialAcademicStructure2026Seeder::class);
        $this->seed(OfficialAcademicStructure2026Seeder::class);

        $this->assertDatabaseHas('organizational_units', ['unit_code' => 'IMTAQ-ISY-KARIMA', 'unit_name' => 'IMTAQ ISY KARIMA']);
        $this->assertDatabaseHas('academic_years', ['year_code' => '2026/2027', 'display_name' => 'Tahun Ajaran 2026/2027']);
        $this->assertSame(1, Semester::where('semester_code', 'S1-2026-2027')->count());
        $this->assertSame(5, AcademicClass::whereIn('class_code', ['IMTAQ-2026-1', 'IMTAQ-2026-2A', 'IMTAQ-2026-2B', 'IMTAQ-2026-3A', 'IMTAQ-2026-3B'])->count());
        $this->assertSame(0, AcademicClass::where('display_name', 'like', '%Pilot%')->count());
    }
}
