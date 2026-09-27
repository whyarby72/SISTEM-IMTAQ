<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\GradeLevel;
use App\Domains\Academic\Models\StudentClassEnrollment;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use App\Shared\Core\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use Tests\TestCase;

class StudentClassEnrollmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_enrollment_is_effective_dated_and_student_identity_has_no_class_field(): void
    {
        $this->assertTrue(Schema::hasTable('student_class_enrollments'));
        $this->assertTrue(Schema::hasColumn('student_class_enrollments', 'effective_from'));
        $this->assertTrue(Schema::hasColumn('student_class_enrollments', 'effective_until'));
        $this->assertFalse(Schema::hasColumn('students', 'class_id'));
        $this->assertFalse(Schema::hasColumn('students', 'current_class_name'));
    }

    public function test_adjacent_enrollments_preserve_class_transfer_history(): void
    {
        [$student, $firstClass, $secondClass] = $this->fixtures();

        $first = StudentClassEnrollment::create([
            'student_id' => $student->id,
            'class_id' => $firstClass->id,
            'effective_from' => '2026-07-01',
            'effective_until' => '2027-01-15',
            'reason' => 'Initial placement',
        ]);
        $second = StudentClassEnrollment::create([
            'student_id' => $student->id,
            'class_id' => $secondClass->id,
            'effective_from' => '2027-01-15',
            'reason' => 'Mid-year transfer',
        ]);

        $this->assertSame($firstClass->id, $first->academicClass->id);
        $this->assertSame($secondClass->id, $second->academicClass->id);
        $this->assertCount(2, $student->classEnrollments);
    }

    public function test_overlapping_enrollment_for_one_student_is_rejected(): void
    {
        [$student, $firstClass, $secondClass] = $this->fixtures();
        StudentClassEnrollment::create([
            'student_id' => $student->id,
            'class_id' => $firstClass->id,
            'effective_from' => '2026-07-01',
            'effective_until' => '2027-01-15',
        ]);

        $this->expectException(InvalidArgumentException::class);
        StudentClassEnrollment::create([
            'student_id' => $student->id,
            'class_id' => $secondClass->id,
            'effective_from' => '2027-01-01',
            'effective_until' => '2027-06-30',
        ]);
    }

    private function fixtures(): array
    {
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-ENROLL', 'unit_name' => 'Enrollment Unit', 'unit_type' => 'SCHOOL']);
        $grade = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '1', 'display_name' => 'Tingkat 1', 'sequence_no' => 1]);
        $year = AcademicYear::create(['year_code' => '2026-ENROLL', 'display_name' => '2026/2027', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $student = Student::create(['student_code' => 'STU-ENROLL-001', 'full_name' => 'Enrollment Student']);
        $base = ['academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id];
        $firstClass = AcademicClass::create(['class_code' => 'CLASS-ENROLL-A', 'section_code' => 'A', 'display_name' => 'Kelas 1 A', ...$base]);
        $secondClass = AcademicClass::create(['class_code' => 'CLASS-ENROLL-B', 'section_code' => 'B', 'display_name' => 'Kelas 1 B', ...$base]);

        return [$student, $firstClass, $secondClass];
    }
}
