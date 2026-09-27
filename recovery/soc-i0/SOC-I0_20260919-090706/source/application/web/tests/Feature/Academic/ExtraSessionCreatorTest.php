<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Exceptions\ScheduleConflictException;
use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\GradeLevel;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Models\TeachingAssignment;
use App\Domains\Academic\Services\ExtraSessionCreator;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use App\Shared\Core\Models\Staff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class ExtraSessionCreatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_extra_session_is_created_with_explicit_source_and_status(): void
    {
        [$assignment] = $this->fixtures();
        $session = app(ExtraSessionCreator::class)->create($assignment, '2026-08-01 08:00:00', '2026-08-01 09:00:00');

        $this->assertSame('EXTRA', $session->session_source);
        $this->assertSame('PLANNED', $session->session_status);
        $this->assertNull($session->schedule_rule_id);
    }

    public function test_ad_hoc_session_can_be_created_in_a_free_slot(): void
    {
        [$assignment] = $this->fixtures();
        $session = app(ExtraSessionCreator::class)->create($assignment, '2026-08-01 10:00:00', '2026-08-01 11:00:00', 'AD_HOC', 'SELECTED_STUDENTS');

        $this->assertSame('AD_HOC', $session->session_source);
        $this->assertSame('SELECTED_STUDENTS', $session->participant_scope);
    }

    public function test_invalid_participant_scope_is_rejected_before_persistence(): void
    {
        [$assignment] = $this->fixtures();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Participant scope must be FULL_CLASS or SELECTED_STUDENTS.');

        app(ExtraSessionCreator::class)->create($assignment, '2026-08-01 10:00:00', '2026-08-01 11:00:00', 'AD_HOC', 'INVALID_SCOPE');
    }

    public function test_extra_session_rejects_overlapping_class_with_structured_conflict(): void
    {
        [$assignment] = $this->fixtures();
        ClassSession::create([
            'session_code' => 'SESSION-EXISTING-EXTRA', 'teaching_assignment_id' => $assignment->id,
            'class_id' => $assignment->class_id, 'subject_id' => $assignment->subject_id,
            'planned_start_at' => '2026-08-01 08:00:00', 'planned_end_at' => '2026-08-01 09:30:00',
            'session_source' => 'SCHEDULED', 'participant_scope' => 'FULL_CLASS', 'session_status' => 'PLANNED',
        ]);

        try {
            app(ExtraSessionCreator::class)->create($assignment, '2026-08-01 09:00:00', '2026-08-01 10:00:00');
            $this->fail('Expected a schedule conflict.');
        } catch (ScheduleConflictException $exception) {
            $this->assertSame('CLASS_CONFLICT', $exception->conflict['conflict_type']);
            $this->assertSame('HIGH', $exception->conflict['severity']);
            $this->assertSame('2026-08-01 09:00:00', $exception->conflict['overlap_start_at']);
        }
    }

    private function fixtures(): array
    {
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-EXTRA', 'unit_name' => 'Extra Unit', 'unit_type' => 'SCHOOL']);
        $grade = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '1', 'display_name' => 'Tingkat 1', 'sequence_no' => 1]);
        $year = AcademicYear::create(['year_code' => '2026-EXTRA', 'display_name' => '2026/2027', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $semester = Semester::create(['academic_year_id' => $year->id, 'semester_code' => 'ODD', 'display_name' => 'Semester Ganjil', 'sequence_no' => 1, 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-31']);
        $class = AcademicClass::create(['class_code' => 'CLASS-EXTRA-A', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id, 'section_code' => 'A', 'display_name' => 'Kelas 1 A']);
        $subject = Subject::create(['subject_code' => 'SUBJ-EXTRA', 'subject_name' => 'Extra Subject']);
        $staff = Staff::create(['staff_code' => 'STAFF-EXTRA-001', 'full_name' => 'Extra Staff']);
        $assignment = TeachingAssignment::create(['assignment_code' => 'TA-EXTRA-001', 'semester_id' => $semester->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $staff->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);

        return [$assignment];
    }
}
