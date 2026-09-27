<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\AttendancePeriodLock;
use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\GradeLevel;
use App\Domains\Academic\Models\ReportCard;
use App\Domains\Academic\Models\ReportCardVersion;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\SemesterSubjectGrade;
use App\Domains\Academic\Models\SessionStudentParticipant;
use App\Domains\Academic\Models\StudentAttendance;
use App\Domains\Academic\Models\StudentClassEnrollment;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Models\TeachingAssignment;
use App\Domains\Academic\Services\ReportCardDraftService;
use App\Models\User;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use App\Shared\Core\Models\Staff;
use App\Shared\Core\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class ReportCardDraftServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_structured_draft_with_grade_and_attendance_snapshots(): void
    {
        [$student, $semester, $class, $actor] = $this->fixtures(true, true);

        $version = app(ReportCardDraftService::class)->create($student, $semester, $class, $actor);

        $this->assertSame('DRAFT', $version->status);
        $this->assertSame(1, $version->version_no);
        $this->assertSame('Report Student', $version->student_name_snapshot);
        $this->assertSame('88.00', $version->subjectLines->first()->score);
        $this->assertSame('PRESENT', $version->attendanceLines->first()->status_code);
        $this->assertSame(1, ReportCard::count());
    }

    public function test_second_draft_is_new_version_and_previous_snapshot_is_immutable(): void
    {
        [$student, $semester, $class, $actor] = $this->fixtures(true, true);
        $service = app(ReportCardDraftService::class);
        $first = $service->create($student, $semester, $class, $actor);
        $second = $service->create($student, $semester, $class, $actor);

        $this->assertSame(2, $second->version_no);
        $this->assertCount(2, ReportCardVersion::all());
        $this->expectException(LogicException::class);
        $first->update(['status' => 'REVIEWED']);
    }

    public function test_readiness_blocker_prevents_report_draft_creation(): void
    {
        [$student, $semester, $class, $actor] = $this->fixtures(false, true);

        $this->expectException(\InvalidArgumentException::class);
        app(ReportCardDraftService::class)->create($student, $semester, $class, $actor);
        $this->assertSame(0, ReportCard::count());
    }

    private function fixtures(bool $withGrade, bool $lockAttendance): array
    {
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-RPT', 'unit_name' => 'Report Unit', 'unit_type' => 'SCHOOL']);
        $gradeLevel = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '1', 'display_name' => 'Tingkat 1', 'sequence_no' => 1]);
        $year = AcademicYear::create(['year_code' => '2026-RPT', 'display_name' => '2026/2027', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $semester = Semester::create(['academic_year_id' => $year->id, 'semester_code' => 'ODD', 'display_name' => 'Semester Ganjil', 'sequence_no' => 1, 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-31']);
        $class = AcademicClass::create(['class_code' => 'CLASS-RPT-A', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $gradeLevel->id, 'section_code' => 'A', 'display_name' => 'Kelas Report']);
        $subject = Subject::create(['subject_code' => 'SUBJ-RPT', 'subject_name' => 'Report Subject']);
        $teacher = Staff::create(['staff_code' => 'STAFF-RPT', 'full_name' => 'Report Teacher']);
        $student = Student::create(['student_code' => 'STU-RPT', 'full_name' => 'Report Student']);
        $actor = User::factory()->create();
        StudentClassEnrollment::create(['student_id' => $student->id, 'class_id' => $class->id, 'effective_from' => '2026-07-01']);
        $assignment = TeachingAssignment::create(['assignment_code' => 'TA-RPT', 'semester_id' => $semester->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $teacher->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);
        $session = ClassSession::create(['session_code' => 'SESSION-RPT', 'teaching_assignment_id' => $assignment->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'planned_start_at' => '2026-07-06 08:00:00', 'planned_end_at' => '2026-07-06 09:30:00', 'session_source' => 'SCHEDULED', 'participant_scope' => 'FULL_CLASS', 'session_status' => 'COMPLETED']);
        $participant = SessionStudentParticipant::create(['class_session_id' => $session->id, 'student_id' => $student->id, 'participant_basis' => 'CLASS_ENROLLMENT']);
        StudentAttendance::create(['session_student_participant_id' => $participant->id, 'attendance_status' => 'PRESENT', 'workflow_status' => 'VALIDATED', 'entered_by' => $actor->id, 'entered_at' => now(), 'finalized_by' => $actor->id, 'finalized_at' => now(), 'updated_by' => $actor->id, 'updated_at' => now()]);
        if ($withGrade) {
            SemesterSubjectGrade::create(['student_id' => $student->id, 'semester_id' => $semester->id, 'subject_id' => $subject->id, 'score' => 88, 'workflow_status' => 'LOCKED', 'entered_by' => $actor->id, 'entered_at' => now(), 'finalized_by' => $actor->id, 'finalized_at' => now(), 'updated_by' => $actor->id, 'updated_at' => now()]);
        }
        if ($lockAttendance) {
            foreach (range(7, 12) as $month) {
                $start = sprintf('2026-%02d-01', $month);
                AttendancePeriodLock::create(['class_id' => $class->id, 'period_start' => $start, 'period_end' => date('Y-m-t', strtotime($start)), 'status' => 'LOCKED', 'locked_by' => $actor->id, 'locked_at' => now()]);
            }
        }

        return [$student, $semester, $class, $actor];
    }
}
