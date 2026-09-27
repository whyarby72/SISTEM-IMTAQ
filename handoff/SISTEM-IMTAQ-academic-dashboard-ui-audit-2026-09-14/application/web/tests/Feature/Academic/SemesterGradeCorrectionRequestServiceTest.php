<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\GradeLevel;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\SemesterSubjectGrade;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Models\TeachingAssignment;
use App\Domains\Academic\Services\SemesterGradeCorrectionRequestService;
use App\Models\User;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use App\Shared\Core\Models\Staff;
use App\Shared\Core\Models\Student;
use App\Shared\Platform\Audit\Models\AuditLog;
use App\Shared\Platform\Audit\Models\CorrectionRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class SemesterGradeCorrectionRequestServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_requests_correction_with_old_new_values_reason_version_and_audit(): void
    {
        [$grade, $actor] = $this->gradeFixture();

        $request = app(SemesterGradeCorrectionRequestService::class)->request($grade, $actor, 1, 'Koreksi input nilai.', ['score' => 91]);

        $this->assertSame('PENDING', $request->status);
        $this->assertSame(1, $request->requested_changes['expected_version']);
        $this->assertSame(91, $request->requested_changes['changes']['score']);
        $audit = AuditLog::where('action', 'SEMESTER_SUBJECT_GRADE_CORRECTION_REQUESTED')->first();
        $this->assertSame(91.0, (float) $audit->new_values['score']);
        $this->assertSame(1, SemesterSubjectGrade::first()->version_no);
        $this->assertSame(1, CorrectionRequest::count());
    }

    public function test_request_rejects_stale_version_invalid_score_and_missing_reason(): void
    {
        [$grade, $actor] = $this->gradeFixture();
        $service = app(SemesterGradeCorrectionRequestService::class);

        $this->expectException(InvalidArgumentException::class);
        $service->request($grade, $actor, 2, 'Stale.', ['score' => 91]);
    }

    public function test_request_rejects_missing_reason(): void
    {
        [$grade, $actor] = $this->gradeFixture();
        $this->expectException(InvalidArgumentException::class);
        app(SemesterGradeCorrectionRequestService::class)->request($grade, $actor, 1, ' ', ['score' => 91]);
    }

    public function test_request_rejects_invalid_score(): void
    {
        [$grade, $actor] = $this->gradeFixture();
        $this->expectException(InvalidArgumentException::class);
        app(SemesterGradeCorrectionRequestService::class)->request($grade, $actor, 1, 'Koreksi.', ['score' => 101]);
    }

    private function gradeFixture(): array
    {
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-GCR', 'unit_name' => 'Grade Correction Unit', 'unit_type' => 'SCHOOL']);
        $gradeLevel = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '1', 'display_name' => 'Tingkat 1', 'sequence_no' => 1]);
        $year = AcademicYear::create(['year_code' => '2026-GCR', 'display_name' => '2026/2027', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $semester = Semester::create(['academic_year_id' => $year->id, 'semester_code' => 'ODD', 'display_name' => 'Ganjil', 'sequence_no' => 1, 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-31']);
        $class = AcademicClass::create(['class_code' => 'CLASS-GCR-A', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $gradeLevel->id, 'section_code' => 'A', 'display_name' => 'Kelas Grade Correction']);
        $subject = Subject::create(['subject_code' => 'SUBJ-GCR', 'subject_name' => 'Grade Correction']);
        $teacher = Staff::create(['staff_code' => 'STAFF-GCR', 'full_name' => 'Correction Teacher']);
        $student = Student::create(['student_code' => 'STU-GCR', 'full_name' => 'Correction Student']);
        $actor = User::factory()->create();
        TeachingAssignment::create(['assignment_code' => 'TA-GCR', 'semester_id' => $semester->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $teacher->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);
        $grade = SemesterSubjectGrade::create(['student_id' => $student->id, 'semester_id' => $semester->id, 'subject_id' => $subject->id, 'score' => 80, 'entered_by' => $actor->id, 'entered_at' => now(), 'updated_by' => $actor->id, 'updated_at' => now()]);

        return [$grade, $actor];
    }
}
