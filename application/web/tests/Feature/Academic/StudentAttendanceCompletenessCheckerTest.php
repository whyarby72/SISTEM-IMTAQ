<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\GradeLevel;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\SessionStudentParticipant;
use App\Domains\Academic\Models\StudentAttendance;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Models\TeachingAssignment;
use App\Domains\Academic\Services\StudentAttendanceCompletenessChecker;
use App\Models\User;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use App\Shared\Core\Models\Staff;
use App\Shared\Core\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentAttendanceCompletenessCheckerTest extends TestCase
{
    use RefreshDatabase;

    public function test_missing_and_draft_attendance_are_reported_without_creating_absence(): void
    {
        [$session, $first, $second, $actor] = $this->fixtures();
        StudentAttendance::create(['session_student_participant_id' => $first->id, 'attendance_status' => 'PRESENT', 'workflow_status' => 'DRAFT', 'entered_by' => $actor->id, 'entered_at' => now(), 'updated_by' => $actor->id, 'updated_at' => now()]);

        $finding = app(StudentAttendanceCompletenessChecker::class)->check($session);

        $this->assertSame('INCOMPLETE', $finding['status']);
        $this->assertSame([(string) $second->id], $finding['missing_attendance_participant_ids']);
        $this->assertSame([(string) $first->id], $finding['unresolved_attendance_participant_ids']);
        $this->assertSame(0, StudentAttendance::where('attendance_status', 'ABSENT')->count());
    }

    public function test_validated_attendance_is_complete_and_cancelled_session_is_not_applicable(): void
    {
        [$session, $first, $second, $actor] = $this->fixtures();
        foreach ([$first, $second] as $participant) {
            StudentAttendance::create(['session_student_participant_id' => $participant->id, 'attendance_status' => 'PRESENT', 'workflow_status' => 'VALIDATED', 'entered_by' => $actor->id, 'entered_at' => now(), 'finalized_by' => $actor->id, 'finalized_at' => now(), 'updated_by' => $actor->id, 'updated_at' => now()]);
        }

        $this->assertSame('COMPLETE', app(StudentAttendanceCompletenessChecker::class)->check($session)['status']);
        $session->update(['session_status' => 'CANCELLED']);
        $this->assertSame('NOT_APPLICABLE', app(StudentAttendanceCompletenessChecker::class)->check($session)['status']);
    }

    public function test_session_without_participants_is_reported_as_missing_roster(): void
    {
        [$session] = $this->fixtures();
        $session->studentParticipants()->delete();

        $finding = app(StudentAttendanceCompletenessChecker::class)->check($session);

        $this->assertSame('NO_PARTICIPANTS', $finding['status']);
        $this->assertFalse($finding['is_complete']);
        $this->assertTrue($finding['no_participants']);
    }

    private function fixtures(): array
    {
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-DQC', 'unit_name' => 'DQ Unit', 'unit_type' => 'SCHOOL']);
        $grade = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '1', 'display_name' => 'Tingkat 1', 'sequence_no' => 1]);
        $year = AcademicYear::create(['year_code' => '2026-DQC', 'display_name' => '2026/2027', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $semester = Semester::create(['academic_year_id' => $year->id, 'semester_code' => 'ODD', 'display_name' => 'Semester Ganjil', 'sequence_no' => 1, 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-31']);
        $class = AcademicClass::create(['class_code' => 'CLASS-DQC-A', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id, 'section_code' => 'A', 'display_name' => 'Kelas 1 A']);
        $subject = Subject::create(['subject_code' => 'SUBJ-DQC', 'subject_name' => 'DQ Subject']);
        $teacher = Staff::create(['staff_code' => 'STAFF-DQC-001', 'full_name' => 'Teacher']);
        $actor = User::factory()->create();
        $assignment = TeachingAssignment::create(['assignment_code' => 'TA-DQC-001', 'semester_id' => $semester->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $teacher->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);
        $session = ClassSession::create(['session_code' => 'SESSION-DQC-001', 'teaching_assignment_id' => $assignment->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'planned_start_at' => '2026-07-06 08:00:00', 'planned_end_at' => '2026-07-06 09:30:00', 'session_source' => 'SCHEDULED', 'participant_scope' => 'FULL_CLASS', 'session_status' => 'PLANNED']);
        $first = SessionStudentParticipant::create(['class_session_id' => $session->id, 'student_id' => Student::create(['student_code' => 'STU-DQC-001', 'full_name' => 'First Student'])->id, 'participant_basis' => 'CLASS_ENROLLMENT']);
        $second = SessionStudentParticipant::create(['class_session_id' => $session->id, 'student_id' => Student::create(['student_code' => 'STU-DQC-002', 'full_name' => 'Second Student'])->id, 'participant_basis' => 'CLASS_ENROLLMENT']);

        return [$session, $first, $second, $actor];
    }
}
