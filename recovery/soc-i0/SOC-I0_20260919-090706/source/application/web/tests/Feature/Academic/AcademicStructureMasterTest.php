<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\GradeLevel;
use App\Domains\Academic\Models\Subject;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AcademicStructureMasterTest extends TestCase
{
    use RefreshDatabase;

    public function test_academic_structure_master_tables_and_fields_exist(): void
    {
        foreach (['academic_years', 'grade_levels', 'classes', 'subjects'] as $table) {
            $this->assertTrue(Schema::hasTable($table));
        }

        $this->assertTrue(Schema::hasColumn('classes', 'academic_year_id'));
        $this->assertTrue(Schema::hasColumn('classes', 'grade_level_id'));
        $this->assertTrue(Schema::hasColumn('classes', 'section_code'));
        $this->assertTrue(Schema::hasColumn('subjects', 'subject_code'));
    }

    public function test_class_sections_are_data_driven_and_year_specific(): void
    {
        $unit = OrganizationalUnit::create([
            'unit_code' => 'UNIT-ACADEMIC',
            'unit_name' => 'Academic Unit',
            'unit_type' => 'SCHOOL',
        ]);
        $grade = GradeLevel::create([
            'organizational_unit_id' => $unit->id,
            'level_code' => '1',
            'display_name' => 'Tingkat 1',
            'sequence_no' => 1,
        ]);
        $yearOne = AcademicYear::create([
            'year_code' => '2026-2027',
            'display_name' => '2026/2027',
            'starts_on' => '2026-07-01',
            'ends_on' => '2027-06-30',
        ]);
        $yearTwo = AcademicYear::create([
            'year_code' => '2027-2028',
            'display_name' => '2027/2028',
            'starts_on' => '2027-07-01',
            'ends_on' => '2028-06-30',
        ]);

        $classA = AcademicClass::create([
            'class_code' => 'CLASS-2026-1A',
            'academic_year_id' => $yearOne->id,
            'organizational_unit_id' => $unit->id,
            'grade_level_id' => $grade->id,
            'section_code' => 'A',
            'display_name' => 'Kelas 1 A',
        ]);
        $classC = AcademicClass::create([
            'class_code' => 'CLASS-2026-1C',
            'academic_year_id' => $yearOne->id,
            'organizational_unit_id' => $unit->id,
            'grade_level_id' => $grade->id,
            'section_code' => 'C',
            'display_name' => 'Kelas 1 C',
        ]);
        $nextYearA = AcademicClass::create([
            'class_code' => 'CLASS-2027-1A',
            'academic_year_id' => $yearTwo->id,
            'organizational_unit_id' => $unit->id,
            'grade_level_id' => $grade->id,
            'section_code' => 'A',
            'display_name' => 'Kelas 1 A',
        ]);

        $this->assertSame('A', $classA->section_code);
        $this->assertSame('C', $classC->section_code);
        $this->assertNotSame($classA->id, $nextYearA->id);
        $this->assertSame($yearOne->id, $classA->academicYear->id);
    }

    public function test_duplicate_class_section_within_same_year_unit_and_grade_is_blocked(): void
    {
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-DQ', 'unit_name' => 'DQ Unit', 'unit_type' => 'SCHOOL']);
        $grade = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '2', 'display_name' => 'Tingkat 2', 'sequence_no' => 2]);
        $year = AcademicYear::create(['year_code' => '2026-DQ', 'display_name' => '2026 DQ', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $attributes = [
            'academic_year_id' => $year->id,
            'organizational_unit_id' => $unit->id,
            'grade_level_id' => $grade->id,
            'section_code' => 'B',
            'display_name' => 'Tingkat 2 B',
        ];

        AcademicClass::create(['class_code' => 'CLASS-DQ-1', ...$attributes]);
        $this->expectException(QueryException::class);
        AcademicClass::create(['class_code' => 'CLASS-DQ-2', ...$attributes]);
    }

    public function test_subject_master_does_not_encode_semester_or_score_fields(): void
    {
        $subject = Subject::create(['subject_code' => 'FIQH', 'subject_name' => 'Fiqh']);

        $this->assertNotEmpty($subject->id);
        $this->assertFalse(Schema::hasColumn('subjects', 'semester'));
        $this->assertFalse(Schema::hasColumn('subjects', 'score'));
    }
}
