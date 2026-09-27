<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\GradeLevel;
use App\Domains\Academic\Models\ScheduleRule;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Models\TeachingAssignment;
use App\Domains\Academic\Services\ScheduleRuleConflictChecker;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use App\Shared\Core\Models\Staff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use Tests\TestCase;

class ScheduleRuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_schedule_rule_tables_support_recurrence_and_week_numbers(): void
    {
        $this->assertTrue(Schema::hasTable('schedule_rules'));
        $this->assertTrue(Schema::hasTable('schedule_rule_week_numbers'));
        foreach (['weekday', 'start_time', 'end_time', 'recurrence_type', 'effective_from', 'workflow_status'] as $column) {
            $this->assertTrue(Schema::hasColumn('schedule_rules', $column));
        }
    }

    public function test_schedule_rule_rejects_invalid_time_interval(): void
    {
        [$assignment] = $this->fixtures();

        $this->expectException(InvalidArgumentException::class);
        ScheduleRule::create([
            'teaching_assignment_id' => $assignment->id, 'weekday' => 1,
            'start_time' => '08:00', 'end_time' => '08:00', 'recurrence_type' => 'EVERY_WEEK',
            'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE',
        ]);
    }

    public function test_conflict_checker_returns_structured_teacher_and_class_conflicts(): void
    {
        [$assignment, $class, $subject, $staff] = $this->fixtures();
        ScheduleRule::create([
            'teaching_assignment_id' => $assignment->id, 'weekday' => 1,
            'start_time' => '08:00', 'end_time' => '09:30', 'recurrence_type' => 'EVERY_WEEK',
            'effective_from' => '2026-07-01', 'effective_until' => '2027-01-01', 'workflow_status' => 'ACTIVE',
        ]);
        $candidate = [
            'teaching_assignment' => $assignment,
            'class_id' => $class->id,
            'teacher_staff_id' => $staff->id,
            'weekday' => 1,
            'start_time' => '09:00', 'end_time' => '10:00',
            'recurrence_type' => 'EVERY_WEEK',
            'effective_from' => '2026-07-01', 'effective_until' => '2027-01-01',
        ];

        $conflicts = app(ScheduleRuleConflictChecker::class)->check($candidate);

        $this->assertCount(1, $conflicts);
        $this->assertSame('CLASS_CONFLICT', $conflicts->first()['conflict_type']);
        $this->assertSame('HIGH', $conflicts->first()['severity']);
        $this->assertSame('2026-07-06', $conflicts->first()['occurrence_date']);
    }

    public function test_imported_schedule_overlap_enters_blocking_conflict_path(): void
    {
        [$assignment, $class, , $staff] = $this->fixtures();
        ScheduleRule::create([
            'teaching_assignment_id' => $assignment->id, 'weekday' => 1,
            'start_time' => '08:00', 'end_time' => '09:30', 'recurrence_type' => 'EVERY_WEEK',
            'effective_from' => '2026-07-01', 'effective_until' => '2027-01-01', 'workflow_status' => 'ACTIVE',
        ]);

        $conflicts = app(ScheduleRuleConflictChecker::class)->check([
            'source' => 'IMPORTED',
            'teaching_assignment' => $assignment,
            'class_id' => $class->id,
            'teacher_staff_id' => $staff->id,
            'weekday' => 1,
            'start_time' => '09:00', 'end_time' => '10:00',
            'recurrence_type' => 'EVERY_WEEK',
            'effective_from' => '2026-07-01', 'effective_until' => '2027-01-01',
        ]);

        $this->assertCount(1, $conflicts);
        $this->assertSame('HIGH', $conflicts->first()['severity']);
        $this->assertSame('CLASS_CONFLICT', $conflicts->first()['conflict_type']);
        $this->assertSame($class->id, $conflicts->first()['resource_id']);
    }

    private function fixtures(): array
    {
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-SCHEDULE', 'unit_name' => 'Schedule Unit', 'unit_type' => 'SCHOOL']);
        $grade = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '1', 'display_name' => 'Tingkat 1', 'sequence_no' => 1]);
        $year = AcademicYear::create(['year_code' => '2026-SCHEDULE', 'display_name' => '2026/2027', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $semester = Semester::create(['academic_year_id' => $year->id, 'semester_code' => 'ODD', 'display_name' => 'Semester Ganjil', 'sequence_no' => 1, 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-31']);
        $class = AcademicClass::create(['class_code' => 'CLASS-SCHEDULE-A', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id, 'section_code' => 'A', 'display_name' => 'Kelas 1 A']);
        $subject = Subject::create(['subject_code' => 'SUBJ-SCHEDULE', 'subject_name' => 'Schedule Subject']);
        $staff = Staff::create(['staff_code' => 'STAFF-SCHEDULE-001', 'full_name' => 'Schedule Staff']);
        $assignment = TeachingAssignment::create(['assignment_code' => 'TA-SCHEDULE-001', 'semester_id' => $semester->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $staff->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);

        return [$assignment, $class, $subject, $staff];
    }
}
