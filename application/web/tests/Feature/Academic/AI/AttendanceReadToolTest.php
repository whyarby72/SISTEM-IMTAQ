<?php

namespace Tests\Feature\Academic\AI;

use App\Domains\Academic\AI\AcademicAiToolRegistry;
use App\Domains\Academic\AI\Contracts\AcademicAiToolContext;
use App\Domains\Academic\AI\Contracts\GetStudentAttendanceDetailRequest;
use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\GradeLevel;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\SessionStudentParticipant;
use App\Domains\Academic\Models\StudentAttendance;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Models\TeachingAssignment;
use App\Models\User;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use App\Shared\Core\Models\Staff;
use App\Shared\Core\Models\Student;
use App\Shared\Platform\Authorization\Models\Permission;
use App\Shared\Platform\Authorization\Models\Role;
use App\Shared\Platform\Authorization\Models\UserRoleAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AttendanceReadToolTest extends TestCase
{
    use RefreshDatabase;

    public function test_summary_delegates_to_canonical_semantics_and_preserves_present_late(): void
    {
        [$user, $student] = $this->fixture();
        $class = AcademicClass::query()->firstOrFail();
        $session = ClassSession::create([
            'session_code' => 'AI-A1-SESSION', 'teaching_assignment_id' => TeachingAssignment::query()->first()->id,
            'class_id' => $class->id, 'subject_id' => Subject::query()->first()->id,
            'planned_start_at' => '2026-07-15 08:00:00', 'planned_end_at' => '2026-07-15 09:00:00',
            'session_source' => 'SCHEDULED', 'participant_scope' => 'FULL_CLASS', 'session_status' => 'COMPLETED',
        ]);
        $participant = SessionStudentParticipant::create(['class_session_id' => $session->id, 'student_id' => $student->id, 'participant_basis' => 'CLASS_ENROLLMENT']);
        StudentAttendance::create(['session_student_participant_id' => $participant->id, 'attendance_status' => 'LATE', 'workflow_status' => 'VALIDATED', 'entered_by' => $user->id, 'entered_at' => now(), 'finalized_by' => $user->id, 'finalized_at' => now(), 'updated_by' => $user->id, 'updated_at' => now()]);

        $result = app(AcademicAiToolRegistry::class)->execute('get_student_attendance_summary', new AcademicAiToolContext($user), [
            'student_id' => (string) $student->id, 'period_start' => '2026-07-01', 'period_end' => '2026-07-31',
        ])->toArray();

        $this->assertSame('OK', $result['status']);
        $this->assertSame(1, $result['metrics']['counts']['PRESENT']);
        $this->assertSame(1, $result['metrics']['late_count']);
        $this->assertSame('LEGACY', $result['evidence']['semantic_regime']);
    }

    public function test_detail_status_is_allow_listed_and_bounded(): void
    {
        $this->expectException(ValidationException::class);
        app(AcademicAiToolRegistry::class)->get('get_student_attendance_detail')->inputSchema();
        GetStudentAttendanceDetailRequest::fromArray([
            'student_id' => (string) User::factory()->make()->id,
            'period_start' => '2026-07-01', 'period_end' => '2026-07-31', 'status' => 'DROP TABLE', 'limit' => 1000,
        ]);
    }

    public function test_class_roster_is_factual_and_does_not_rank_students(): void
    {
        $this->assertArrayNotHasKey('rank', app(AcademicAiToolRegistry::class)->get('get_class_attendance_roster')->inputSchema());
        $this->assertStringNotContainsString('rank', strtolower(app(AcademicAiToolRegistry::class)->get('get_class_attendance_roster')->description()));
    }

    public function test_student_detail_excludes_free_text_reason_and_notes_from_ai_context(): void
    {
        [$user, $student] = $this->fixture();
        $class = AcademicClass::query()->firstOrFail();
        $session = ClassSession::create([
            'session_code' => 'AI-R1C-SESSION', 'teaching_assignment_id' => TeachingAssignment::query()->first()->id,
            'class_id' => $class->id, 'subject_id' => Subject::query()->first()->id,
            'planned_start_at' => '2026-07-15 08:00:00', 'planned_end_at' => '2026-07-15 09:00:00',
            'session_source' => 'SCHEDULED', 'participant_scope' => 'FULL_CLASS', 'session_status' => 'COMPLETED',
        ]);
        $participant = SessionStudentParticipant::create(['class_session_id' => $session->id, 'student_id' => $student->id, 'participant_basis' => 'CLASS_ENROLLMENT']);
        StudentAttendance::create([
            'session_student_participant_id' => $participant->id,
            'attendance_status' => 'PRESENT',
            'reason_code' => 'free-form reason',
            'notes' => 'private operational note',
            'workflow_status' => 'VALIDATED',
            'entered_by' => $user->id,
            'entered_at' => now(),
            'finalized_by' => $user->id,
            'finalized_at' => now(),
            'updated_by' => $user->id,
            'updated_at' => now(),
        ]);

        $result = app(AcademicAiToolRegistry::class)->execute('get_student_attendance_detail', new AcademicAiToolContext($user), [
            'student_id' => (string) $student->id, 'period_start' => '2026-07-01', 'period_end' => '2026-07-31', 'page' => 1, 'limit' => 20,
        ])->toArray();

        $this->assertSame('OK', $result['status']);
        $this->assertArrayNotHasKey('reason', $result['records'][0]);
        $this->assertArrayNotHasKey('notes', $result['records'][0]);
        $this->assertArrayNotHasKey('raw_attendance_status', $result['records'][0]);
        $this->assertSame('free-form reason', StudentAttendance::query()->firstOrFail()->reason_code);
        $this->assertSame('private operational note', StudentAttendance::query()->firstOrFail()->notes);
    }

    private function fixture(): array
    {
        $unit = OrganizationalUnit::create(['unit_code' => 'AI-A1-UNIT', 'unit_name' => 'AI-A1 Unit', 'unit_type' => 'SCHOOL']);
        $grade = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => 'AI-A1', 'display_name' => 'AI-A1 Grade', 'sequence_no' => 1]);
        $year = AcademicYear::create(['year_code' => '2026-AI-A1', 'display_name' => '2026/2027', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $semester = Semester::create(['academic_year_id' => $year->id, 'semester_code' => 'AI-A1', 'display_name' => 'AI-A1 Semester', 'sequence_no' => 1, 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-31']);
        $class = AcademicClass::create(['class_code' => 'AI-A1-CLASS', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id, 'section_code' => 'A', 'display_name' => 'AI-A1 Class']);
        $subject = Subject::create(['subject_code' => 'AI-A1-SUBJECT', 'subject_name' => 'AI-A1 Subject']);
        $staff = Staff::create(['staff_code' => 'AI-A1-STAFF', 'full_name' => 'AI-A1 Staff']);
        TeachingAssignment::create(['assignment_code' => 'AI-A1-ASSIGNMENT', 'semester_id' => $semester->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $staff->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);
        $student = Student::create(['full_name' => 'AI-A1 Student']);
        $user = User::factory()->create();
        $role = Role::create(['code' => 'WAKA_AKADEMIK', 'name' => 'Waka Akademik']);
        $permission = Permission::create(['code' => 'academic.domain.manage', 'name' => 'Manage Academic domain']);
        $role->permissions()->attach($permission);
        UserRoleAssignment::create(['user_id' => $user->id, 'role_id' => $role->id, 'effective_from' => '2026-07-01']);

        return [$user, $student];
    }
}
