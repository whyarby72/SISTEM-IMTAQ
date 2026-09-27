<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Models\AcademicTranscript;
use App\Domains\Academic\Models\AcademicTranscriptLine;
use App\Domains\Academic\Models\AcademicTranscriptVersion;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\SemesterSubjectGrade;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Services\AcademicTranscriptPublicationService;
use App\Models\User;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\Staff;
use App\Shared\Core\Models\Student;
use App\Shared\Platform\Audit\Models\AuditLog;
use App\Shared\Platform\Authorization\Models\Role;
use App\Shared\Platform\Authorization\Models\UserRoleAssignment;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicTranscriptPublicationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_wali_reviews_and_waka_publishes_with_signatory_snapshots(): void
    {
        [$version, $wali, $waka, $head, $waliStaff] = $this->fixture();
        $published = app(AcademicTranscriptPublicationService::class)->review($version, $wali);
        $published = app(AcademicTranscriptPublicationService::class)->approveAndPublish($published, $waka, $head, $waliStaff);

        $this->assertSame('PUBLISHED', $published->status);
        $this->assertCount(2, $published->signatories);
        $this->assertSame('Kepala Sekolah', $published->signatories->firstWhere('signatory_role', 'KEPALA_SEKOLAH')->title_snapshot);
        $this->assertSame(1, AuditLog::where('action', 'ACADEMIC_TRANSCRIPT_REVIEWED')->count());
        $this->assertSame(1, AuditLog::where('action', 'ACADEMIC_TRANSCRIPT_APPROVED_AND_PUBLISHED')->count());
    }

    public function test_super_admin_can_create_audited_revision_without_mutating_published_version(): void
    {
        [$version, $wali, $waka, $head, $waliStaff, $line] = $this->fixture();
        $service = app(AcademicTranscriptPublicationService::class);
        $published = $service->approveAndPublish($service->review($version, $wali), $waka, $head, $waliStaff);
        $admin = User::factory()->create();
        $role = Role::create(['code' => 'SUPER_ADMIN', 'name' => 'Super Admin']);
        UserRoleAssignment::create(['user_id' => $admin->id, 'role_id' => $role->id]);

        $revision = $service->createSuperAdminRevision($published, $admin, [(string) $line->id => ['score' => 99]]);

        $this->assertSame('DRAFT', $revision->status);
        $this->assertSame('99.00', (string) $revision->lines->first()->score);
        $this->assertSame('PUBLISHED', $published->fresh()->status);
        $this->assertSame('85.00', (string) $published->lines()->first()->score);
        $this->assertSame(1, AuditLog::where('action', 'ACADEMIC_TRANSCRIPT_SUPER_ADMIN_REVISION_CREATED')->count());
    }

    public function test_non_wali_cannot_review(): void
    {
        [$version, , $waka, $head, $waliStaff] = $this->fixture();
        $this->expectException(AuthorizationException::class);
        app(AcademicTranscriptPublicationService::class)->review($version, $waka);
    }

    private function fixture(): array
    {
        $year = AcademicYear::create(['year_code' => '2026-TRN', 'display_name' => '2026/2027', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $semester = Semester::create(['academic_year_id' => $year->id, 'semester_code' => 'ODD', 'display_name' => 'Ganjil', 'sequence_no' => 1, 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-31']);
        $subject = Subject::create(['subject_code' => 'TRN-SUBJ', 'subject_name' => 'Transcript Subject']);
        $student = Student::create(['student_code' => 'TRN-STU', 'full_name' => 'Transcript Student']);
        $waliStaff = Staff::create(['staff_code' => 'TRN-WALI', 'full_name' => 'Wali Kelas']);
        $head = Staff::create(['staff_code' => 'TRN-HEAD', 'full_name' => 'Kepala Sekolah']);
        $wali = User::factory()->create();
        $waka = User::factory()->create();
        foreach ([[$wali, 'WALI_KELAS'], [$waka, 'WAKA_AKADEMIK']] as [$user, $code]) {
            $role = Role::create(['code' => $code, 'name' => $code]);
            UserRoleAssignment::create(['user_id' => $user->id, 'role_id' => $role->id]);
        }
        $transcript = AcademicTranscript::create(['student_id' => $student->id]);
        $version = AcademicTranscriptVersion::create(['academic_transcript_id' => $transcript->id, 'version_no' => 1, 'status' => 'DRAFT', 'source_cutoff_at' => now(), 'student_name_snapshot' => $student->full_name, 'created_by' => $wali->id]);
        $grade = SemesterSubjectGrade::create(['student_id' => $student->id, 'semester_id' => $semester->id, 'subject_id' => $subject->id, 'score' => 85, 'workflow_status' => 'LOCKED', 'entered_by' => $wali->id, 'entered_at' => now(), 'finalized_by' => $wali->id, 'finalized_at' => now(), 'updated_by' => $wali->id]);
        $line = AcademicTranscriptLine::create(['academic_transcript_version_id' => $version->id, 'semester_id' => $semester->id, 'subject_id' => $subject->id, 'semester_name_snapshot' => $semester->display_name, 'subject_name_snapshot' => $subject->subject_name, 'semester_subject_grade_id' => $grade->id, 'grade_version_no' => 1, 'score' => 85]);

        return [$version, $wali, $waka, $head, $waliStaff, $line];
    }
}
