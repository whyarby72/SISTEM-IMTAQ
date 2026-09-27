<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\GradeLevel;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Models\TeachingAssignment;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use App\Shared\Core\Models\Staff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use Tests\TestCase;

class TeachingAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_teaching_assignment_and_semester_tables_have_canonical_references(): void
    {
        foreach (['semesters', 'teaching_assignments'] as $table) {
            $this->assertTrue(Schema::hasTable($table));
        }
        foreach (['semester_id', 'class_id', 'subject_id', 'teacher_staff_id', 'effective_from', 'workflow_status'] as $column) {
            $this->assertTrue(Schema::hasColumn('teaching_assignments', $column));
        }
    }

    public function test_assignment_links_teacher_class_subject_and_semester(): void
    {
        [$semester, $class, $subject, $staff] = $this->fixtures();
        $assignment = TeachingAssignment::create([
            'assignment_code' => 'TA-2026-FIQH-1A',
            'semester_id' => $semester->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'teacher_staff_id' => $staff->id,
            'effective_from' => '2026-07-01',
            'workflow_status' => 'ACTIVE',
        ]);

        $this->assertSame($semester->id, $assignment->semester->id);
        $this->assertSame($class->id, $assignment->academicClass->id);
        $this->assertSame($subject->id, $assignment->subject->id);
        $this->assertSame($staff->id, $assignment->teacher->id);
    }

    public function test_assignment_rejects_non_positive_effective_interval(): void
    {
        [$semester, $class, $subject, $staff] = $this->fixtures();

        $this->expectException(InvalidArgumentException::class);
        TeachingAssignment::create([
            'assignment_code' => 'TA-INVALID',
            'semester_id' => $semester->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'teacher_staff_id' => $staff->id,
            'effective_from' => '2026-07-01',
            'effective_until' => '2026-07-01',
            'workflow_status' => 'DRAFT',
        ]);
    }

    private function fixtures(): array
    {
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-TEACHING', 'unit_name' => 'Teaching Unit', 'unit_type' => 'SCHOOL']);
        $grade = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '1', 'display_name' => 'Tingkat 1', 'sequence_no' => 1]);
        $year = AcademicYear::create(['year_code' => '2026-TEACHING', 'display_name' => '2026/2027', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $semester = Semester::create(['academic_year_id' => $year->id, 'semester_code' => 'ODD', 'display_name' => 'Semester Ganjil', 'sequence_no' => 1, 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-31']);
        $class = AcademicClass::create(['class_code' => 'CLASS-TEACHING-A', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id, 'section_code' => 'A', 'display_name' => 'Kelas 1 A']);
        $subject = Subject::create(['subject_code' => 'SUBJ-FIQH', 'subject_name' => 'Fiqh']);
        $staff = Staff::create(['staff_code' => 'STAFF-TEACHING-001', 'full_name' => 'Teaching Staff']);

        return [$semester, $class, $subject, $staff];
    }
}
