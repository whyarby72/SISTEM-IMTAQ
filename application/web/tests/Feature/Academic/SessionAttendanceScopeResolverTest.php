<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\ClassHomeroomAssignment;
use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\ClassSessionGroup;
use App\Domains\Academic\Models\GradeLevel;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\SessionStudentParticipant;
use App\Domains\Academic\Models\StudentClassEnrollment;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Models\TeachingAssignment;
use App\Domains\Academic\Services\SessionAttendanceScopeResolver;
use App\Models\User;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use App\Shared\Core\Models\Staff;
use App\Shared\Core\Models\Student;
use App\Shared\Platform\Authorization\Models\Role;
use App\Shared\Platform\Authorization\Models\UserRoleAssignment;
use App\Shared\Platform\Authorization\Models\UserStaffLink;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionAttendanceScopeResolverTest extends TestCase
{
    use RefreshDatabase;

    public function test_unmapped_joint_participant_fails_closed_for_wali(): void
    {
        [$session, $user] = $this->jointFixture();
        $student = Student::create(['student_code' => 'SCOPE-UNMAPPED', 'full_name' => 'Unmapped']);
        SessionStudentParticipant::create(['class_session_id' => $session->id, 'student_id' => $student->id, 'participant_basis' => 'CLASS_ENROLLMENT']);

        $this->expectException(AuthorizationException::class);
        app(SessionAttendanceScopeResolver::class)->resolve($user, $session->load('scopeGroups'), $session->studentParticipants()->with('student.classEnrollments')->get());
    }

    public function test_ambiguous_joint_participant_fails_closed_for_wali(): void
    {
        [$session, $user, $anchor, $secondary] = $this->jointFixture();
        $student = Student::create(['student_code' => 'SCOPE-AMBIGUOUS', 'full_name' => 'Ambiguous']);
        $participant = new SessionStudentParticipant(['class_session_id' => $session->id, 'student_id' => $student->id]);
        $participant->setRelation('student', $student->setRelation('classEnrollments', collect([
            StudentClassEnrollment::make(['class_id' => $anchor->id, 'effective_from' => '2026-07-01', 'status' => 'ACTIVE']),
            StudentClassEnrollment::make(['class_id' => $secondary->id, 'effective_from' => '2026-07-01', 'status' => 'ACTIVE']),
        ])));

        $this->expectException(AuthorizationException::class);
        app(SessionAttendanceScopeResolver::class)->resolve($user, $session->load('scopeGroups'), collect([$participant]));
    }

    private function jointFixture(): array
    {
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-SCOPE', 'unit_name' => 'Scope Unit', 'unit_type' => 'SCHOOL']);
        $grade = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '1', 'display_name' => 'Tingkat 1', 'sequence_no' => 1]);
        $year = AcademicYear::create(['year_code' => '2026-SCOPE', 'display_name' => '2026/2027', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $semester = Semester::create(['academic_year_id' => $year->id, 'semester_code' => 'ODD', 'display_name' => 'Semester Ganjil', 'sequence_no' => 1, 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-31']);
        $anchor = AcademicClass::create(['class_code' => 'SCOPE-A', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id, 'section_code' => 'A', 'display_name' => 'Scope A']);
        $secondary = AcademicClass::create(['class_code' => 'SCOPE-B', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id, 'section_code' => 'B', 'display_name' => 'Scope B']);
        $subject = Subject::create(['subject_code' => 'SCOPE-SUBJECT', 'subject_name' => 'Scope Subject']);
        $teacher = Staff::create(['staff_code' => 'SCOPE-TEACHER', 'full_name' => 'Scope Teacher']);
        $waliStaff = Staff::create(['staff_code' => 'SCOPE-WALI', 'full_name' => 'Scope Wali']);
        $user = User::factory()->create();
        $role = Role::create(['code' => 'WALI_KELAS', 'name' => 'Wali Kelas']);
        UserRoleAssignment::create(['user_id' => $user->id, 'role_id' => $role->id, 'effective_from' => '2026-07-01']);
        UserStaffLink::create(['user_id' => $user->id, 'staff_id' => $waliStaff->id, 'effective_from' => '2026-07-01']);
        ClassHomeroomAssignment::create(['class_id' => $secondary->id, 'staff_id' => $waliStaff->id, 'effective_from' => '2026-07-01']);
        $assignment = TeachingAssignment::create(['assignment_code' => 'SCOPE-ASSIGNMENT', 'semester_id' => $semester->id, 'class_id' => $anchor->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $teacher->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);
        $session = ClassSession::create(['session_code' => 'SCOPE-SESSION', 'teaching_assignment_id' => $assignment->id, 'class_id' => $anchor->id, 'subject_id' => $subject->id, 'planned_start_at' => '2026-07-06 08:00:00', 'planned_end_at' => '2026-07-06 09:30:00', 'session_source' => 'SCHEDULED', 'participant_scope' => 'FULL_CLASS', 'session_status' => 'PLANNED']);
        ClassSessionGroup::createMany([
            ['class_session_id' => $session->id, 'class_id' => $anchor->id, 'scope_role' => 'JOINT_SCOPE'],
            ['class_session_id' => $session->id, 'class_id' => $secondary->id, 'scope_role' => 'JOINT_SCOPE'],
        ]);

        return [$session, $user, $anchor, $secondary];
    }
}
