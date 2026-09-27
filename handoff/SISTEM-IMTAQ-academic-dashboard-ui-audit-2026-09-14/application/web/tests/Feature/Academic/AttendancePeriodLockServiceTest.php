<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\AttendancePeriodLock;
use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\GradeLevel;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\SessionStudentParticipant;
use App\Domains\Academic\Models\StudentAttendance;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Models\TeachingAssignment;
use App\Domains\Academic\Services\AttendancePeriodLockService;
use App\Models\User;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use App\Shared\Core\Models\Staff;
use App\Shared\Core\Models\Student;
use App\Shared\Platform\Authorization\Models\Role;
use App\Shared\Platform\Authorization\Models\UserRoleAssignment;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use Tests\TestCase;

class AttendancePeriodLockServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_waka_locks_complete_class_calendar_month_after_fifteen_day_grace(): void
    {
        [$class, $session, $participant, $actor] = $this->fixtures();
        StudentAttendance::create(['session_student_participant_id' => $participant->id, 'attendance_status' => 'PRESENT', 'workflow_status' => 'VALIDATED', 'entered_by' => $actor->id, 'entered_at' => now(), 'finalized_by' => $actor->id, 'finalized_at' => now(), 'updated_by' => $actor->id, 'updated_at' => now()]);

        $lock = app(AttendancePeriodLockService::class)->lock($class, Carbon::parse('2026-07-01'), $actor, Carbon::parse('2026-08-15'));

        $this->assertTrue(Schema::hasTable('attendance_period_locks'));
        $this->assertSame('LOCKED', $lock->status);
        $this->assertSame('2026-07-01', $lock->period_start->toDateString());
        $this->assertSame('2026-07-31', $lock->period_end->toDateString());
        $this->assertSame(1, AttendancePeriodLock::count());
    }

    public function test_lock_requires_complete_attendance_and_waits_until_deadline(): void
    {
        [$class, , , $actor] = $this->fixtures();
        $service = app(AttendancePeriodLockService::class);

        $this->expectException(InvalidArgumentException::class);
        $service->lock($class, Carbon::parse('2026-07-01'), $actor, Carbon::parse('2026-08-14'));
    }

    public function test_lock_after_deadline_still_requires_complete_attendance(): void
    {
        [$class, , , $actor] = $this->fixtures();

        $this->expectException(InvalidArgumentException::class);
        app(AttendancePeriodLockService::class)->lock($class, Carbon::parse('2026-07-01'), $actor, Carbon::parse('2026-08-15'));
    }

    public function test_only_waka_akademik_can_lock(): void
    {
        [$class, , , $actor] = $this->fixtures(false);
        $this->expectException(AuthorizationException::class);

        app(AttendancePeriodLockService::class)->lock($class, Carbon::parse('2026-07-01'), $actor, Carbon::parse('2026-08-15'));
    }

    private function fixtures(bool $waka = true): array
    {
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-LOCK', 'unit_name' => 'Lock Unit', 'unit_type' => 'SCHOOL']);
        $grade = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '1', 'display_name' => 'Tingkat 1', 'sequence_no' => 1]);
        $year = AcademicYear::create(['year_code' => '2026-LOCK', 'display_name' => '2026/2027', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $semester = Semester::create(['academic_year_id' => $year->id, 'semester_code' => 'ODD', 'display_name' => 'Semester Ganjil', 'sequence_no' => 1, 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-31']);
        $class = AcademicClass::create(['class_code' => 'CLASS-LOCK-A', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id, 'section_code' => 'A', 'display_name' => 'Kelas 1 A']);
        $subject = Subject::create(['subject_code' => 'SUBJ-LOCK', 'subject_name' => 'Lock Subject']);
        $teacher = Staff::create(['staff_code' => 'STAFF-LOCK-001', 'full_name' => 'Teacher']);
        $actor = User::factory()->create();
        $role = Role::create(['code' => $waka ? 'WAKA_AKADEMIK' : 'ADMIN', 'name' => $waka ? 'Waka Akademik' : 'Admin']);
        UserRoleAssignment::create(['user_id' => $actor->id, 'role_id' => $role->id, 'effective_from' => '2026-07-01']);
        $assignment = TeachingAssignment::create(['assignment_code' => 'TA-LOCK-001', 'semester_id' => $semester->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $teacher->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);
        $session = ClassSession::create(['session_code' => 'SESSION-LOCK-001', 'teaching_assignment_id' => $assignment->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'planned_start_at' => '2026-07-06 08:00:00', 'planned_end_at' => '2026-07-06 09:30:00', 'session_source' => 'SCHEDULED', 'participant_scope' => 'FULL_CLASS', 'session_status' => 'PLANNED']);
        $participant = SessionStudentParticipant::create(['class_session_id' => $session->id, 'student_id' => Student::create(['student_code' => 'STU-LOCK-001', 'full_name' => 'Student'])->id, 'participant_basis' => 'CLASS_ENROLLMENT']);

        return [$class, $session, $participant, $actor];
    }
}
