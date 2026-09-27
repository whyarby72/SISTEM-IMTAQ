<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\ClassSessionGroup;
use App\Domains\Academic\Models\GradeLevel;
use App\Domains\Academic\Models\ScheduleRule;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\SessionStudentParticipant;
use App\Domains\Academic\Models\StudentAttendance;
use App\Domains\Academic\Models\StudentClassEnrollment;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Models\TeachingAssignment;
use App\Domains\Academic\Services\CanonicalSessionOccurrenceService;
use App\Models\User;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use App\Shared\Core\Models\Staff;
use App\Shared\Core\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;
use Tests\TestCase;

class CanonicalSessionOccurrenceServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_initial_scheduled_creates_version_one_and_effective_pointer(): void
    {
        [$session, $actor] = $this->fixtures();

        $version = $this->service()->record($session, $actor, 'SCHEDULED');

        $this->assertSame(1, $version->version_no);
        $this->assertSame($version->id, $session->fresh()->effective_occurrence_version_id);
    }

    public function test_initial_held_is_allowed_without_synthetic_scheduled_version(): void
    {
        [$session, $actor] = $this->fixtures();

        $version = $this->service()->record($session, $actor, 'HELD');

        $this->assertSame('HELD', $version->occurrence_status);
        $this->assertSame(1, $session->occurrenceVersions()->count());
    }

    public function test_held_materializes_participant_snapshot(): void
    {
        [$session, $actor] = $this->fixtures();

        $this->service()->record($session, $actor, 'HELD');

        $this->assertDatabaseCount('session_student_participants', 1);
    }

    public function test_held_does_not_require_complete_attendance(): void
    {
        [$session, $actor] = $this->fixtures();

        $version = $this->service()->record($session, $actor, 'HELD');

        $this->assertSame('HELD', $version->occurrence_status);
        $this->assertDatabaseCount('student_attendance', 0);
    }

    public function test_partial_held_requires_partial_reason(): void
    {
        [$session, $actor] = $this->fixtures();

        $this->expectException(InvalidArgumentException::class);
        $this->service()->record($session, $actor, 'HELD', ['is_partial' => true]);
    }

    public function test_non_partial_held_does_not_retain_partial_reason(): void
    {
        [$session, $actor] = $this->fixtures();

        $this->expectException(InvalidArgumentException::class);
        $this->service()->record($session, $actor, 'HELD', ['partial_reason' => 'Selesai lebih awal']);
    }

    public function test_cancelled_requires_reason(): void
    {
        [$session, $actor] = $this->fixtures();

        $this->expectException(InvalidArgumentException::class);
        $this->service()->record($session, $actor, 'CANCELLED');
    }

    public function test_routine_cancellation_with_existing_attendance_is_rejected(): void
    {
        [$session, $actor] = $this->fixtures();
        $this->createAttendance($session, $actor);

        $this->expectException(InvalidArgumentException::class);
        $this->service()->record($session, $actor, 'CANCELLED', ['reason' => 'Kegiatan dibatalkan']);
    }

    public function test_rescheduled_requires_replacement_session_and_reason(): void
    {
        [$session, $actor] = $this->fixtures();

        $this->expectException(InvalidArgumentException::class);
        $this->service()->record($session, $actor, 'RESCHEDULED', ['reason' => 'Pindah jadwal']);
    }

    public function test_rescheduled_replacement_cannot_equal_source_session(): void
    {
        [$session, $actor] = $this->fixtures();

        $this->expectException(InvalidArgumentException::class);
        $this->service()->record($session, $actor, 'RESCHEDULED', ['reason' => 'Pindah jadwal', 'replacement_class_session_id' => $session->id]);
    }

    public function test_routine_reschedule_with_existing_attendance_is_rejected(): void
    {
        [$session, $actor] = $this->fixtures();
        $replacement = $this->replacement($session);
        $this->createAttendance($session, $actor);

        $this->expectException(InvalidArgumentException::class);
        $this->service()->record($session, $actor, 'RESCHEDULED', ['reason' => 'Pindah jadwal', 'replacement_class_session_id' => $replacement->id]);
    }

    public function test_scheduled_to_held_appends_version_two_and_preserves_version_one(): void
    {
        [$session, $actor] = $this->fixtures();
        $first = $this->service()->record($session, $actor, 'SCHEDULED');

        $second = $this->service()->record($session, $actor, 'HELD');

        $this->assertSame(2, $second->version_no);
        $this->assertSame($first->id, $second->supersedes_version_id);
        $this->assertDatabaseCount('session_occurrence_versions', 2);
    }

    public function test_scheduled_to_cancelled_appends_version_two(): void
    {
        [$session, $actor] = $this->fixtures();
        $this->service()->record($session, $actor, 'SCHEDULED');

        $version = $this->service()->record($session, $actor, 'CANCELLED', ['reason' => 'Libur resmi']);

        $this->assertSame(2, $version->version_no);
        $this->assertSame('CANCELLED', $version->occurrence_status);
    }

    public function test_terminal_routine_overwrite_is_rejected(): void
    {
        [$session, $actor] = $this->fixtures();
        $this->service()->record($session, $actor, 'HELD');

        $this->expectException(InvalidArgumentException::class);
        $this->service()->record($session, $actor, 'CANCELLED', ['reason' => 'Koreksi']);
    }

    public function test_correction_appends_new_version_instead_of_updating_old_one(): void
    {
        [$session, $actor] = $this->fixtures();
        $first = $this->service()->record($session, $actor, 'HELD');

        $second = $this->service()->correctEffectiveOccurrence($session, $actor, 'CANCELLED', 'Bukti menunjukkan KBM tidak berlangsung.');

        $this->assertSame(2, $second->version_no);
        $this->assertSame('HELD', $first->fresh()->occurrence_status);
        $this->assertSame(2, $session->occurrenceVersions()->count());
    }

    public function test_correction_requires_explicit_reason(): void
    {
        [$session, $actor] = $this->fixtures();
        $this->service()->record($session, $actor, 'SCHEDULED');

        $this->expectException(InvalidArgumentException::class);
        $this->service()->correctEffectiveOccurrence($session, $actor, 'CANCELLED', ' ');
    }

    public function test_effective_pointer_moves_to_newest_version(): void
    {
        [$session, $actor] = $this->fixtures();
        $this->service()->record($session, $actor, 'SCHEDULED');

        $second = $this->service()->record($session, $actor, 'HELD');

        $this->assertSame($second->id, $session->fresh()->effective_occurrence_version_id);
    }

    public function test_supersedes_points_to_previous_effective_version(): void
    {
        [$session, $actor] = $this->fixtures();
        $first = $this->service()->record($session, $actor, 'SCHEDULED');

        $second = $this->service()->record($session, $actor, 'CANCELLED', ['reason' => 'Libur resmi']);

        $this->assertSame($first->id, $second->supersedes_version_id);
    }

    public function test_joint_session_produces_only_one_occurrence_chain(): void
    {
        [$session, $actor] = $this->fixtures();
        ClassSessionGroup::create(['class_session_id' => $session->id, 'class_id' => $session->class_id, 'scope_role' => 'TEACHING_SCOPE']);

        $this->service()->record($session, $actor, 'HELD');

        $this->assertDatabaseCount('session_occurrence_versions', 1);
    }

    public function test_legacy_class_session_status_remains_unchanged(): void
    {
        [$session, $actor] = $this->fixtures();

        $this->service()->record($session, $actor, 'HELD');

        $this->assertSame('PLANNED', $session->fresh()->session_status);
    }

    public function test_actor_is_required_and_persisted(): void
    {
        [$session, $actor] = $this->fixtures();

        $version = $this->service()->record($session, $actor, 'SCHEDULED');

        $this->assertSame($actor->id, $version->recorded_by_user_id);
    }

    public function test_transaction_rollback_leaves_no_orphan_version_or_moved_pointer(): void
    {
        [$session, $actor] = $this->fixtures();

        try {
            DB::transaction(function () use ($session, $actor): void {
                $this->service()->record($session, $actor, 'SCHEDULED');
                throw new RuntimeException('force rollback');
            });
        } catch (RuntimeException) {
            // Expected test rollback.
        }

        $this->assertDatabaseCount('session_occurrence_versions', 0);
        $this->assertNull($session->fresh()->effective_occurrence_version_id);
    }

    private function service(): CanonicalSessionOccurrenceService
    {
        return app(CanonicalSessionOccurrenceService::class);
    }

    private function createAttendance(ClassSession $session, User $actor): void
    {
        $participant = SessionStudentParticipant::create([
            'class_session_id' => $session->id,
            'student_id' => Student::query()->firstOrFail()->id,
            'participant_basis' => 'CLASS_ENROLLMENT',
            'participant_status' => 'EXPECTED',
            'is_required' => true,
        ]);
        StudentAttendance::create([
            'session_student_participant_id' => $participant->id,
            'attendance_status' => 'PRESENT',
            'workflow_status' => 'DRAFT',
            'entered_by' => $actor->id,
            'entered_at' => now(),
            'updated_by' => $actor->id,
            'updated_at' => now(),
        ]);
    }

    private function replacement(ClassSession $source): ClassSession
    {
        return ClassSession::create([
            'session_code' => 'REPLACEMENT-'.str()->uuid(),
            'teaching_assignment_id' => $source->teaching_assignment_id,
            'schedule_rule_id' => $source->schedule_rule_id,
            'class_id' => $source->class_id,
            'subject_id' => $source->subject_id,
            'planned_start_at' => '2026-07-07 08:00:00',
            'planned_end_at' => '2026-07-07 09:30:00',
            'session_source' => 'RESCHEDULED',
            'participant_scope' => 'FULL_CLASS',
            'session_status' => 'PLANNED',
            'rescheduled_from_session_id' => $source->id,
        ]);
    }

    private function fixtures(): array
    {
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-OCCURRENCE', 'unit_name' => 'Occurrence Unit', 'unit_type' => 'SCHOOL']);
        $grade = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '1', 'display_name' => 'Tingkat 1', 'sequence_no' => 1]);
        $year = AcademicYear::create(['year_code' => '2026-OCCURRENCE', 'display_name' => '2026/2027', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $semester = Semester::create(['academic_year_id' => $year->id, 'semester_code' => 'ODD', 'display_name' => 'Semester Ganjil', 'sequence_no' => 1, 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-31']);
        $class = AcademicClass::create(['class_code' => 'CLASS-OCCURRENCE-A', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id, 'section_code' => 'A', 'display_name' => 'Kelas Occurrence A']);
        $subject = Subject::create(['subject_code' => 'SUBJ-OCCURRENCE', 'subject_name' => 'Occurrence Subject']);
        $staff = Staff::create(['staff_code' => 'STAFF-OCCURRENCE-001', 'full_name' => 'Occurrence Staff']);
        $assignment = TeachingAssignment::create(['assignment_code' => 'TA-OCCURRENCE-001', 'semester_id' => $semester->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $staff->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);
        $rule = ScheduleRule::create(['teaching_assignment_id' => $assignment->id, 'weekday' => 1, 'start_time' => '08:00', 'end_time' => '09:30', 'recurrence_type' => 'EVERY_WEEK', 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);
        $session = ClassSession::create(['session_code' => 'SESSION-OCCURRENCE-001', 'teaching_assignment_id' => $assignment->id, 'schedule_rule_id' => $rule->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'planned_start_at' => '2026-07-06 08:00:00', 'planned_end_at' => '2026-07-06 09:30:00', 'session_source' => 'SCHEDULED', 'participant_scope' => 'FULL_CLASS', 'session_status' => 'PLANNED']);
        $student = Student::create(['student_code' => 'STU-OCCURRENCE-001', 'full_name' => 'Occurrence Student']);
        StudentClassEnrollment::create(['student_id' => $student->id, 'class_id' => $class->id, 'effective_from' => '2026-07-01', 'status' => 'ACTIVE']);

        return [$session, User::factory()->create()];
    }
}
