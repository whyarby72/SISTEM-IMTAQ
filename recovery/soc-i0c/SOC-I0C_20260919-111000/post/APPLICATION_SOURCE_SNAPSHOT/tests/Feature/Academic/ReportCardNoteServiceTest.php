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
use App\Domains\Academic\Services\ReportCardNoteService;
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

class ReportCardNoteServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_effective_wali_saves_and_updates_optional_non_parent_facing_note(): void
    {
        [$version, $wali] = $this->fixtures('DRAFT', true);
        $service = app(ReportCardNoteService::class);

        $first = $service->save($version, $wali, 'Catatan perkembangan siswa.');
        $second = $service->save($version, $wali, 'Catatan perkembangan siswa diperbarui.');

        $this->assertSame($first->id, $second->id);
        $this->assertSame('Catatan perkembangan siswa diperbarui.', $second->note_text);
        $this->assertFalse($second->approved_for_parent_report);
        $this->assertSame(2, AuditLog::where('action', 'REPORT_CARD_NOTE_SAVED')->count());
        $this->assertSame(1, ReportCardNote::count());
    }

    public function test_non_wali_and_non_draft_version_are_rejected(): void
    {
        [$version, $user] = $this->fixtures('DRAFT', false);
        $service = app(ReportCardNoteService::class);

        $this->expectException(AuthorizationException::class);
        $service->save($version, $user, 'Tidak boleh.');

        [$lockedVersion, $wali] = $this->fixtures('REVIEWED', true);
        $this->expectException(InvalidArgumentException::class);
        $service->save($lockedVersion, $wali, 'Sudah reviewed.');
    }

    private function fixtures(string $versionStatus, bool $withWali): array
    {
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-NOT', 'unit_name' => 'Note Unit', 'unit_type' => 'SCHOOL']);
        $grade = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '1', 'display_name' => 'Tingkat 1', 'sequence_no' => 1]);
        $year = AcademicYear::create(['year_code' => '2026-NOT', 'display_name' => '2026/2027', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $semester = Semester::create(['academic_year_id' => $year->id, 'semester_code' => 'ODD', 'display_name' => 'Ganjil', 'sequence_no' => 1, 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-31']);
        $class = AcademicClass::create(['class_code' => 'CLASS-NOT-A', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id, 'section_code' => 'A', 'display_name' => 'Kelas Note']);
        $student = Student::create(['student_code' => 'STU-NOT', 'full_name' => 'Note Student']);
        StudentClassEnrollment::create(['student_id' => $student->id, 'class_id' => $class->id, 'effective_from' => '2026-07-01']);
        $user = User::factory()->create();
        if ($withWali) {
            $staff = Staff::create(['staff_code' => 'STAFF-NOT', 'full_name' => 'Wali Note']);
            $role = Role::create(['code' => 'WALI_KELAS', 'name' => 'Wali Kelas']);
            UserRoleAssignment::create(['user_id' => $user->id, 'role_id' => $role->id, 'effective_from' => '2026-07-01']);
            UserStaffLink::create(['user_id' => $user->id, 'staff_id' => $staff->id, 'effective_from' => '2026-07-01']);
            ClassHomeroomAssignment::create(['class_id' => $class->id, 'staff_id' => $staff->id, 'effective_from' => '2026-07-01']);
        }
        $report = ReportCard::create(['student_id' => $student->id, 'semester_id' => $semester->id, 'report_type' => 'SEMESTER']);
        $version = ReportCardVersion::create(['report_card_id' => $report->id, 'version_no' => 1, 'status' => $versionStatus, 'source_cutoff_at' => now(), 'student_name_snapshot' => $student->full_name, 'class_id' => $class->id, 'class_name_snapshot' => $class->display_name, 'semester_name_snapshot' => $semester->display_name, 'created_by' => $user->id]);

        return [$version, $user];
    }
}
