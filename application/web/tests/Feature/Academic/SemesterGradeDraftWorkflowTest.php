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

class SemesterGradeDraftWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_assigned_teacher_creates_atomic_draft_with_server_owned_provenance_and_explicit_zero(): void
    {
        $fixture = $this->fixture();
        $second = $this->enrolledStudent($fixture, 'SECOND');

        $response = $this->actingAs($fixture['user'])->post(route('academic.grades.batch-draft'), $this->payload($fixture, [
            ['student_id' => $fixture['student']->id, 'score' => 0],
            ['student_id' => $second->id, 'score' => ''],
        ]));

        $response->assertRedirect(route('academic.grades.index', $this->selection($fixture)));
        $this->assertDatabaseHas('semester_subject_grades', [
            'student_id' => $fixture['student']->id,
            'score' => 0,
            'workflow_status' => 'DRAFT',
            'grade_source' => 'DIRECT_ENTRY',
            'source_teaching_assignment_id' => $fixture['assignment']->id,
            'responsible_staff_id' => $fixture['teacher']->id,
            'version_no' => 1,
        ]);
        $this->assertDatabaseMissing('semester_subject_grades', ['student_id' => $second->id]);
        $this->assertSame(1, AuditLog::where('action', 'SEMESTER_SUBJECT_GRADE_SAVED')->count());
    }

    public function test_update_clear_and_exact_noop_have_correct_version_and_audit_semantics(): void
    {
        $fixture = $this->fixture();
        $grade = $this->grade($fixture, 75);

        $this->actingAs($fixture['user'])->post(route('academic.grades.batch-draft'), $this->payload($fixture, [[
            'student_id' => $fixture['student']->id, 'score' => 75, 'expected_version' => 1,
        ]]))->assertRedirect();
        $this->assertSame(1, $grade->fresh()->version_no);
        $this->assertSame(0, AuditLog::count());

        $this->actingAs($fixture['user'])->post(route('academic.grades.batch-draft'), $this->payload($fixture, [[
            'student_id' => $fixture['student']->id, 'score' => 80, 'expected_version' => 1,
        ]]))->assertRedirect();
        $this->assertSame(2, $grade->fresh()->version_no);

        $this->actingAs($fixture['user'])->post(route('academic.grades.batch-draft'), $this->payload($fixture, [[
            'student_id' => $fixture['student']->id, 'score' => '', 'expected_version' => 2,
        ]]))->assertRedirect();
        $this->assertNull($grade->fresh()->score);
        $this->assertSame(3, $grade->fresh()->version_no);
        $this->assertSame(2, AuditLog::where('action', 'SEMESTER_SUBJECT_GRADE_SAVED')->count());
    }

    public function test_missing_or_stale_version_rolls_back_the_whole_batch(): void
    {
        $fixture = $this->fixture();
        $second = $this->enrolledStudent($fixture, 'SECOND');
        $grade = $this->grade($fixture, 75);

        $this->actingAs($fixture['user'])->from(route('academic.grades.index'))->post(route('academic.grades.batch-draft'), $this->payload($fixture, [
            ['student_id' => $fixture['student']->id, 'score' => 80],
            ['student_id' => $second->id, 'score' => 90],
        ]))->assertSessionHasErrors('rows');
        $this->assertSame('75.00', $grade->fresh()->score);
        $this->assertDatabaseMissing('semester_subject_grades', ['student_id' => $second->id]);

        $this->actingAs($fixture['user'])->from(route('academic.grades.index'))->post(route('academic.grades.batch-draft'), $this->payload($fixture, [
            ['student_id' => $fixture['student']->id, 'score' => 80, 'expected_version' => 99],
            ['student_id' => $second->id, 'score' => 90],
        ]))->assertSessionHasErrors('rows');
        $this->assertSame('75.00', $grade->fresh()->score);
        $this->assertSame(1, SemesterSubjectGrade::count());
    }

    public function test_batch_rejects_duplicate_outside_enrollment_out_of_range_and_spoofed_fields(): void
    {
        $fixture = $this->fixture();
        $outside = Student::create(['student_code' => 'OUTSIDE-'.Str::random(5), 'full_name' => 'Outside Student']);

        $cases = [
            [['student_id' => $fixture['student']->id, 'score' => 10], ['student_id' => $fixture['student']->id, 'score' => 20]],
            [['student_id' => $outside->id, 'score' => 10]],
            [['student_id' => $fixture['student']->id, 'score' => 101]],
        ];
        foreach ($cases as $rows) {
            $this->actingAs($fixture['user'])->from(route('academic.grades.index'))->post(route('academic.grades.batch-draft'), $this->payload($fixture, $rows))->assertSessionHasErrors();
        }

        $spoofed = $this->payload($fixture, [['student_id' => $fixture['student']->id, 'score' => 10]]);
        $spoofed['workflow_status'] = 'LOCKED';
        $spoofed['responsible_staff_id'] = $fixture['otherTeacher']->id;
        $this->actingAs($fixture['user'])->from(route('academic.grades.index'))->post(route('academic.grades.batch-draft'), $spoofed)->assertSessionHasErrors();
        $this->assertSame(0, SemesterSubjectGrade::count());
    }

    public function test_unassigned_teacher_wali_waka_and_super_admin_cannot_enter_drafts(): void
    {
        $fixture = $this->fixture();
        $actors = [
            User::factory()->create(['status' => 'ACTIVE']),
            $this->roleUser('WAKA_AKADEMIK'),
            $this->roleUser('SUPER_ADMIN'),
        ];
        $wali = User::factory()->create(['status' => 'ACTIVE']);
        UserStaffLink::create(['user_id' => $wali->id, 'staff_id' => $fixture['otherTeacher']->id, 'effective_from' => '2026-07-01']);
        $this->assignRole($wali, 'WALI_KELAS');
        ClassHomeroomAssignment::create(['class_id' => $fixture['class']->id, 'staff_id' => $fixture['otherTeacher']->id, 'effective_from' => '2026-07-01']);
        $actors[] = $wali;

        foreach ($actors as $actor) {
            $this->actingAs($actor)->post(route('academic.grades.batch-draft'), $this->payload($fixture, [[
                'student_id' => $fixture['student']->id, 'score' => 70,
            ]]))->assertForbidden();
        }
        $this->assertSame(0, SemesterSubjectGrade::count());
    }

    public function test_assigned_teacher_cannot_write_another_class_or_subject(): void
    {
        $fixture = $this->fixture();
        $otherClass = AcademicClass::create([
            'class_code' => 'OTHER-G2-'.Str::random(5), 'academic_year_id' => $fixture['year']->id,
            'organizational_unit_id' => $fixture['unit']->id, 'grade_level_id' => $fixture['gradeLevel']->id,
            'section_code' => 'B', 'display_name' => 'Other G2 Class',
        ]);
        $otherSubject = Subject::create(['subject_code' => 'OTHER-SUBJ-'.Str::random(5), 'subject_name' => 'Other G2 Subject']);
        $outside = Student::create(['student_code' => 'OTHER-STU-'.Str::random(5), 'full_name' => 'Other Student']);
        StudentClassEnrollment::create(['student_id' => $outside->id, 'class_id' => $otherClass->id, 'effective_from' => '2026-07-01']);
        foreach ([
            [$otherClass, $fixture['subject'], $outside],
            [$fixture['class'], $otherSubject, $fixture['student']],
        ] as [$class, $subject, $student]) {
            $payload = [
                'semester_id' => $fixture['semester']->id, 'class_id' => $class->id, 'subject_id' => $subject->id,
                'rows' => [['student_id' => $student->id, 'score' => 70]],
            ];
            $this->actingAs($fixture['user'])->post(route('academic.grades.batch-draft'), $payload)->assertForbidden();
        }
        $this->assertSame(0, SemesterSubjectGrade::count());
    }

    public function test_feature_gate_fails_closed_and_enabled_override_cannot_bypass_assignment(): void
    {
        $fixture = $this->fixture();
        Feature::where('code', 'academic.grades')->update(['system_enabled' => false]);
        $this->actingAs($fixture['user'])->post(route('academic.grades.batch-draft'), $this->payload($fixture, [['student_id' => $fixture['student']->id, 'score' => 70]]))->assertForbidden();

        Feature::where('code', 'academic.grades')->update(['system_enabled' => true]);
        $unassigned = User::factory()->create(['status' => 'ACTIVE']);
        $feature = Feature::where('code', 'academic.grades')->firstOrFail();
        UserFeatureOverride::create(['user_id' => $unassigned->id, 'feature_id' => $feature->id, 'state' => 'ENABLED', 'version_no' => 1]);
        $this->actingAs($unassigned)->post(route('academic.grades.batch-draft'), $this->payload($fixture, [['student_id' => $fixture['student']->id, 'score' => 70]]))->assertForbidden();
        $this->assertSame(0, SemesterSubjectGrade::count());
    }

    public function test_checked_and_locked_rows_are_read_only_and_batch_rejects_them_atomically(): void
    {
        foreach (['CHECKED', 'LOCKED'] as $state) {
            $fixture = $this->fixture();
            $grade = $this->grade($fixture, 75, $state);

            $this->actingAs($fixture['user'])->get(route('academic.grades.index', $this->selection($fixture)))
                ->assertOk()->assertSee($state)->assertDontSee('Simpan Draft')->assertDontSee('grade-score-input');
            $this->actingAs($fixture['user'])->from(route('academic.grades.index'))->post(route('academic.grades.batch-draft'), $this->payload($fixture, [[
                'student_id' => $fixture['student']->id, 'score' => 80, 'expected_version' => 1,
            ]]))->assertSessionHasErrors('rows');
            $this->assertSame('75.00', $grade->fresh()->score);
        }
    }

    public function test_ambiguous_teaching_assignment_fails_closed(): void
    {
        $fixture = $this->fixture();
        TeachingAssignment::create([
            'assignment_code' => 'TA-AMB-'.Str::random(5), 'semester_id' => $fixture['semester']->id,
            'class_id' => $fixture['class']->id, 'subject_id' => $fixture['subject']->id,
            'teacher_staff_id' => $fixture['teacher']->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE',
        ]);

        $this->actingAs($fixture['user'])->from(route('academic.grades.index'))->post(route('academic.grades.batch-draft'), $this->payload($fixture, [[
            'student_id' => $fixture['student']->id, 'score' => 70,
        ]]))->assertSessionHasErrors('rows');
        $this->assertSame(0, SemesterSubjectGrade::count());
    }

    public function test_g2_exposes_only_the_authorized_batch_draft_write_route(): void
    {
        $route = app('router')->getRoutes()->getByName('academic.grades.batch-draft');
        $this->assertNotNull($route);
        $this->assertSame(['POST'], array_values($route->methods()));
        $this->assertNull(app('router')->getRoutes()->getByName('academic.grades.check'));
        $this->assertNull(app('router')->getRoutes()->getByName('academic.grades.lock'));
        $this->assertNull(app('router')->getRoutes()->getByName('academic.grades.correct'));
    }

    private function fixture(): array
    {
        $this->seed(UserAccessFeatureSeeder::class);
        $suffix = Str::random(6);
        $unit = OrganizationalUnit::create(['unit_code' => "UNIT-G2-{$suffix}", 'unit_name' => 'G2 Unit', 'unit_type' => 'SCHOOL']);
        $gradeLevel = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '1', 'display_name' => 'Tingkat 1', 'sequence_no' => 1]);
        $year = AcademicYear::create(['year_code' => "2026-G2-{$suffix}", 'display_name' => '2026/2027', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $semester = Semester::create(['academic_year_id' => $year->id, 'semester_code' => "ODD-{$suffix}", 'display_name' => 'Ganjil', 'sequence_no' => 1, 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-31']);
        $class = AcademicClass::create(['class_code' => "CLASS-G2-{$suffix}", 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $gradeLevel->id, 'section_code' => 'A', 'display_name' => 'Kelas G2']);
        $subject = Subject::create(['subject_code' => "SUBJ-G2-{$suffix}", 'subject_name' => 'G2 Subject']);
        $teacher = Staff::create(['staff_code' => "STAFF-G2-{$suffix}", 'full_name' => 'G2 Teacher']);
        $otherTeacher = Staff::create(['staff_code' => "OTHER-G2-{$suffix}", 'full_name' => 'Other Teacher']);
        $student = Student::create(['student_code' => "STU-G2-{$suffix}", 'full_name' => 'G2 Student']);
        $user = User::factory()->create(['status' => 'ACTIVE']);
        UserStaffLink::create(['user_id' => $user->id, 'staff_id' => $teacher->id, 'effective_from' => '2026-07-01']);
        StudentClassEnrollment::create(['student_id' => $student->id, 'class_id' => $class->id, 'effective_from' => '2026-07-01']);
        $assignment = TeachingAssignment::create(['assignment_code' => "TA-G2-{$suffix}", 'semester_id' => $semester->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $teacher->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);

        return compact('unit', 'gradeLevel', 'year', 'semester', 'class', 'subject', 'teacher', 'otherTeacher', 'student', 'user', 'assignment');
    }

    private function enrolledStudent(array $fixture, string $label): Student
    {
        $student = Student::create(['student_code' => "STU-G2-{$label}-".Str::random(5), 'full_name' => "{$label} Student"]);
        StudentClassEnrollment::create(['student_id' => $student->id, 'class_id' => $fixture['class']->id, 'effective_from' => '2026-07-01']);

        return $student;
    }

    private function grade(array $fixture, float $score, string $state = 'DRAFT'): SemesterSubjectGrade
    {
        return SemesterSubjectGrade::create([
            'student_id' => $fixture['student']->id, 'semester_id' => $fixture['semester']->id,
            'subject_id' => $fixture['subject']->id, 'score' => $score, 'workflow_status' => $state,
            'grade_source' => 'DIRECT_ENTRY', 'source_teaching_assignment_id' => $fixture['assignment']->id,
            'responsible_staff_id' => $fixture['teacher']->id, 'entered_by' => $fixture['user']->id,
            'entered_at' => now(), 'updated_by' => $fixture['user']->id, 'updated_at' => now(), 'version_no' => 1,
        ]);
    }

    private function payload(array $fixture, array $rows): array
    {
        return $this->selection($fixture) + ['rows' => $rows];
    }

    private function selection(array $fixture): array
    {
        return ['semester_id' => $fixture['semester']->id, 'class_id' => $fixture['class']->id, 'subject_id' => $fixture['subject']->id];
    }

    private function roleUser(string $roleCode): User
    {
        $user = User::factory()->create(['status' => 'ACTIVE']);
        $this->assignRole($user, $roleCode);

        return $user;
    }

    private function assignRole(User $user, string $roleCode): void
    {
        $role = Role::firstOrCreate(['code' => $roleCode], ['name' => $roleCode]);
        UserRoleAssignment::create(['user_id' => $user->id, 'role_id' => $role->id, 'effective_from' => '2026-07-01']);
    }
}
