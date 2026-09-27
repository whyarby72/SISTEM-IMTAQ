<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\ClassHomeroomAssignment;
use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\GradeLevel;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Models\TeachingAssignment;
use App\Domains\Academic\Services\WaliKelasContextResolver;
use App\Models\User;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use App\Shared\Core\Models\Staff;
use App\Shared\Platform\Authorization\Models\Role;
use App\Shared\Platform\Authorization\Models\UserRoleAssignment;
use App\Shared\Platform\Authorization\Models\UserStaffLink;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class WaliKelasContextResolverTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_wali_kelas_resolves_to_staff_for_effective_class_scope(): void
    {
        [$session, $homeroom, $user] = $this->fixtures();
        $resolved = app(WaliKelasContextResolver::class)->resolve($user, $session);

        $this->assertTrue(Schema::hasTable('user_staff_links'));
        $this->assertSame($homeroom->id, $resolved->id);
    }

    public function test_missing_identity_or_wali_kelas_scope_is_denied(): void
    {
        [$session, , $user] = $this->fixtures();
        UserStaffLink::where('user_id', $user->id)->delete();

        $this->expectException(AuthorizationException::class);
        app(WaliKelasContextResolver::class)->resolve($user, $session);
    }

    private function fixtures(): array
    {
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-AUTH', 'unit_name' => 'Auth Unit', 'unit_type' => 'SCHOOL']);
        $grade = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '1', 'display_name' => 'Tingkat 1', 'sequence_no' => 1]);
        $year = AcademicYear::create(['year_code' => '2026-AUTH', 'display_name' => '2026/2027', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $semester = Semester::create(['academic_year_id' => $year->id, 'semester_code' => 'ODD', 'display_name' => 'Semester Ganjil', 'sequence_no' => 1, 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-31']);
        $class = AcademicClass::create(['class_code' => 'CLASS-AUTH-A', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id, 'section_code' => 'A', 'display_name' => 'Kelas 1 A']);
        $subject = Subject::create(['subject_code' => 'SUBJ-AUTH', 'subject_name' => 'Auth Subject']);
        $teacher = Staff::create(['staff_code' => 'STAFF-AUTH-001', 'full_name' => 'Teacher']);
        $homeroom = Staff::create(['staff_code' => 'STAFF-AUTH-002', 'full_name' => 'Wali Kelas']);
        $user = User::factory()->create();
        $role = Role::create(['code' => 'WALI_KELAS', 'name' => 'Wali Kelas']);
        UserRoleAssignment::create(['user_id' => $user->id, 'role_id' => $role->id, 'effective_from' => '2026-07-01']);
        UserStaffLink::create(['user_id' => $user->id, 'staff_id' => $homeroom->id, 'effective_from' => '2026-07-01']);
        ClassHomeroomAssignment::create(['class_id' => $class->id, 'staff_id' => $homeroom->id, 'effective_from' => '2026-07-01']);
        $assignment = TeachingAssignment::create(['assignment_code' => 'TA-AUTH-001', 'semester_id' => $semester->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $teacher->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);
        $session = ClassSession::create(['session_code' => 'SESSION-AUTH-001', 'teaching_assignment_id' => $assignment->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'planned_start_at' => '2026-07-06 08:00:00', 'planned_end_at' => '2026-07-06 09:30:00', 'session_source' => 'SCHEDULED', 'participant_scope' => 'FULL_CLASS', 'session_status' => 'PLANNED']);

        return [$session, $homeroom, $user];
    }
}
