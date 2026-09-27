<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\GradeLevel;
use App\Domains\Academic\Models\ScheduleRule;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Models\TeachingAssignment;
use App\Domains\Academic\Services\ClassSessionGenerator;
use App\Domains\Academic\Services\ScheduleRuleRevisionService;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use App\Shared\Core\Models\Staff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleRuleRevisionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_joint_rule_revision_preserves_groups_and_generated_session_scope(): void
    {
        [$rule, $anchor, $secondary] = $this->fixture();
        $rule->groups()->createMany([
            ['class_id' => $anchor->id, 'scope_role' => 'JOINT_SCOPE'],
            ['class_id' => $secondary->id, 'scope_role' => 'JOINT_SCOPE'],
        ]);

        $revised = app(ScheduleRuleRevisionService::class)->revise($rule, [
            'revision_from' => '2026-09-02',
            'weekday' => 3,
            'start_time' => '09:00',
            'end_time' => '10:30',
            'recurrence_type' => 'EVERY_WEEK',
            'effective_until' => '2026-12-31',
            'week_numbers' => [],
            'teacher_staff_id' => $rule->teachingAssignment->teacher_staff_id,
            'subject_id' => $rule->teachingAssignment->subject_id,
        ], null, 'Joint rule revision', app(ClassSessionGenerator::class));

        $this->assertSame($anchor->id, $revised->teachingAssignment->class_id);
        $this->assertEqualsCanonicalizing(
            [[$anchor->id, 'JOINT_SCOPE'], [$secondary->id, 'JOINT_SCOPE']],
            $revised->groups()->get(['class_id', 'scope_role'])->map(fn ($group): array => [$group->class_id, $group->scope_role])->all(),
        );
        $this->assertEqualsCanonicalizing(
            [[$anchor->id, 'JOINT_SCOPE'], [$secondary->id, 'JOINT_SCOPE']],
            $revised->sessions()->firstOrFail()->scopeGroups()->get(['class_id', 'scope_role'])->map(fn ($group): array => [$group->class_id, $group->scope_role])->all(),
        );
        $this->assertNotEmpty($revised->sessions()->get());
        $this->assertSame(2, $rule->fresh()->groups()->count());
    }

    private function fixture(): array
    {
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-REVISION-JOINT', 'unit_name' => 'Revision Joint Unit', 'unit_type' => 'SCHOOL']);
        $gradeTwo = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '2', 'display_name' => 'Tingkat 2', 'sequence_no' => 2]);
        $gradeThree = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '3', 'display_name' => 'Tingkat 3', 'sequence_no' => 3]);
        $year = AcademicYear::create(['year_code' => 'YEAR-REVISION-JOINT', 'display_name' => '2026/2027 Revision Joint', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $semester = Semester::create(['academic_year_id' => $year->id, 'semester_code' => 'S1-REVISION-JOINT', 'display_name' => 'Semester 1 Revision Joint', 'sequence_no' => 1, 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-31']);
        $anchor = AcademicClass::create(['class_code' => 'REVISION-2B', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $gradeTwo->id, 'section_code' => 'B', 'display_name' => 'Kelas 2B Revision']);
        $secondary = AcademicClass::create(['class_code' => 'REVISION-3B', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $gradeThree->id, 'section_code' => 'B', 'display_name' => 'Kelas 3B Revision']);
        $subject = Subject::create(['subject_code' => 'SUBJECT-REVISION-JOINT', 'subject_name' => 'Revision Joint Subject']);
        $staff = Staff::create(['staff_code' => 'STAFF-REVISION-JOINT', 'full_name' => 'Revision Joint Staff']);
        $assignment = TeachingAssignment::create(['assignment_code' => 'ASSIGNMENT-REVISION-JOINT', 'semester_id' => $semester->id, 'class_id' => $anchor->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $staff->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);
        $rule = ScheduleRule::create(['teaching_assignment_id' => $assignment->id, 'weekday' => 3, 'start_time' => '08:00', 'end_time' => '09:30', 'recurrence_type' => 'EVERY_WEEK', 'effective_from' => '2026-07-01', 'effective_until' => '2026-12-31', 'workflow_status' => 'ACTIVE']);

        return [$rule, $anchor, $secondary];
    }
}
