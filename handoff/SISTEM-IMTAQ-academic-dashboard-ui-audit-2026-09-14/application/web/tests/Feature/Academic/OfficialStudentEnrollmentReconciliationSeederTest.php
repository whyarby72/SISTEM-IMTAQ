<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\GradeLevel;
use App\Domains\Academic\Models\StudentClassEnrollment;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use App\Shared\Core\Models\Student;
use Database\Seeders\OfficialStudentEnrollmentReconciliationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfficialStudentEnrollmentReconciliationSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_master_enrollment_moves_to_official_section_and_sample_stays_pilot(): void
    {
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-RECON', 'unit_name' => 'IMTAQ ISY KARIMA', 'unit_type' => 'SCHOOL']);
        $grade = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '3', 'display_name' => 'Tingkat 3', 'sequence_no' => 3]);
        $pilotYear = AcademicYear::create(['year_code' => '2026/2027-PILOT', 'display_name' => 'Pilot', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $officialYear = AcademicYear::create(['year_code' => '2026/2027', 'display_name' => 'Resmi', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $pilotClass = AcademicClass::create(['class_code' => '3A', 'academic_year_id' => $pilotYear->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id, 'section_code' => '3A', 'display_name' => 'Kelas 3A Pilot']);
        $officialClass = AcademicClass::create(['class_code' => 'IMTAQ-2026-3A', 'academic_year_id' => $officialYear->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id, 'section_code' => '3A', 'display_name' => 'Kelas 3A']);
        $master = Student::create(['student_code' => 'JUL26-001', 'full_name' => 'Santri Master']);
        $sample = Student::create(['student_code' => 'PILOT-3A-01', 'full_name' => 'Santri Sample']);
        $masterEnrollment = StudentClassEnrollment::create(['student_id' => $master->id, 'class_id' => $pilotClass->id, 'effective_from' => '2026-07-01', 'source_reference' => 'IMTAQ-MASTER-2026-07:JUL26-001']);
        $sampleEnrollment = StudentClassEnrollment::create(['student_id' => $sample->id, 'class_id' => $pilotClass->id, 'effective_from' => '2026-07-01', 'source_reference' => 'SAMPLE-PILOT']);

        $this->seed(OfficialStudentEnrollmentReconciliationSeeder::class);
        $this->seed(OfficialStudentEnrollmentReconciliationSeeder::class);

        $this->assertDatabaseHas('student_class_enrollments', ['id' => $masterEnrollment->id, 'class_id' => $officialClass->id, 'source_reference' => 'IMTAQ-MASTER-2026-07:JUL26-001']);
        $this->assertDatabaseHas('student_class_enrollments', ['id' => $sampleEnrollment->id, 'class_id' => $pilotClass->id, 'source_reference' => 'SAMPLE-PILOT']);
        $this->assertDatabaseCount('student_class_enrollments', 2);
    }
}
