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
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\RunClassInSeparateProcess;
use Tests\TestCase;

#[RunClassInSeparateProcess]
class CancellationConcurrencyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate:fresh');
        $this->beforeApplicationDestroyed(fn () => $this->artisan('migrate:fresh'));
    }

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

        $this->assertRaceRejection($result, 'Session changed before cancellation could be applied.');
        $this->assertSame('Lock', $result['wait_event_type']);
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

        $this->assertRaceRejection($result, 'Only planned or confirmed sessions can accept attendance drafts.');
        $this->assertSame('Lock', $result['wait_event_type']);
        $this->assertSame('CANCELLED', $session->fresh()->session_status);
        $this->assertSame(0, StudentAttendance::where('session_student_participant_id', $participant->id)->count());
        $this->assertSame(1, $session->scheduleChanges()->count());
    }

    /**
     * The parent transaction owns the ClassSession row lock. The child uses a
     * fresh PostgreSQL backend, announces that it is about to compete, and is
     * observed in pg_stat_activity waiting on the row lock before the parent
     * commits its first operation.
     *
     * @return array{outcome:string,exception_class:?string,exception_message:?string,parent_backend_pid:int,child_backend_pid:int,wait_event_type:?string,wait_event:?string}
     */
    private function runRace(ClassSession $session, callable $firstOperation, callable $secondOperation): array
    {
        $this->assertTrue(function_exists('pcntl_fork'), 'Concurrency evidence requires pcntl_fork.');
        $this->assertTrue(function_exists('stream_socket_pair'), 'Concurrency evidence requires stream_socket_pair.');
        $this->assertTrue(function_exists('posix_kill'), 'Concurrency evidence requires isolated child termination.');

        $connection = DB::connection();
        $this->assertSame('pgsql', $connection->getDriverName(), 'Concurrency evidence requires disposable PostgreSQL.');
        $connection->getPdo();
        $parentBackendPid = (int) DB::selectOne('SELECT pg_backend_pid() AS pid')->pid;
        $connection->beginTransaction();
        ClassSession::query()->whereKey($session->id)->lockForUpdate()->firstOrFail();

        $sockets = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, 0);
        $this->assertNotFalse($sockets, 'Unable to create the bounded concurrency coordination socket.');
        [$parentSocket, $childSocket] = $sockets;
        $pid = pcntl_fork();
        if ($pid === -1) {
            throw new \RuntimeException('Unable to fork PostgreSQL concurrency test worker.');
        }

        if ($pid === 0) {
            fclose($parentSocket);
            DB::purge();
            $childConnection = DB::connection();
            $childConnection->getPdo();
            $childBackendPid = (int) DB::selectOne('SELECT pg_backend_pid() AS pid')->pid;
            $this->writeProtocol($childSocket, [
                'event' => 'READY',
                'child_backend_pid' => $childBackendPid,
            ]);

            try {
                $this->readProtocol($childSocket, 'GO');
                $this->writeProtocol($childSocket, ['event' => 'STARTED']);
                $secondOperation();
                $this->writeProtocol($childSocket, [
                    'event' => 'RESULT',
                    'outcome' => 'COMPLETED',
                    'exception_class' => null,
                    'exception_message' => null,
                ]);
            } catch (\Throwable $exception) {
                $this->writeProtocol($childSocket, [
                    'event' => 'RESULT',
                    'outcome' => 'REJECTED',
                    'exception_class' => $exception::class,
                    'exception_message' => $exception->getMessage(),
                ]);
            }
            fclose($childSocket);
            posix_kill(getmypid(), SIGKILL);
        }

        fclose($childSocket);
        try {
            $ready = $this->readProtocol($parentSocket, 'child READY');
            $this->assertSame('READY', $ready['event']);
            $childBackendPid = (int) $ready['child_backend_pid'];
            $this->assertNotSame($parentBackendPid, $childBackendPid, 'Parent and child must use independent PostgreSQL backends.');

            $this->writeProtocol($parentSocket, ['event' => 'GO']);
            $started = $this->readProtocol($parentSocket, 'child STARTED');
            $this->assertSame('STARTED', $started['event']);
            $wait = $this->awaitChildLockWait($connection, $childBackendPid);

            $firstOperation();
            $connection->commit();

            $result = $this->readProtocol($parentSocket, 'child RESULT');
            $this->assertSame('RESULT', $result['event']);

            return [
                'outcome' => (string) $result['outcome'],
                'exception_class' => $result['exception_class'] ?? null,
                'exception_message' => $result['exception_message'] ?? null,
                'parent_backend_pid' => $parentBackendPid,
                'child_backend_pid' => $childBackendPid,
                'wait_event_type' => $wait['wait_event_type'] ?? null,
                'wait_event' => $wait['wait_event'] ?? null,
            ];
        } finally {
            if ($connection->transactionLevel() > 0) {
                $connection->rollBack();
            }
            pcntl_waitpid($pid, $status);
            fclose($parentSocket);
        }
    }

    /** @param array<string,mixed> $result */
    private function assertRaceRejection(array $result, string $expectedMessage): void
    {
        $this->assertSame('REJECTED', $result['outcome']);
        $this->assertSame(\InvalidArgumentException::class, $result['exception_class']);
        $this->assertSame($expectedMessage, $result['exception_message']);
        $this->assertNotSame($result['parent_backend_pid'], $result['child_backend_pid']);
    }

    /** @param resource $socket @param array<string,mixed> $payload */
    private function writeProtocol($socket, array $payload): void
    {
        $encoded = json_encode($payload, JSON_THROW_ON_ERROR)."\n";
        $written = fwrite($socket, $encoded);
        if ($written === false || $written < strlen($encoded)) {
            throw new \RuntimeException('Concurrency protocol write failed.');
        }
        fflush($socket);
    }

    /** @return array<string,mixed> */
    private function readProtocol($socket, string $context): array
    {
        stream_set_timeout($socket, 10);
        $line = fgets($socket);
        $metadata = stream_get_meta_data($socket);
        if ($line === false) {
            $suffix = ($metadata['timed_out'] ?? false) ? ' (timed out)' : '';
            throw new \RuntimeException("Concurrency protocol read failed for {$context}{$suffix}.");
        }

        $payload = json_decode(trim($line), true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($payload)) {
            throw new \RuntimeException("Concurrency protocol payload for {$context} was not an object.");
        }

        return $payload;
    }

    /** @return array{wait_event_type:?string,wait_event:?string} */
    private function awaitChildLockWait($connection, int $childBackendPid): array
    {
        $deadline = microtime(true) + 10;
        $lastActivity = null;
        do {
            $activity = $connection->selectOne(
                'SELECT wait_event_type, wait_event FROM pg_stat_activity WHERE pid = ?',
                [$childBackendPid],
            );
            if ($activity !== null) {
                $lastActivity = $activity;
                if ($activity->wait_event_type === 'Lock') {
                    return [
                        'wait_event_type' => $activity->wait_event_type,
                        'wait_event' => $activity->wait_event,
                    ];
                }
            }
            usleep(50_000);
        } while (microtime(true) < $deadline);

        $lastEvent = $lastActivity === null
            ? 'backend not visible'
            : sprintf('%s/%s', $lastActivity->wait_event_type ?? 'none', $lastActivity->wait_event ?? 'none');
        throw new \RuntimeException("Child PostgreSQL backend {$childBackendPid} did not reach a row-lock wait; last={$lastEvent}.");
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
