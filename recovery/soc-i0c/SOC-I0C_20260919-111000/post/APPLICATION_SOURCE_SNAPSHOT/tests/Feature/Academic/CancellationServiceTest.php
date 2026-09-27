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
use App\Domains\Academic\Services\CancellationService;
use App\Models\User;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use App\Shared\Core\Models\Staff;
use App\Shared\Core\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use InvalidArgumentException;
use Tests\TestCase;

class CancellationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_cancellation_preserves_session_and_releases_it_with_audited_change(): void
    {
        [$session] = $this->fixtures('2099-01-01 08:00:00', 'PLANNED');
        $change = app(CancellationService::class)->apply($session, null, 'Institution closure');

        $this->assertSame('CANCELLED', $session->fresh()->session_status);
        $this->assertSame('CANCELLATION', $change->change_type);
        $this->assertSame('APPLIED', $change->status);
        $this->assertDatabaseHas('class_sessions', ['id' => $session->id, 'session_status' => 'CANCELLED']);
    }

    public function test_cancellation_rejects_completed_sessions(): void
    {
        [$completed] = $this->fixtures('2099-01-01 08:00:00', 'COMPLETED');
        $this->expectException(InvalidArgumentException::class);
        app(CancellationService::class)->apply($completed, null, 'Too late');
    }

    public function test_cancellation_allows_past_planned_session_without_attendance(): void
    {
        [$session] = $this->fixtures(now()->subDay()->setTime(8, 0)->toDateTimeString(), 'PLANNED');

        app(CancellationService::class)->apply($session, null, 'Kegiatan akademik diganti Tahfizh');

        $this->assertSame('CANCELLED', $session->fresh()->session_status);
    }

    public function test_cancellation_rejects_session_with_attendance_data(): void
    {
        [$session] = $this->fixtures(now()->subDay()->setTime(8, 0)->toDateTimeString(), 'PLANNED');
        $student = Student::create(['student_code' => 'STU-CANCEL-001', 'full_name' => 'Cancellation Student']);
        $participant = SessionStudentParticipant::create(['class_session_id' => $session->id, 'student_id' => $student->id, 'participant_basis' => 'CLASS_ENROLLMENT']);
        $actor = User::factory()->create();
        StudentAttendance::create(['session_student_participant_id' => $participant->id, 'attendance_status' => 'PRESENT', 'workflow_status' => 'DRAFT', 'entered_by' => $actor->id, 'entered_at' => now(), 'updated_by' => $actor->id, 'updated_at' => now()]);

        $this->expectException(InvalidArgumentException::class);
        app(CancellationService::class)->apply($session, null, 'Kegiatan akademik diganti Tahfizh');
    }

    private function fixtures(string $start, string $status): array
    {
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-CANCEL', 'unit_name' => 'Cancellation Unit', 'unit_type' => 'SCHOOL']);
        $grade = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '1', 'display_name' => 'Tingkat 1', 'sequence_no' => 1]);
        $year = AcademicYear::create(['year_code' => '2099-CANCEL', 'display_name' => '2099/2100', 'starts_on' => '2099-07-01', 'ends_on' => '2100-06-30']);
        $semester = Semester::create(['academic_year_id' => $year->id, 'semester_code' => 'ODD', 'display_name' => 'Semester Ganjil', 'sequence_no' => 1, 'starts_on' => '2099-07-01', 'ends_on' => '2099-12-31']);
        $class = AcademicClass::create(['class_code' => 'CLASS-CANCEL-A', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id, 'section_code' => 'A', 'display_name' => 'Kelas 1 A']);
        $subject = Subject::create(['subject_code' => 'SUBJ-CANCEL', 'subject_name' => 'Cancellation Subject']);
        $staff = Staff::create(['staff_code' => 'STAFF-CANCEL-001', 'full_name' => 'Cancellation Staff']);
        $assignment = TeachingAssignment::create(['assignment_code' => 'TA-CANCEL-001', 'semester_id' => $semester->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $staff->id, 'effective_from' => '2099-07-01', 'workflow_status' => 'ACTIVE']);
        $plannedStart = Carbon::parse($start);
        $session = ClassSession::create(['session_code' => 'SESSION-CANCEL-'.str()->uuid(), 'teaching_assignment_id' => $assignment->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'planned_start_at' => $plannedStart, 'planned_end_at' => $plannedStart->copy()->addHour(), 'session_source' => 'SCHEDULED', 'participant_scope' => 'FULL_CLASS', 'session_status' => $status]);

        return [$session];
    }
}
