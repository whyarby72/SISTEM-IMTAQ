<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\ClassHomeroomAssignment;
use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\GradeLevel;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\SessionStudentParticipant;
use App\Domains\Academic\Models\StudentAttendance;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Models\TeachingAssignment;
use App\Domains\Academic\Services\CancellationService;
use App\Domains\Academic\Services\StudentAttendanceDraftService;
use App\Models\User;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use App\Shared\Core\Models\Staff;
use App\Shared\Core\Models\Student;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class CancellationConcurrencyTest extends TestCase
{
    use DatabaseMigrations;

    public function test_attendance_first_race_serializes_cancellation_after_the_session_lock(): void
    {
        [$session, $participant, $staff, $actor] = $this->fixtures('ATTENDANCE_FIRST');
        $result = $this->runRace($session, function () use ($session, $participant, $staff, $actor): void {
            app(StudentAttendanceDraftService::class)->save(
                $session,
                $participant,
                $staff,
                $actor->id,
                ['attendance_status' => 'PRESENT'],
            );
        }, function () use ($session): void {
            app(CancellationService::class)->apply($session, null, 'Concurrent cancellation');
        });

        $this->assertSame('REJECTED', $result);
        $this->assertSame('PLANNED', $session->fresh()->session_status);
        $this->assertSame(1, StudentAttendance::where('session_student_participant_id', $participant->id)->count());
        $this->assertSame(0, $session->scheduleChanges()->count());
    }

    public function test_cancellation_first_race_serializes_attendance_after_the_session_lock(): void
    {
        [$session, $participant, $staff, $actor] = $this->fixtures('CANCELLATION_FIRST');
        $result = $this->runRace($session, function () use ($session): void {
            app(CancellationService::class)->apply($session, null, 'Concurrent cancellation');
        }, function () use ($session, $participant, $staff, $actor): void {
            app(StudentAttendanceDraftService::class)->save(
                $session,
                $participant,
                $staff,
                $actor->id,
                ['attendance_status' => 'PRESENT'],
            );
        });

        $this->assertSame('REJECTED', $result);
        $this->assertSame('CANCELLED', $session->fresh()->session_status);
        $this->assertSame(0, StudentAttendance::where('session_student_participant_id', $participant->id)->count());
        $this->assertSame(1, $session->scheduleChanges()->count());
    }

    /**
     * The parent transaction owns the ClassSession row lock. The child starts
     * the competing operation only after the parent confirms the lock is held.
     * The parent then executes the first operation, commits, and lets the
     * child re-check the locked authoritative state.
     */
    private function runRace(ClassSession $session, callable $firstOperation, callable $secondOperation): string
    {
        $connection = db()->connection();
        $connection->beginTransaction();
        ClassSession::query()->whereKey($session->id)->lockForUpdate()->firstOrFail();

        [$parentSocket, $childSocket] = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, 0);
        $pid = pcntl_fork();
        if ($pid === -1) {
            throw new \RuntimeException('Unable to fork PostgreSQL concurrency test worker.');
        }

        if ($pid === 0) {
            fclose($parentSocket);
            db()->purge();
            fwrite($childSocket, "READY\n");
            fflush($childSocket);

            try {
                $secondOperation();
                fwrite($childSocket, "COMPLETED\n");
            } catch (\Throwable $exception) {
                fwrite($childSocket, "REJECTED\n");
            }
            fflush($childSocket);
            fclose($childSocket);
            exit(0);
        }

        fclose($childSocket);
        $this->assertSame("READY\n", fgets($parentSocket));
        $firstOperation();
        $connection->commit();
        $result = trim((string) fgets($parentSocket));
        pcntl_waitpid($pid, $status);
        fclose($parentSocket);

        return $result;
    }

    private function fixtures(string $suffix): array
    {
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-RACE-'.$suffix, 'unit_name' => 'Cancellation Race Unit', 'unit_type' => 'SCHOOL']);
        $grade = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '1', 'display_name' => 'Tingkat 1', 'sequence_no' => 1]);
        $year = AcademicYear::create(['year_code' => '2026-RACE-'.$suffix, 'display_name' => '2026/2027', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $semester = Semester::create(['academic_year_id' => $year->id, 'semester_code' => 'ODD', 'display_name' => 'Semester Ganjil', 'sequence_no' => 1, 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-31']);
        $class = AcademicClass::create(['class_code' => 'CLASS-RACE-'.$suffix, 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id, 'section_code' => 'A', 'display_name' => 'Kelas Race']);
        $subject = Subject::create(['subject_code' => 'SUBJ-RACE-'.$suffix, 'subject_name' => 'Cancellation Race Subject']);
        $staff = Staff::create(['staff_code' => 'STAFF-RACE-'.$suffix, 'full_name' => 'Race Wali']);
        $actor = User::factory()->create();
        ClassHomeroomAssignment::create(['class_id' => $class->id, 'staff_id' => $staff->id, 'effective_from' => '2026-07-01']);
        $assignment = TeachingAssignment::create(['assignment_code' => 'TA-RACE-'.$suffix, 'semester_id' => $semester->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $staff->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);
        $session = ClassSession::create(['session_code' => 'SESSION-RACE-'.$suffix, 'teaching_assignment_id' => $assignment->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'planned_start_at' => '2026-07-06 08:00:00', 'planned_end_at' => '2026-07-06 09:30:00', 'session_source' => 'SCHEDULED', 'participant_scope' => 'FULL_CLASS', 'session_status' => 'PLANNED']);
        $student = Student::create(['student_code' => 'STU-RACE-'.$suffix, 'full_name' => 'Race Student']);
        $participant = SessionStudentParticipant::create(['class_session_id' => $session->id, 'student_id' => $student->id, 'participant_basis' => 'CLASS_ENROLLMENT']);

        return [$session, $participant, $staff, $actor];
    }
}
