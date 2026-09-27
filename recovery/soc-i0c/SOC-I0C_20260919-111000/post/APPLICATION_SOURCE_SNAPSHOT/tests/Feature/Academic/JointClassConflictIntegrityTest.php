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
use Tests\TestCase;

class JointClassConflictIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_joint_candidate_conflicts_with_secondary_class_rule(): void
    {
        [$anchor, $secondary, , $subject, $semester, $teacher] = $this->fixture();
        $this->activeRule($secondary, $subject, $semester, $teacher);

        $conflicts = app(ScheduleRuleConflictChecker::class)->check($this->candidate($anchor, $anchor, $secondary, $subject, $semester, 'OTHER-TEACHER'));

        $this->assertSame('CLASS_CONFLICT', $conflicts->firstOrFail()['conflict_type']);
        $this->assertSame($secondary->id, $conflicts->first()['resource_id']);
    }

    public function test_single_secondary_candidate_conflicts_with_existing_joint_rule(): void
    {
        [$anchor, $secondary, , $subject, $semester, $teacher] = $this->fixture();
        $existing = $this->activeRule($anchor, $subject, $semester, $teacher);
        $existing->groups()->createMany([
            ['class_id' => $anchor->id, 'scope_role' => 'JOINT_SCOPE'],
            ['class_id' => $secondary->id, 'scope_role' => 'JOINT_SCOPE'],
        ]);

        $conflicts = app(ScheduleRuleConflictChecker::class)->check($this->candidate($secondary, $secondary, $secondary, $subject, $semester, 'OTHER-TEACHER'));

        $this->assertSame('CLASS_CONFLICT', $conflicts->firstOrFail()['conflict_type']);
    }

    public function test_disjoint_candidate_scope_has_no_class_conflict(): void
    {
        [$anchor, $secondary, $disjoint, $subject, $semester, $teacher] = $this->fixture();
        $existing = $this->activeRule($anchor, $subject, $semester, $teacher);
        $existing->groups()->createMany([
            ['class_id' => $anchor->id, 'scope_role' => 'JOINT_SCOPE'],
            ['class_id' => $secondary->id, 'scope_role' => 'JOINT_SCOPE'],
        ]);

        $conflicts = app(ScheduleRuleConflictChecker::class)->check($this->candidate($disjoint, $disjoint, $disjoint, $subject, $semester, 'OTHER-TEACHER'));

        $this->assertCount(0, $conflicts);
    }

    private function candidate(AcademicClass $class, AcademicClass $firstScope, AcademicClass $secondScope, Subject $subject, Semester $semester, string $teacherId): array
    {
        return [
            'teaching_assignment' => (object) ['class_id' => $class->id],
            'class_id' => $class->id,
            'effective_class_ids' => [$firstScope->id, $secondScope->id],
            'teacher_staff_id' => $teacherId,
            'weekday' => 1,
            'start_time' => '08:00',
            'end_time' => '09:00',
            'recurrence_type' => 'EVERY_WEEK',
            'effective_from' => '2026-07-01',
            'effective_until' => '2026-12-31',
        ];
    }

    private function activeRule(AcademicClass $class, Subject $subject, Semester $semester, Staff $teacher): ScheduleRule
    {
        $assignment = TeachingAssignment::create(['assignment_code' => 'ASSIGNMENT-'.str()->uuid(), 'semester_id' => $semester->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $teacher->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);

        return ScheduleRule::create(['teaching_assignment_id' => $assignment->id, 'weekday' => 1, 'start_time' => '08:00', 'end_time' => '09:00', 'recurrence_type' => 'EVERY_WEEK', 'effective_from' => '2026-07-01', 'effective_until' => '2026-12-31', 'workflow_status' => 'ACTIVE']);
    }

    private function fixture(): array
    {
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-JOINT-CONFLICT', 'unit_name' => 'Joint Conflict Unit', 'unit_type' => 'SCHOOL']);
        $grade = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '2', 'display_name' => 'Tingkat 2', 'sequence_no' => 2]);
        $year = AcademicYear::create(['year_code' => 'YEAR-JOINT-CONFLICT', 'display_name' => '2026/2027 Joint Conflict', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $semester = Semester::create(['academic_year_id' => $year->id, 'semester_code' => 'S1-JOINT-CONFLICT', 'display_name' => 'Semester Joint Conflict', 'sequence_no' => 1, 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-31']);
        $anchor = AcademicClass::create(['class_code' => 'CONFLICT-2B', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id, 'section_code' => 'B', 'display_name' => 'Kelas 2B Conflict']);
        $secondary = AcademicClass::create(['class_code' => 'CONFLICT-3B', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id, 'section_code' => 'C', 'display_name' => 'Kelas 3B Conflict']);
        $disjoint = AcademicClass::create(['class_code' => 'CONFLICT-4A', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id, 'section_code' => 'A', 'display_name' => 'Kelas 4A Conflict']);
        $subject = Subject::create(['subject_code' => 'SUBJECT-JOINT-CONFLICT', 'subject_name' => 'Joint Conflict Subject']);
        $teacher = Staff::create(['staff_code' => 'STAFF-JOINT-CONFLICT', 'full_name' => 'Joint Conflict Teacher']);

        return [$anchor, $secondary, $disjoint, $subject, $semester, $teacher];
    }
}
