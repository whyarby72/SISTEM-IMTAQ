<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Exceptions\ScheduleConflictException;
use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\ClassSessionGroup;
use App\Domains\Academic\Models\GradeLevel;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Models\TeachingAssignment;
use App\Domains\Academic\Services\RescheduleService;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use App\Shared\Core\Models\Staff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RescheduleServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_reschedule_preserves_source_and_creates_lineage_target(): void
    {
        [$source] = $this->fixtures();
        $change = app(RescheduleService::class)->apply($source, '2026-07-07 08:00:00', '2026-07-07 09:30:00', null, 'Room unavailable');

        $source->refresh();
        $target = ClassSession::find($change->related_session_id);
        $this->assertSame('RESCHEDULED', $source->session_status);
        $this->assertSame('RESCHEDULED', $target->session_source);
        $this->assertSame($source->id, $target->rescheduled_from_session_id);
        $this->assertSame('APPLIED', $change->status);
    }

    public function test_time_change_uses_same_atomic_lineage_path(): void
    {
        [$source] = $this->fixtures();
        $change = app(RescheduleService::class)->apply($source, '2026-07-08 08:00:00', '2026-07-08 09:30:00', null, 'Time adjustment', 'TIME_CHANGE');

        $this->assertSame('TIME_CHANGE', $change->change_type);
        $this->assertDatabaseHas('class_sessions', ['id' => $change->related_session_id, 'session_source' => 'RESCHEDULED']);
    }

    public function test_joint_source_preserves_scope_groups_on_one_replacement(): void
    {
        [$source, , , , $class, , $unit, $grade, $year] = $this->fixtures();
        $secondaryClass = AcademicClass::create(['class_code' => 'CLASS-RESCHEDULE-B', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id, 'section_code' => 'B', 'display_name' => 'Kelas 1 B']);
        ClassSessionGroup::create(['class_session_id' => $source->id, 'class_id' => $class->id, 'scope_role' => 'JOINT_SCOPE']);
        ClassSessionGroup::create(['class_session_id' => $source->id, 'class_id' => $secondaryClass->id, 'scope_role' => 'JOINT_SCOPE']);

        $change = app(RescheduleService::class)->apply($source, '2026-07-07 08:00:00', '2026-07-07 09:30:00', null, 'Joint session move');
        $replacement = ClassSession::findOrFail($change->related_session_id);

        $this->assertSame($class->id, $replacement->class_id);
        $this->assertSame(1, ClassSession::where('rescheduled_from_session_id', $source->id)->count());
        $this->assertEqualsCanonicalizing(
            [[$class->id, 'JOINT_SCOPE'], [$secondaryClass->id, 'JOINT_SCOPE']],
            $replacement->scopeGroups()->get(['class_id', 'scope_role'])->map(fn (ClassSessionGroup $group): array => [$group->class_id, $group->scope_role])->all(),
        );
        $this->assertSame(2, $source->fresh()->scopeGroups()->count());
    }

    public function test_single_scope_group_is_preserved_on_replacement(): void
    {
        [$source, , , , $class] = $this->fixtures();
        ClassSessionGroup::create(['class_session_id' => $source->id, 'class_id' => $class->id, 'scope_role' => 'TEACHING_SCOPE']);

        $change = app(RescheduleService::class)->apply($source, '2026-07-07 08:00:00', '2026-07-07 09:30:00', null, 'Single scope move');
        $replacement = ClassSession::findOrFail($change->related_session_id);

        $this->assertSame(1, $replacement->scopeGroups()->count());
        $this->assertDatabaseHas('class_session_groups', ['class_session_id' => $replacement->id, 'class_id' => $class->id, 'scope_role' => 'TEACHING_SCOPE']);
    }

    public function test_joint_reschedule_rejects_conflict_on_secondary_scope(): void
    {
        [$source, , $subject, $semester, $class, , $unit, $grade, $year] = $this->fixtures();
        $secondaryClass = AcademicClass::create(['class_code' => 'CLASS-RESCHEDULE-SECONDARY', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id, 'section_code' => 'B', 'display_name' => 'Kelas Secondary']);
        $source->scopeGroups()->createMany([
            ['class_session_id' => $source->id, 'class_id' => $class->id, 'scope_role' => 'JOINT_SCOPE'],
            ['class_session_id' => $source->id, 'class_id' => $secondaryClass->id, 'scope_role' => 'JOINT_SCOPE'],
        ]);
        $otherTeacher = Staff::create(['staff_code' => 'STAFF-RESCHEDULE-SECONDARY', 'full_name' => 'Secondary Teacher']);
        $otherAssignment = TeachingAssignment::create(['assignment_code' => 'TA-RESCHEDULE-SECONDARY', 'semester_id' => $semester->id, 'class_id' => $secondaryClass->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $otherTeacher->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);
        ClassSession::create(['session_code' => 'SESSION-RESCHEDULE-SECONDARY', 'teaching_assignment_id' => $otherAssignment->id, 'class_id' => $secondaryClass->id, 'subject_id' => $subject->id, 'planned_start_at' => '2026-07-07 08:30:00', 'planned_end_at' => '2026-07-07 09:00:00', 'session_source' => 'SCHEDULED', 'participant_scope' => 'FULL_CLASS', 'session_status' => 'PLANNED']);

        $this->expectException(ScheduleConflictException::class);
        try {
            app(RescheduleService::class)->apply($source, '2026-07-07 08:00:00', '2026-07-07 09:30:00', null, 'Secondary scope conflict');
        } finally {
            $this->assertSame('PLANNED', $source->fresh()->session_status);
        }
    }

    public function test_conflicting_target_leaves_source_and_change_history_untouched(): void
    {
        [$source, , $subject, $semester, $class, $staff] = $this->fixtures();
        $otherAssignment = TeachingAssignment::create(['assignment_code' => 'TA-RESCHEDULE-CONFLICT', 'semester_id' => $semester->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $staff->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);
        ClassSession::create(['session_code' => 'SESSION-RESCHEDULE-CONFLICT', 'teaching_assignment_id' => $otherAssignment->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'planned_start_at' => '2026-07-07 08:30:00', 'planned_end_at' => '2026-07-07 09:00:00', 'session_source' => 'SCHEDULED', 'participant_scope' => 'FULL_CLASS', 'session_status' => 'PLANNED']);

        $this->expectException(ScheduleConflictException::class);
        try {
            app(RescheduleService::class)->apply($source, '2026-07-07 08:00:00', '2026-07-07 09:30:00', null, 'Conflict');
        } finally {
            $this->assertSame('PLANNED', $source->fresh()->session_status);
            $this->assertDatabaseCount('schedule_changes', 0);
        }
    }

    private function fixtures(): array
    {
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-RESCHEDULE', 'unit_name' => 'Reschedule Unit', 'unit_type' => 'SCHOOL']);
        $grade = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '1', 'display_name' => 'Tingkat 1', 'sequence_no' => 1]);
        $year = AcademicYear::create(['year_code' => '2026-RESCHEDULE', 'display_name' => '2026/2027', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $semester = Semester::create(['academic_year_id' => $year->id, 'semester_code' => 'ODD', 'display_name' => 'Semester Ganjil', 'sequence_no' => 1, 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-31']);
        $class = AcademicClass::create(['class_code' => 'CLASS-RESCHEDULE-A', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id, 'section_code' => 'A', 'display_name' => 'Kelas 1 A']);
        $subject = Subject::create(['subject_code' => 'SUBJ-RESCHEDULE', 'subject_name' => 'Reschedule Subject']);
        $staff = Staff::create(['staff_code' => 'STAFF-RESCHEDULE-001', 'full_name' => 'Reschedule Staff']);
        $assignment = TeachingAssignment::create(['assignment_code' => 'TA-RESCHEDULE-001', 'semester_id' => $semester->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $staff->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);
        $source = ClassSession::create(['session_code' => 'SESSION-RESCHEDULE-001', 'teaching_assignment_id' => $assignment->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'planned_start_at' => '2026-07-06 08:00:00', 'planned_end_at' => '2026-07-06 09:30:00', 'session_source' => 'SCHEDULED', 'participant_scope' => 'FULL_CLASS', 'session_status' => 'PLANNED']);

        return [$source, $assignment, $subject, $semester, $class, $staff, $unit, $grade, $year];
    }
}
