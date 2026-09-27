<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\GradeLevel;
use App\Domains\Academic\Models\ScheduleRule;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\StudentClassEnrollment;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Models\TeachingAssignment;
use App\Domains\Academic\Services\SessionParticipantSnapshotter;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use App\Shared\Core\Models\Staff;
use App\Shared\Core\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SessionParticipantSnapshotTest extends TestCase
{
    use RefreshDatabase;

    public function test_participant_snapshot_table_has_canonical_session_student_fields(): void
    {
        $this->assertTrue(Schema::hasTable('session_student_participants'));
        foreach (['class_session_id', 'student_id', 'participant_basis', 'participant_status', 'is_required'] as $column) {
            $this->assertTrue(Schema::hasColumn('session_student_participants', $column));
        }
    }

    public function test_snapshot_uses_students_effectively_enrolled_at_session_time(): void
    {
        [$session, $student, $secondStudent] = $this->fixtures();
        StudentClassEnrollment::create(['student_id' => $student->id, 'class_id' => $session->class_id, 'effective_from' => '2026-07-01']);
        StudentClassEnrollment::create(['student_id' => $secondStudent->id, 'class_id' => $session->class_id, 'effective_from' => '2026-08-01']);

        $participants = app(SessionParticipantSnapshotter::class)->snapshot($session);

        $this->assertCount(1, $participants);
        $this->assertSame($student->id, $participants->first()->student_id);
        $this->assertSame('EXPECTED', $participants->first()->participant_status);
    }

    public function test_repeated_snapshot_is_idempotent(): void
    {
        [$session, $student] = $this->fixtures();
        StudentClassEnrollment::create(['student_id' => $student->id, 'class_id' => $session->class_id, 'effective_from' => '2026-07-01']);

        $first = app(SessionParticipantSnapshotter::class)->snapshot($session);
        $second = app(SessionParticipantSnapshotter::class)->snapshot($session);

        $this->assertSame($first->first()->id, $second->first()->id);
        $first->first()->update(['participant_status' => 'REMOVED', 'removal_reason' => 'Manual correction']);
        $third = app(SessionParticipantSnapshotter::class)->snapshot($session);
        $this->assertSame('REMOVED', $third->first()->fresh()->participant_status);
        $this->assertDatabaseCount('session_student_participants', 1);
    }

    private function fixtures(): array
    {
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-PARTICIPANT', 'unit_name' => 'Participant Unit', 'unit_type' => 'SCHOOL']);
        $grade = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '1', 'display_name' => 'Tingkat 1', 'sequence_no' => 1]);
        $year = AcademicYear::create(['year_code' => '2026-PARTICIPANT', 'display_name' => '2026/2027', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $semester = Semester::create(['academic_year_id' => $year->id, 'semester_code' => 'ODD', 'display_name' => 'Semester Ganjil', 'sequence_no' => 1, 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-31']);
        $class = AcademicClass::create(['class_code' => 'CLASS-PARTICIPANT-A', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id, 'section_code' => 'A', 'display_name' => 'Kelas 1 A']);
        $subject = Subject::create(['subject_code' => 'SUBJ-PARTICIPANT', 'subject_name' => 'Participant Subject']);
        $staff = Staff::create(['staff_code' => 'STAFF-PARTICIPANT-001', 'full_name' => 'Participant Staff']);
        $assignment = TeachingAssignment::create(['assignment_code' => 'TA-PARTICIPANT-001', 'semester_id' => $semester->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $staff->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);
        $rule = ScheduleRule::create(['teaching_assignment_id' => $assignment->id, 'weekday' => 1, 'start_time' => '08:00', 'end_time' => '09:30', 'recurrence_type' => 'EVERY_WEEK', 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);
        $session = ClassSession::create(['session_code' => 'SESSION-PARTICIPANT-001', 'teaching_assignment_id' => $assignment->id, 'schedule_rule_id' => $rule->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'planned_start_at' => '2026-07-06 08:00:00', 'planned_end_at' => '2026-07-06 09:30:00', 'session_source' => 'SCHEDULED', 'participant_scope' => 'FULL_CLASS', 'session_status' => 'PLANNED']);
        $student = Student::create(['student_code' => 'STU-PARTICIPANT-001', 'full_name' => 'Participant Student']);
        $secondStudent = Student::create(['student_code' => 'STU-PARTICIPANT-002', 'full_name' => 'Second Participant']);

        return [$session, $student, $secondStudent];
    }
}
