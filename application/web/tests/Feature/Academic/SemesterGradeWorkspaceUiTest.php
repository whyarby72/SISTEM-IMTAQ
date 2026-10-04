<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\ClassHomeroomAssignment;
use App\Domains\Academic\Models\GradeLevel;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\SemesterSubjectGrade;
use App\Domains\Academic\Models\StudentClassEnrollment;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Models\TeachingAssignment;
use App\Models\User;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use App\Shared\Core\Models\Staff;
use App\Shared\Core\Models\Student;
use App\Shared\Platform\Audit\Models\AuditLog;
use App\Shared\Platform\Authorization\Models\Feature;
use App\Shared\Platform\Authorization\Models\Role;
use App\Shared\Platform\Authorization\Models\UserFeatureOverride;
use App\Shared\Platform\Authorization\Models\UserRoleAssignment;
use App\Shared\Platform\Authorization\Models\UserStaffLink;
use Database\Seeders\UserAccessFeatureSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SemesterGradeWorkspaceUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_unknown_grade_feature_fails_closed(): void
    {
        $user = User::factory()->create(['status' => 'ACTIVE']);

        $this->actingAs($user)->get(route('academic.grades.index'))->assertForbidden();
    }

    public function test_assigned_teacher_can_read_exact_scope_and_get_route_has_no_write_methods(): void
    {
        $fixture = $this->fixture('teacher');
        $before = [SemesterSubjectGrade::count(), StudentClassEnrollment::count(), AuditLog::count()];

        $this->actingAs($fixture['user'])->get(route('academic.grades.index', [
            'semester_id' => $fixture['semester']->id,
            'class_id' => $fixture['class']->id,
            'subject_id' => $fixture['subject']->id,
        ]))->assertOk()->assertSee('Nilai Semester')->assertSee('Belum diisi')->assertDontSee('Simpan');

        $this->assertSame($before, [SemesterSubjectGrade::count(), StudentClassEnrollment::count(), AuditLog::count()]);
        $this->assertSame(['GET', 'HEAD'], collect(app('router')->getRoutes()->getByName('academic.grades.index')->methods())->values()->all());
    }

    public function test_teacher_cannot_view_other_subject_or_class(): void
    {
        $fixture = $this->fixture('teacher');
        $otherSubject = Subject::create(['subject_code' => 'OTHER-SUBJECT', 'subject_name' => 'Other Subject']);
        $otherClass = AcademicClass::create(['class_code' => 'OTHER-CLASS', 'academic_year_id' => $fixture['year']->id, 'organizational_unit_id' => $fixture['unit']->id, 'grade_level_id' => $fixture['gradeLevel']->id, 'section_code' => 'B', 'display_name' => 'Other Class']);

        TeachingAssignment::create(['assignment_code' => 'OTHER-TA', 'semester_id' => $fixture['semester']->id, 'class_id' => $otherClass->id, 'subject_id' => $otherSubject->id, 'teacher_staff_id' => $fixture['otherTeacher']->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);

        $this->actingAs($fixture['user'])->get(route('academic.grades.index', ['semester_id' => $fixture['semester']->id, 'class_id' => $fixture['class']->id, 'subject_id' => $otherSubject->id]))->assertForbidden();
        $this->actingAs($fixture['user'])->get(route('academic.grades.index', ['semester_id' => $fixture['semester']->id, 'class_id' => $otherClass->id, 'subject_id' => $otherSubject->id]))->assertForbidden();
    }

    public function test_wali_can_view_own_class_but_not_another_class(): void
    {
        $fixture = $this->fixture('wali');
        $otherClass = AcademicClass::create(['class_code' => 'OTHER-WALI-CLASS', 'academic_year_id' => $fixture['year']->id, 'organizational_unit_id' => $fixture['unit']->id, 'grade_level_id' => $fixture['gradeLevel']->id, 'section_code' => 'B', 'display_name' => 'Other Wali Class']);
        TeachingAssignment::create(['assignment_code' => 'OTHER-WALI-TA', 'semester_id' => $fixture['semester']->id, 'class_id' => $otherClass->id, 'subject_id' => $fixture['subject']->id, 'teacher_staff_id' => $fixture['otherTeacher']->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);

        $this->actingAs($fixture['user'])->get(route('academic.grades.index', ['semester_id' => $fixture['semester']->id, 'class_id' => $fixture['class']->id, 'subject_id' => $fixture['subject']->id]))->assertOk();
        $this->actingAs($fixture['user'])->get(route('academic.grades.index', ['semester_id' => $fixture['semester']->id, 'class_id' => $otherClass->id, 'subject_id' => $fixture['subject']->id]))->assertForbidden();
    }

    public function test_waka_can_view_and_super_admin_only_is_denied(): void
    {
        $fixture = $this->fixture('waka');
        $this->actingAs($fixture['user'])->get(route('academic.grades.index', ['semester_id' => $fixture['semester']->id, 'class_id' => $fixture['class']->id, 'subject_id' => $fixture['subject']->id]))->assertOk();

        $admin = $this->userWithRole('SUPER_ADMIN');
        $this->actingAs($admin)->get(route('academic.grades.index', ['semester_id' => $fixture['semester']->id, 'class_id' => $fixture['class']->id, 'subject_id' => $fixture['subject']->id]))->assertForbidden();
    }

    public function test_disabled_feature_and_enabled_override_cannot_bypass_resource_authority(): void
    {
        $fixture = $this->fixture('unassigned');
        $feature = Feature::where('code', 'academic.grades')->firstOrFail();
        UserFeatureOverride::create(['user_id' => $fixture['user']->id, 'feature_id' => $feature->id, 'state' => 'DISABLED', 'version_no' => 1]);
        $this->actingAs($fixture['user'])->get(route('academic.grades.index'))->assertForbidden();

        $fixture = $this->fixture('unassigned');
        $feature = Feature::where('code', 'academic.grades')->firstOrFail();
        UserFeatureOverride::create(['user_id' => $fixture['user']->id, 'feature_id' => $feature->id, 'state' => 'ENABLED', 'version_no' => 1]);
        $this->actingAs($fixture['user'])->get(route('academic.grades.index', ['semester_id' => $fixture['semester']->id, 'class_id' => $fixture['class']->id, 'subject_id' => $fixture['subject']->id]))->assertForbidden();
    }

    public function test_missing_zero_and_transferred_students_are_read_without_synthetic_grade_rows(): void
    {
        $fixture = $this->fixture('teacher');
        $second = Student::create(['student_code' => 'STU-GRADE-2', 'full_name' => 'Second Student']);
        StudentClassEnrollment::create(['student_id' => $second->id, 'class_id' => $fixture['class']->id, 'effective_from' => '2026-07-01']);
        SemesterSubjectGrade::create(['student_id' => $fixture['student']->id, 'semester_id' => $fixture['semester']->id, 'subject_id' => $fixture['subject']->id, 'score' => 0, 'entered_by' => $fixture['user']->id, 'entered_at' => now(), 'updated_by' => $fixture['user']->id, 'updated_at' => now()]);
        $count = SemesterSubjectGrade::count();

        $this->actingAs($fixture['user'])->get(route('academic.grades.index', ['semester_id' => $fixture['semester']->id, 'class_id' => $fixture['class']->id, 'subject_id' => $fixture['subject']->id]))->assertOk()->assertSee('0.00')->assertSee('Belum diisi')->assertSee('STU-GRADE-2');
        $this->assertSame($count, SemesterSubjectGrade::count());
    }

    public function test_registered_disabled_feature_is_denied(): void
    {
        $fixture = $this->fixture('teacher');
        Feature::where('code', 'academic.grades')->update(['system_enabled' => false]);

        $this->actingAs($fixture['user'])->get(route('academic.grades.index'))->assertForbidden();
    }

    private function fixture(string $role): array
    {
        $this->seed(UserAccessFeatureSeeder::class);
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-GRADE-'.Str::random(5), 'unit_name' => 'Grade Unit', 'unit_type' => 'SCHOOL']);
        $gradeLevel = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '1', 'display_name' => 'Tingkat 1', 'sequence_no' => 1]);
        $year = AcademicYear::create(['year_code' => '2026-GRADE-'.Str::random(4), 'display_name' => '2026/2027', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $semester = Semester::create(['academic_year_id' => $year->id, 'semester_code' => 'ODD-'.Str::random(4), 'display_name' => 'Ganjil', 'sequence_no' => 1, 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-31']);
        $class = AcademicClass::create(['class_code' => 'CLASS-GRADE-'.Str::random(4), 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $gradeLevel->id, 'section_code' => 'A', 'display_name' => 'Kelas Grade']);
        $subject = Subject::create(['subject_code' => 'SUBJ-GRADE-'.Str::random(4), 'subject_name' => 'Grade Subject']);
        $teacher = Staff::create(['staff_code' => 'STAFF-GRADE-'.Str::random(4), 'full_name' => 'Grade Teacher']);
        $otherTeacher = Staff::create(['staff_code' => 'STAFF-OTHER-'.Str::random(4), 'full_name' => 'Other Teacher']);
        $student = Student::create(['student_code' => 'STU-GRADE-1-'.Str::random(4), 'full_name' => 'First Student']);
        $user = User::factory()->create(['status' => 'ACTIVE']);
        if (in_array($role, ['teacher', 'wali'], true)) {
            UserStaffLink::create(['user_id' => $user->id, 'staff_id' => $teacher->id, 'effective_from' => '2026-07-01']);
        }
        StudentClassEnrollment::create(['student_id' => $student->id, 'class_id' => $class->id, 'effective_from' => '2026-07-01']);
        TeachingAssignment::create(['assignment_code' => 'TA-GRADE-'.Str::random(4), 'semester_id' => $semester->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $teacher->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);
        if ($role === 'wali') {
            $this->assignRole($user, 'WALI_KELAS');
            ClassHomeroomAssignment::create(['class_id' => $class->id, 'staff_id' => $teacher->id, 'effective_from' => '2026-07-01']);
        }
        if ($role === 'waka') {
            $this->assignRole($user, 'WAKA_AKADEMIK');
        }

        return compact('unit', 'gradeLevel', 'year', 'semester', 'class', 'subject', 'teacher', 'otherTeacher', 'student', 'user');
    }

    private function assignRole(User $user, string $roleCode): void
    {
        $role = Role::firstOrCreate(['code' => $roleCode], ['name' => $roleCode]);
        UserRoleAssignment::create(['user_id' => $user->id, 'role_id' => $role->id, 'effective_from' => '2026-07-01']);
    }

    private function userWithRole(string $roleCode): User
    {
        $user = User::factory()->create(['status' => 'ACTIVE']);
        $this->assignRole($user, $roleCode);

        return $user;
    }
}
