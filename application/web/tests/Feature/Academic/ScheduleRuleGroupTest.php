<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\GradeLevel;
use App\Domains\Academic\Models\ScheduleRule;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Models\TeachingAssignment;
use App\Domains\Academic\Services\ClassSessionGenerator;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use App\Shared\Core\Models\Staff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ScheduleRuleGroupTest extends TestCase
{
    use RefreshDatabase;

    public function test_schedule_rule_can_have_multiple_class_scopes(): void
    {
        $this->assertTrue(Schema::hasTable('schedule_rule_groups'));
        [$rule, $first, $second] = $this->fixture();

        $rule->groups()->createMany([
            ['class_id' => $first->id, 'scope_role' => 'JOINT_SCOPE'],
            ['class_id' => $second->id, 'scope_role' => 'JOINT_SCOPE'],
        ]);

        $this->assertCount(2, $rule->fresh()->groups);
        $this->assertDatabaseHas('schedule_rule_groups', ['schedule_rule_id' => $rule->id, 'class_id' => $second->id, 'scope_role' => 'JOINT_SCOPE']);
    }

    public function test_joint_rule_generates_one_session_with_two_class_scopes(): void
    {
        [$rule, $first, $second] = $this->fixture();
        $rule->update(['recurrence_type' => 'EVERY_WEEK']);
        $rule->groups()->createMany([
            ['class_id' => $first->id, 'scope_role' => 'JOINT_SCOPE'],
            ['class_id' => $second->id, 'scope_role' => 'JOINT_SCOPE'],
        ]);

        $sessions = app(ClassSessionGenerator::class)->generate($rule, '2026-09-01', '2026-09-10');

        $this->assertCount(2, $sessions);
        $this->assertDatabaseCount('class_session_groups', 4);
        $this->assertSame(2, $sessions->first()->fresh()->scopeGroups()->count());
    }

    private function fixture(): array
    {
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-JOINT', 'unit_name' => 'Joint Unit', 'unit_type' => 'SCHOOL']);
        $grade = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '2', 'display_name' => 'Tingkat 2', 'sequence_no' => 2]);
        $thirdGrade = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '3', 'display_name' => 'Tingkat 3', 'sequence_no' => 3]);
        $year = AcademicYear::create(['year_code' => 'YEAR-JOINT', 'display_name' => '2026/2027 Joint', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $semester = Semester::create(['academic_year_id' => $year->id, 'semester_code' => 'S1-JOINT', 'display_name' => 'Semester 1 Joint', 'sequence_no' => 1, 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-31']);
        $first = AcademicClass::create(['class_code' => 'JOINT-2B', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id, 'section_code' => 'B', 'display_name' => 'Kelas 2B Joint']);
        $second = AcademicClass::create(['class_code' => 'JOINT-3B', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $thirdGrade->id, 'section_code' => 'B', 'display_name' => 'Kelas 3B Joint']);
        $subject = Subject::create(['subject_code' => 'SUB-JOINT', 'subject_name' => 'Joint Subject']);
        $staff = Staff::create(['staff_code' => 'STAFF-JOINT', 'full_name' => 'Joint Staff']);
        $assignment = TeachingAssignment::create(['assignment_code' => 'TA-JOINT', 'semester_id' => $semester->id, 'class_id' => $first->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $staff->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'DRAFT']);
        $rule = ScheduleRule::create(['teaching_assignment_id' => $assignment->id, 'weekday' => 3, 'start_time' => '08:00', 'end_time' => '09:30', 'recurrence_type' => 'WEEK_OF_MONTH', 'effective_from' => '2026-07-01', 'effective_until' => '2026-12-31', 'workflow_status' => 'DRAFT']);

        return [$rule, $first, $second];
    }
}
