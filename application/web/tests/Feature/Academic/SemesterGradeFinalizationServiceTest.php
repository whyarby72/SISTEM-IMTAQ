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
use App\Domains\Academic\Services\SemesterGradeFinalizationService;
use App\Models\User;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use App\Shared\Core\Models\Staff;
use App\Shared\Core\Models\Student;
use App\Shared\Platform\Audit\Models\AuditLog;
use App\Shared\Platform\Authorization\Models\Role;
use App\Shared\Platform\Authorization\Models\UserRoleAssignment;
use App\Shared\Platform\Authorization\Models\UserStaffLink;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class SemesterGradeFinalizationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_wali_checks_then_waka_approves_and_locks_grade(): void
    {
        [$grade, $class, $wali, $waka] = $this->fixtures();
        $service = app(SemesterGradeFinalizationService::class);

        $checked = $service->check($grade, $class, $wali, 1);
        $locked = $service->approveAndLock($checked, $waka, 2);

        $this->assertSame('CHECKED', $checked->workflow_status);
        $this->assertSame(2, $checked->version_no);
        $this->assertSame('LOCKED', $locked->workflow_status);
        $this->assertSame(3, $locked->version_no);
        $this->assertSame($waka->id, $locked->finalized_by);
        $this->assertSame(1, AuditLog::where('action', 'SEMESTER_SUBJECT_GRADE_CHECKED')->count());
        $this->assertSame(1, AuditLog::where('action', 'SEMESTER_SUBJECT_GRADE_LOCKED')->count());
    }

    public function test_wali_cannot_lock_and_incomplete_or_stale_grade_is_rejected(): void
    {
        [$grade, $class, $wali, $waka] = $this->fixtures();
        $service = app(SemesterGradeFinalizationService::class);

        $this->expectException(AuthorizationException::class);
        $service->approveAndLock($grade, $wali, 1);

        $checked = $service->check($grade, $class, $wali, 1);
        $this->expectException(InvalidArgumentException::class);
        $service->approveAndLock($checked, $waka, 1);
    }

    public function test_wali_cannot_check_grade_for_another_class(): void
    {
        [$grade, , $wali, , $unit, $gradeLevel] = $this->fixtures();
        $otherClass = AcademicClass::create(['class_code' => 'CLASS-GFL-B', 'academic_year_id' => $grade->semester->academic_year_id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $gradeLevel->id, 'section_code' => 'B', 'display_name' => 'Invalid Class']);

        $this->expectException(AuthorizationException::class);
        app(SemesterGradeFinalizationService::class)->check($grade, $otherClass, $wali, 1);
    }

    private function fixtures(): array
    {
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-GFL', 'unit_name' => 'Grade Finalization Unit', 'unit_type' => 'SCHOOL']);
        $gradeLevel = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '1', 'display_name' => 'Tingkat 1', 'sequence_no' => 1]);
        $year = AcademicYear::create(['year_code' => '2026-GFL', 'display_name' => '2026/2027', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $semester = Semester::create(['academic_year_id' => $year->id, 'semester_code' => 'ODD', 'display_name' => 'Ganjil', 'sequence_no' => 1, 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-31']);
        $class = AcademicClass::create(['class_code' => 'CLASS-GFL-A', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $gradeLevel->id, 'section_code' => 'A', 'display_name' => 'Kelas Grade Finalization']);
        $subject = Subject::create(['subject_code' => 'SUBJ-GFL', 'subject_name' => 'Grade Finalization']);
        $teacher = Staff::create(['staff_code' => 'STAFF-GFL-TEACHER', 'full_name' => 'Teacher']);
        $waliStaff = Staff::create(['staff_code' => 'STAFF-GFL-WALI', 'full_name' => 'Wali Kelas']);
        $student = Student::create(['student_code' => 'STU-GFL', 'full_name' => 'Grade Student']);
        $wali = User::factory()->create();
        $waka = User::factory()->create();
        $waliRole = Role::create(['code' => 'WALI_KELAS', 'name' => 'Wali Kelas']);
        $wakaRole = Role::create(['code' => 'WAKA_AKADEMIK', 'name' => 'Waka Akademik']);
        UserRoleAssignment::create(['user_id' => $wali->id, 'role_id' => $waliRole->id, 'effective_from' => '2026-07-01']);
        UserRoleAssignment::create(['user_id' => $waka->id, 'role_id' => $wakaRole->id, 'effective_from' => '2026-07-01']);
        UserStaffLink::create(['user_id' => $wali->id, 'staff_id' => $waliStaff->id, 'effective_from' => '2026-07-01']);
        ClassHomeroomAssignment::create(['class_id' => $class->id, 'staff_id' => $waliStaff->id, 'effective_from' => '2026-07-01']);
        StudentClassEnrollment::create(['student_id' => $student->id, 'class_id' => $class->id, 'effective_from' => '2026-07-01']);
        TeachingAssignment::create(['assignment_code' => 'TA-GFL', 'semester_id' => $semester->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $teacher->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);
        $grade = SemesterSubjectGrade::create(['student_id' => $student->id, 'semester_id' => $semester->id, 'subject_id' => $subject->id, 'score' => 90, 'entered_by' => $wali->id, 'entered_at' => now(), 'updated_by' => $wali->id, 'updated_at' => now()]);

        return [$grade, $class, $wali, $waka, $unit, $gradeLevel];
    }
}
