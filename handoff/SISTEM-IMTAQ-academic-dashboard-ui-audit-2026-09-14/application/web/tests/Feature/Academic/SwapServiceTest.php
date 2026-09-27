<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Exceptions\ScheduleConflictException;
use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\GradeLevel;
use App\Domains\Academic\Models\ScheduleChange;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\SessionTeacherParticipation;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Models\TeachingAssignment;
use App\Domains\Academic\Services\SwapService;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use App\Shared\Core\Models\Staff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SwapServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_swap_applies_both_resulting_teacher_participations_atomically(): void
    {
        [$first, $second, $firstTeacher, $secondTeacher] = $this->fixtures();
        $change = app(SwapService::class)->applySwap($first, $second, null, 'Teacher exchange');

        $this->assertInstanceOf(ScheduleChange::class, $change);
        $this->assertSame('SWAP', $change->change_type);
        $this->assertSame('APPLIED', $change->status);
        $this->assertDatabaseHas('session_teacher_participations', ['class_session_id' => $first->id, 'teacher_staff_id' => $secondTeacher->id, 'role' => 'SUBSTITUTE']);
        $this->assertDatabaseHas('session_teacher_participations', ['class_session_id' => $second->id, 'teacher_staff_id' => $firstTeacher->id, 'role' => 'SUBSTITUTE']);
    }

    public function test_swap_rejects_resulting_teacher_conflict_without_partial_change(): void
    {
        [$first, $second, , $secondTeacher] = $this->fixtures();
        $otherAssignment = TeachingAssignment::create(['assignment_code' => 'TA-SWAP-CONFLICT', 'semester_id' => $second->teachingAssignment->semester_id, 'class_id' => $second->class_id, 'subject_id' => $second->subject_id, 'teacher_staff_id' => $secondTeacher->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);
        ClassSession::create(['session_code' => 'SESSION-SWAP-CONFLICT', 'teaching_assignment_id' => $otherAssignment->id, 'class_id' => $second->class_id, 'subject_id' => $second->subject_id, 'planned_start_at' => '2026-07-06 08:30:00', 'planned_end_at' => '2026-07-06 09:00:00', 'session_source' => 'SCHEDULED', 'participant_scope' => 'FULL_CLASS', 'session_status' => 'PLANNED']);

        try {
            app(SwapService::class)->applySwap($first, $second, null, 'Conflict test');
            $this->fail('Expected a schedule conflict.');
        } catch (ScheduleConflictException $exception) {
            $this->assertSame('TEACHER_CONFLICT', $exception->conflict['conflict_type']);
        }

        $this->assertDatabaseCount('schedule_changes', 0);
        $this->assertDatabaseCount('session_teacher_participations', 2);
    }

    public function test_swap_rejects_expected_substitute_overlap_without_partial_change(): void
    {
        [$first, $second, $firstTeacher, $secondTeacher] = $this->fixtures();
        $otherAssignment = TeachingAssignment::create(['assignment_code' => 'TA-SWAP-SUBSTITUTE-CONFLICT', 'semester_id' => $second->teachingAssignment->semester_id, 'class_id' => $first->class_id, 'subject_id' => $second->subject_id, 'teacher_staff_id' => $firstTeacher->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);
        $other = ClassSession::create(['session_code' => 'SESSION-SWAP-SUBSTITUTE-CONFLICT', 'teaching_assignment_id' => $otherAssignment->id, 'class_id' => $first->class_id, 'subject_id' => $second->subject_id, 'planned_start_at' => '2026-07-06 08:30:00', 'planned_end_at' => '2026-07-06 09:30:00', 'session_source' => 'SCHEDULED', 'participant_scope' => 'FULL_CLASS', 'session_status' => 'PLANNED']);
        SessionTeacherParticipation::create(['class_session_id' => $other->id, 'teacher_staff_id' => $secondTeacher->id, 'role' => 'SUBSTITUTE', 'obligation_type' => 'REPLACEMENT', 'participation_status' => 'EXPECTED']);

        $this->expectException(ScheduleConflictException::class);
        app(SwapService::class)->applySwap($first, $second, null, 'Substitute conflict test');
    }

    private function fixtures(): array
    {
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-SWAP', 'unit_name' => 'Swap Unit', 'unit_type' => 'SCHOOL']);
        $grade = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '1', 'display_name' => 'Tingkat 1', 'sequence_no' => 1]);
        $year = AcademicYear::create(['year_code' => '2026-SWAP', 'display_name' => '2026/2027', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $semester = Semester::create(['academic_year_id' => $year->id, 'semester_code' => 'ODD', 'display_name' => 'Semester Ganjil', 'sequence_no' => 1, 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-31']);
        $gradeA = $grade;
        $classA = AcademicClass::create(['class_code' => 'CLASS-SWAP-A', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $gradeA->id, 'section_code' => 'A', 'display_name' => 'Kelas 1 A']);
        $classB = AcademicClass::create(['class_code' => 'CLASS-SWAP-B', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $gradeA->id, 'section_code' => 'B', 'display_name' => 'Kelas 1 B']);
        $subject = Subject::create(['subject_code' => 'SUBJ-SWAP', 'subject_name' => 'Swap Subject']);
        $firstTeacher = Staff::create(['staff_code' => 'STAFF-SWAP-FIRST', 'full_name' => 'First Teacher']);
        $secondTeacher = Staff::create(['staff_code' => 'STAFF-SWAP-SECOND', 'full_name' => 'Second Teacher']);
        $firstAssignment = TeachingAssignment::create(['assignment_code' => 'TA-SWAP-FIRST', 'semester_id' => $semester->id, 'class_id' => $classA->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $firstTeacher->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);
        $secondAssignment = TeachingAssignment::create(['assignment_code' => 'TA-SWAP-SECOND', 'semester_id' => $semester->id, 'class_id' => $classB->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $secondTeacher->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);
        $first = ClassSession::create(['session_code' => 'SESSION-SWAP-FIRST', 'teaching_assignment_id' => $firstAssignment->id, 'class_id' => $classA->id, 'subject_id' => $subject->id, 'planned_start_at' => '2026-07-06 08:00:00', 'planned_end_at' => '2026-07-06 09:00:00', 'session_source' => 'SCHEDULED', 'participant_scope' => 'FULL_CLASS', 'session_status' => 'PLANNED']);
        $second = ClassSession::create(['session_code' => 'SESSION-SWAP-SECOND', 'teaching_assignment_id' => $secondAssignment->id, 'class_id' => $classB->id, 'subject_id' => $subject->id, 'planned_start_at' => '2026-07-06 09:00:00', 'planned_end_at' => '2026-07-06 10:00:00', 'session_source' => 'SCHEDULED', 'participant_scope' => 'FULL_CLASS', 'session_status' => 'PLANNED']);
        SessionTeacherParticipation::create(['class_session_id' => $first->id, 'teacher_staff_id' => $firstTeacher->id, 'role' => 'PRIMARY', 'obligation_type' => 'TEACHING_ASSIGNMENT']);
        SessionTeacherParticipation::create(['class_session_id' => $second->id, 'teacher_staff_id' => $secondTeacher->id, 'role' => 'PRIMARY', 'obligation_type' => 'TEACHING_ASSIGNMENT']);

        return [$first, $second, $firstTeacher, $secondTeacher];
    }
}
