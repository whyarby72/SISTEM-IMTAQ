<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\ClassHomeroomAssignment;
use App\Domains\Academic\Models\GradeLevel;
use App\Domains\Academic\Models\ReportCard;
use App\Domains\Academic\Models\ReportCardNote;
use App\Domains\Academic\Models\ReportCardVersion;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\StudentClassEnrollment;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Models\TeachingAssignment;
use App\Domains\Academic\Services\ReportCardPublicationService;
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
use LogicException;
use Tests\TestCase;

class ReportCardPublicationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_wali_reviews_and_waka_approves_publishes_with_signatory_snapshot(): void
    {
        [$version, $wali, $waka, $head] = $this->fixtures();
        ReportCardNote::create(['report_card_version_id' => $version->id, 'note_text' => 'Catatan.', 'created_by' => $wali->id, 'updated_by' => $wali->id]);
        $service = app(ReportCardPublicationService::class);

        $reviewed = $service->review($version, $wali);
        $published = $service->approveAndPublish($reviewed, $waka, $head);

        $this->assertSame('REVIEWED', $reviewed->status);
        $this->assertSame('PUBLISHED', $published->status);
        $this->assertSame(2, $published->signatories->count());
        $this->assertSame(1, ReportCardNote::where('approved_for_parent_report', true)->count());
        $this->assertSame(1, AuditLog::where('action', 'REPORT_CARD_REVIEWED')->count());
        $this->assertSame(1, AuditLog::where('action', 'REPORT_CARD_APPROVED')->count());
        $this->assertSame(1, AuditLog::where('action', 'REPORT_CARD_PUBLISHED')->count());
    }

    public function test_roles_and_state_transitions_are_enforced(): void
    {
        [$version, $wali, $waka, $head] = $this->fixtures();
        $service = app(ReportCardPublicationService::class);

        $this->expectException(AuthorizationException::class);
        $service->approveAndPublish($version, $wali, $head);

        $reviewed = $service->review($version, $wali);
        $published = $service->approveAndPublish($reviewed, $waka, $head);
        $this->expectException(LogicException::class);
        $published->update(['status' => 'DRAFT']);
    }

    private function fixtures(): array
    {
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-PUB', 'unit_name' => 'Publication Unit', 'unit_type' => 'SCHOOL']);
        $grade = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '1', 'display_name' => 'Tingkat 1', 'sequence_no' => 1]);
        $year = AcademicYear::create(['year_code' => '2026-PUB', 'display_name' => '2026/2027', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $semester = Semester::create(['academic_year_id' => $year->id, 'semester_code' => 'ODD', 'display_name' => 'Ganjil', 'sequence_no' => 1, 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-31']);
        $class = AcademicClass::create(['class_code' => 'CLASS-PUB-A', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id, 'section_code' => 'A', 'display_name' => 'Kelas Publication']);
        $subject = Subject::create(['subject_code' => 'SUBJ-PUB', 'subject_name' => 'Publication']);
        $teacher = Staff::create(['staff_code' => 'STAFF-PUB-T', 'full_name' => 'Teacher']);
        $waliStaff = Staff::create(['staff_code' => 'STAFF-PUB-W', 'full_name' => 'Wali Kelas']);
        $head = Staff::create(['staff_code' => 'STAFF-PUB-H', 'full_name' => 'Kepala Sekolah']);
        $student = Student::create(['student_code' => 'STU-PUB', 'full_name' => 'Report Student']);
        $wali = User::factory()->create();
        $waka = User::factory()->create();
        $waliRole = Role::create(['code' => 'WALI_KELAS', 'name' => 'Wali Kelas']);
        $wakaRole = Role::create(['code' => 'WAKA_AKADEMIK', 'name' => 'Waka Akademik']);
        UserRoleAssignment::create(['user_id' => $wali->id, 'role_id' => $waliRole->id, 'effective_from' => '2026-07-01']);
        UserRoleAssignment::create(['user_id' => $waka->id, 'role_id' => $wakaRole->id, 'effective_from' => '2026-07-01']);
        UserStaffLink::create(['user_id' => $wali->id, 'staff_id' => $waliStaff->id, 'effective_from' => '2026-07-01']);
        ClassHomeroomAssignment::create(['class_id' => $class->id, 'staff_id' => $waliStaff->id, 'effective_from' => '2026-07-01']);
        StudentClassEnrollment::create(['student_id' => $student->id, 'class_id' => $class->id, 'effective_from' => '2026-07-01']);
        TeachingAssignment::create(['assignment_code' => 'TA-PUB', 'semester_id' => $semester->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $teacher->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);
        $report = ReportCard::create(['student_id' => $student->id, 'semester_id' => $semester->id, 'report_type' => 'SEMESTER']);
        $version = ReportCardVersion::create(['report_card_id' => $report->id, 'version_no' => 1, 'status' => 'DRAFT', 'source_cutoff_at' => now(), 'student_name_snapshot' => $student->full_name, 'class_id' => $class->id, 'class_name_snapshot' => $class->display_name, 'semester_name_snapshot' => $semester->display_name, 'created_by' => $wali->id]);

        return [$version, $wali, $waka, $head];
    }
}
