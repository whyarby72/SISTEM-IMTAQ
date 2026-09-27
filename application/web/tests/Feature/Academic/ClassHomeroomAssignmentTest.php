<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\ClassHomeroomAssignment;
use App\Domains\Academic\Models\GradeLevel;
use App\Domains\Academic\Services\HomeroomScopeResolver;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use App\Shared\Core\Models\Staff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use Tests\TestCase;

class ClassHomeroomAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_assignment_is_effective_dated_and_class_has_no_permanent_homeroom_field(): void
    {
        $this->assertTrue(Schema::hasTable('class_homeroom_assignments'));
        $this->assertTrue(Schema::hasColumn('class_homeroom_assignments', 'effective_from'));
        $this->assertTrue(Schema::hasColumn('class_homeroom_assignments', 'effective_until'));
        $this->assertFalse(Schema::hasColumn('classes', 'homeroom_staff_id'));
    }

    public function test_scope_resolver_returns_only_current_active_classes_for_staff(): void
    {
        [$class, $staff] = $this->fixtures();
        ClassHomeroomAssignment::create([
            'class_id' => $class->id, 'staff_id' => $staff->id,
            'effective_from' => '2026-07-01', 'effective_until' => '2027-01-15',
        ]);

        $resolver = app(HomeroomScopeResolver::class);
        $this->assertTrue($resolver->classIdsForStaff($staff->id, '2026-08-01')->contains($class->id));
        $this->assertFalse($resolver->classIdsForStaff($staff->id, '2027-01-15')->contains($class->id));
    }

    public function test_overlapping_assignments_for_one_class_are_rejected(): void
    {
        [$class, $staff] = $this->fixtures();
        ClassHomeroomAssignment::create([
            'class_id' => $class->id, 'staff_id' => $staff->id,
            'effective_from' => '2026-07-01', 'effective_until' => '2027-01-15',
        ]);

        $this->expectException(InvalidArgumentException::class);
        ClassHomeroomAssignment::create([
            'class_id' => $class->id, 'staff_id' => $staff->id,
            'effective_from' => '2027-01-01', 'effective_until' => '2027-06-30',
        ]);
    }

    private function fixtures(): array
    {
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-HR', 'unit_name' => 'Homeroom Unit', 'unit_type' => 'SCHOOL']);
        $grade = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '1', 'display_name' => 'Tingkat 1', 'sequence_no' => 1]);
        $year = AcademicYear::create(['year_code' => '2026-HR', 'display_name' => '2026/2027', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $class = AcademicClass::create([
            'class_code' => 'CLASS-HR-A', 'academic_year_id' => $year->id,
            'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id,
            'section_code' => 'A', 'display_name' => 'Kelas 1 A',
        ]);
        $staff = Staff::create(['staff_code' => 'STAFF-HR-001', 'full_name' => 'Homeroom Staff']);

        return [$class, $staff];
    }
}
