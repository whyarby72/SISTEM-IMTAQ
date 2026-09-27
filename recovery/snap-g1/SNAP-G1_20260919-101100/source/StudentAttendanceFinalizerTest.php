<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\ClassHomeroomAssignment;
use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\GradeLevel;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\SessionStudentParticipant;
use App\Domains\Academic\Models\SessionTeacherParticipation;
use App\Domains\Academic\Models\StudentAttendance;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Models\TeachingAssignment;
use App\Domains\Academic\Services\StudentAttendanceDraftService;
use App\Domains\Academic\Services\StudentAttendanceFinalizer;
use App\Models\User;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use App\Shared\Core\Models\Staff;
use App\Shared\Core\Models\Student;
use App\Shared\Platform\Audit\Models\AuditLog;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class StudentAttendanceFinalizerTest extends TestCase
{
    use RefreshDatabase;

    public function test_wali_kelas_finalizes_all_required_drafts_and_completes_eligible_session(): void
    {
        [$session, $first, $second, $homeroom, $actor] = $this->fixtures();
        $drafts = app(StudentAttendanceDraftService::class);
        $drafts->save($session, $first, $homeroom, $actor->id, ['attendance_status' => 'PRESENT', 'notes' => 'Masuk di tengah sesi']);
        $drafts->save($session, $second, $homeroom, $actor->id, ['attendance_status' => 'IZIN', 'notes' => 'Izin untuk sesi ini']);

        $result = app(StudentAttendanceFinalizer::class)->finalize($session, $homeroom, $actor->id);

        $this->assertSame('COMPLETED', $result->session_status);
        $this->assertSame(2, StudentAttendance::where('workflow_status', 'VALIDATED')->count());
        $this->assertSame('IZIN', StudentAttendance::where('session_student_participant_id', $second->id)->value('attendance_status'));
        $this->assertNull(StudentAttendance::where('session_student_participant_id', $second->id)->value('permission_event_id'));
        $this->assertSame('Masuk di tengah sesi', StudentAttendance::where('session_student_participant_id', $first->id)->value('notes'));
        $this->assertSame(2, AuditLog::where('action', 'STUDENT_ATTENDANCE_FINALIZED')->count());
    }

    public function test_repeated_finalization_is_idempotent_and_does_not_duplicate_audit(): void
    {
        [$session, $first, $second, $homeroom, $actor] = $this->fixtures();
        $drafts = app(StudentAttendanceDraftService::class);
        $drafts->save($session, $first, $homeroom, $actor->id, ['attendance_status' => 'PRESENT']);
        $drafts->save($session, $second, $homeroom, $actor->id, ['attendance_status' => 'ABSENT']);

        $finalizer = app(StudentAttendanceFinalizer::class);
        $finalizer->finalize($session, $homeroom, $actor->id);
        $versionAfterFirstFinalization = $session->fresh()->version_no;
        $finalizer->finalize($session, $homeroom, $actor->id);

        $this->assertSame($versionAfterFirstFinalization, $session->fresh()->version_no);
        $this->assertSame(2, AuditLog::where('action', 'STUDENT_ATTENDANCE_FINALIZED')->count());
        $this->assertSame(2, StudentAttendance::where('workflow_status', 'VALIDATED')->count());
    }

    public function test_incomplete_attendance_rolls_back_without_completing_session(): void
    {
        [$session, $first, $second, $homeroom, $actor] = $this->fixtures();
        app(StudentAttendanceDraftService::class)->save($session, $first, $homeroom, $actor->id, ['attendance_status' => 'PRESENT']);

        $this->expectException(InvalidArgumentException::class);
        try {
            app(StudentAttendanceFinalizer::class)->finalize($session, $homeroom, $actor->id);
        } finally {
            $this->assertSame('PLANNED', $session->fresh()->session_status);
            $this->assertSame('DRAFT', StudentAttendance::first()->workflow_status);
            $this->assertSame(1, StudentAttendance::count());
        }
    }

    public function test_non_homeroom_and_cancelled_session_are_rejected(): void
    {
        [$session, $first, $second, , $actor, $otherStaff] = $this->fixtures();
        $this->expectException(AuthorizationException::class);
        app(StudentAttendanceFinalizer::class)->finalize($session, $otherStaff, $actor->id);
    }

    public function test_cancelled_session_cannot_be_finalized(): void
    {
        [$session, , , $homeroom, $actor] = $this->fixtures();
        $session->update(['session_status' => 'CANCELLED']);

        $this->expectException(InvalidArgumentException::class);
        app(StudentAttendanceFinalizer::class)->finalize($session, $homeroom, $actor->id);
    }

    public function test_stale_attendance_version_is_rejected(): void
    {
        [$session, $first, $second, $homeroom, $actor] = $this->fixtures();
        $drafts = app(StudentAttendanceDraftService::class);
        $drafts->save($session, $first, $homeroom, $actor->id, ['attendance_status' => 'PRESENT']);
        $drafts->save($session, $second, $homeroom, $actor->id, ['attendance_status' => 'ABSENT']);

        $this->expectException(InvalidArgumentException::class);
        app(StudentAttendanceFinalizer::class)->finalize($session, $homeroom, $actor->id, [$first->id => 99]);
    }

    public function test_absent_primary_teacher_requires_present_substitute_before_finalization(): void
    {
        [$session, $first, $second, $homeroom, $actor, , $primary] = $this->fixtures();
        $drafts = app(StudentAttendanceDraftService::class);
        $drafts->save($session, $first, $homeroom, $actor->id, ['attendance_status' => 'PRESENT']);
        $drafts->save($session, $second, $homeroom, $actor->id, ['attendance_status' => 'PRESENT']);

        $primary->update(['attendance_status' => 'SICK', 'reason' => 'Sakit']);

        try {
            app(StudentAttendanceFinalizer::class)->finalize($session, $homeroom, $actor->id);
            $this->fail('Finalization should require a present substitute.');
        } catch (InvalidArgumentException $exception) {
            $this->assertSame('Guru utama tidak hadir. Pilih guru pengganti atau Wali Kelas dan catat hadir sebelum mengesahkan absensi santri.', $exception->getMessage());
        }

        $this->assertSame('PLANNED', $session->fresh()->session_status);
        $this->assertSame('DRAFT', StudentAttendance::where('session_student_participant_id', $first->id)->value('workflow_status'));

        SessionTeacherParticipation::create([
            'class_session_id' => $session->id,
            'teacher_staff_id' => $homeroom->id,
            'role' => 'SUBSTITUTE',
            'obligation_type' => 'REPLACEMENT',
            'attendance_status' => 'PRESENT',
            'reason' => 'Wali Kelas menangani sesi',
        ]);

        $result = app(StudentAttendanceFinalizer::class)->finalize($session, $homeroom, $actor->id);

        $this->assertSame('COMPLETED', $result->session_status);
    }

    public function test_missing_primary_teacher_blocks_finalization_without_mutation(): void
    {
        [$session, $first, $second, $homeroom, $actor, , $primary] = $this->fixtures();
        $drafts = app(StudentAttendanceDraftService::class);
        $drafts->save($session, $first, $homeroom, $actor->id, ['attendance_status' => 'PRESENT']);
        $drafts->save($session, $second, $homeroom, $actor->id, ['attendance_status' => 'PRESENT']);
        $primary->delete();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Guru utama sesi belum tercatat; sesi belum dapat disahkan.');
        app(StudentAttendanceFinalizer::class)->finalize($session, $homeroom, $actor->id);
    }

    public function test_unresolved_primary_teacher_blocks_finalization_without_mutation(): void
    {
        [$session, $first, $second, $homeroom, $actor, , $primary] = $this->fixtures();
        $drafts = app(StudentAttendanceDraftService::class);
        $drafts->save($session, $first, $homeroom, $actor->id, ['attendance_status' => 'PRESENT']);
        $drafts->save($session, $second, $homeroom, $actor->id, ['attendance_status' => 'PRESENT']);
        $primary->update(['attendance_status' => null]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Status kehadiran guru utama harus dicatat sebelum kehadiran santri disahkan.');
        app(StudentAttendanceFinalizer::class)->finalize($session, $homeroom, $actor->id);
    }

    public function test_each_non_present_primary_status_requires_present_substitute(): void
    {
        [$session, $first, $second, $homeroom, $actor, , $primary] = $this->fixtures();
        $drafts = app(StudentAttendanceDraftService::class);
        $drafts->save($session, $first, $homeroom, $actor->id, ['attendance_status' => 'PRESENT']);
        $drafts->save($session, $second, $homeroom, $actor->id, ['attendance_status' => 'PRESENT']);

        foreach (['ABSENT', 'SICK', 'IZIN', 'OTHER'] as $status) {
            $primary->update(['attendance_status' => $status]);
            try {
                app(StudentAttendanceFinalizer::class)->finalize($session, $homeroom, $actor->id);
                $this->fail("{$status} primary should require a present substitute.");
            } catch (InvalidArgumentException $exception) {
                $this->assertStringContainsString('Guru utama tidak hadir.', $exception->getMessage());
            }
            $this->assertSame('PLANNED', $session->fresh()->session_status);
            $this->assertSame('DRAFT', StudentAttendance::where('session_student_participant_id', $first->id)->value('workflow_status'));
        }
    }

    public function test_null_or_non_present_substitute_does_not_cover_absent_primary(): void
    {
        [$session, $first, $second, $homeroom, $actor, , $primary] = $this->fixtures();
        $drafts = app(StudentAttendanceDraftService::class);
        $drafts->save($session, $first, $homeroom, $actor->id, ['attendance_status' => 'PRESENT']);
        $drafts->save($session, $second, $homeroom, $actor->id, ['attendance_status' => 'PRESENT']);
        $primary->update(['attendance_status' => 'ABSENT']);
        $substitute = SessionTeacherParticipation::create(['class_session_id' => $session->id, 'teacher_staff_id' => $homeroom->id, 'role' => 'SUBSTITUTE', 'obligation_type' => 'REPLACEMENT']);

        foreach ([null, 'ABSENT', 'SICK', 'IZIN', 'OTHER'] as $status) {
            $substitute->update(['attendance_status' => $status]);
            try {
                app(StudentAttendanceFinalizer::class)->finalize($session, $homeroom, $actor->id);
                $this->fail('A non-present substitute should not cover an absent primary.');
            } catch (InvalidArgumentException $exception) {
                $this->assertStringContainsString('Guru utama tidak hadir.', $exception->getMessage());
            }
        }
    }

    private function fixtures(): array
    {
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-SAF', 'unit_name' => 'Student Attendance Finalizer Unit', 'unit_type' => 'SCHOOL']);
        $grade = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '1', 'display_name' => 'Tingkat 1', 'sequence_no' => 1]);
        $year = AcademicYear::create(['year_code' => '2026-SAF', 'display_name' => '2026/2027', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $semester = Semester::create(['academic_year_id' => $year->id, 'semester_code' => 'ODD', 'display_name' => 'Semester Ganjil', 'sequence_no' => 1, 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-31']);
        $class = AcademicClass::create(['class_code' => 'CLASS-SAF-A', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id, 'section_code' => 'A', 'display_name' => 'Kelas 1 A']);
        $subject = Subject::create(['subject_code' => 'SUBJ-SAF', 'subject_name' => 'Student Attendance Finalizer Subject']);
        $teacher = Staff::create(['staff_code' => 'STAFF-SAF-001', 'full_name' => 'Teacher']);
        $homeroom = Staff::create(['staff_code' => 'STAFF-SAF-002', 'full_name' => 'Wali Kelas']);
        $otherStaff = Staff::create(['staff_code' => 'STAFF-SAF-003', 'full_name' => 'Other Staff']);
        $actor = User::factory()->create();
        ClassHomeroomAssignment::create(['class_id' => $class->id, 'staff_id' => $homeroom->id, 'effective_from' => '2026-07-01']);
        $assignment = TeachingAssignment::create(['assignment_code' => 'TA-SAF-001', 'semester_id' => $semester->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $teacher->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);
        $session = ClassSession::create(['session_code' => 'SESSION-SAF-001', 'teaching_assignment_id' => $assignment->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'planned_start_at' => '2026-07-06 08:00:00', 'planned_end_at' => '2026-07-06 09:30:00', 'session_source' => 'SCHEDULED', 'participant_scope' => 'FULL_CLASS', 'session_status' => 'PLANNED']);
        $first = SessionStudentParticipant::create(['class_session_id' => $session->id, 'student_id' => Student::create(['student_code' => 'STU-SAF-001', 'full_name' => 'First Student'])->id, 'participant_basis' => 'CLASS_ENROLLMENT']);
        $second = SessionStudentParticipant::create(['class_session_id' => $session->id, 'student_id' => Student::create(['student_code' => 'STU-SAF-002', 'full_name' => 'Second Student'])->id, 'participant_basis' => 'CLASS_ENROLLMENT']);
        $primary = SessionTeacherParticipation::create(['class_session_id' => $session->id, 'teacher_staff_id' => $teacher->id, 'role' => 'PRIMARY', 'obligation_type' => 'TEACHING_ASSIGNMENT', 'attendance_status' => 'PRESENT']);

        return [$session, $first, $second, $homeroom, $actor, $otherStaff, $primary];
    }
}
