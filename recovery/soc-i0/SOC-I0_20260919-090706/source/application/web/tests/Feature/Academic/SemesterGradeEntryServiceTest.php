<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\GradeLevel;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\SemesterSubjectGrade;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Models\TeachingAssignment;
use App\Domains\Academic\Services\SemesterGradeEntryService;
use App\Models\User;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use App\Shared\Core\Models\Staff;
use App\Shared\Core\Models\Student;
use App\Shared\Platform\Audit\Models\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class SemesterGradeEntryServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_saves_one_draft_grade_with_nullable_provenance_and_audit(): void
    {
        [$student, $semester, $subject, $assignment, $actor] = $this->fixtures();

        $grade = app(SemesterGradeEntryService::class)->save($student, $semester, $subject, $actor, [
            'score' => 87.5,
            'source_teaching_assignment_id' => $assignment->id,
        ]);

        $this->assertSame('87.50', $grade->score);
        $this->assertSame('DRAFT', $grade->workflow_status);
        $this->assertSame(1, $grade->version_no);
        $this->assertSame(1, SemesterSubjectGrade::count());
        $this->assertSame(1, AuditLog::where('action', 'SEMESTER_SUBJECT_GRADE_SAVED')->count());
    }

    public function test_imported_grade_preserves_canonical_teaching_assignment_provenance(): void
    {
        [$student, $semester, $subject, $assignment, $actor] = $this->fixtures();

        $grade = app(SemesterGradeEntryService::class)->save($student, $semester, $subject, $actor, [
            'score' => 91,
            'grade_source' => 'IMPORTED',
            'source_teaching_assignment_id' => $assignment->id,
        ]);

        $this->assertSame('IMPORTED', $grade->grade_source);
        $this->assertSame($assignment->id, $grade->source_teaching_assignment_id);
        $this->assertSame($semester->id, $grade->sourceTeachingAssignment->semester_id);
        $this->assertSame($subject->id, $grade->sourceTeachingAssignment->subject_id);
    }

    public function test_updates_existing_grade_with_optimistic_version_and_accepts_explicit_zero(): void
    {
        [$student, $semester, $subject, , $actor] = $this->fixtures();
        $service = app(SemesterGradeEntryService::class);
        $grade = $service->save($student, $semester, $subject, $actor, ['score' => 25]);

        $updated = $service->save($student, $semester, $subject, $actor, ['score' => 0], $grade->version_no);

        $this->assertSame('0.00', $updated->score);
        $this->assertSame(2, $updated->version_no);
        $this->assertSame(2, AuditLog::where('action', 'SEMESTER_SUBJECT_GRADE_SAVED')->count());
    }

    public function test_rejects_score_outside_range(): void
    {
        [$student, $semester, $subject, , $actor] = $this->fixtures();
        $service = app(SemesterGradeEntryService::class);

        $this->expectException(InvalidArgumentException::class);
        $service->save($student, $semester, $subject, $actor, ['score' => 101]);
    }

    public function test_rejects_stale_version(): void
    {
        [$student, $semester, $subject, , $actor] = $this->fixtures();
        $service = app(SemesterGradeEntryService::class);
        $grade = $service->save($student, $semester, $subject, $actor, ['score' => 50]);

        $this->expectException(InvalidArgumentException::class);
        $service->save($student, $semester, $subject, $actor, ['score' => 51], $grade->version_no - 1);
    }

    public function test_rejects_mismatched_teaching_assignment_provenance(): void
    {
        [$student, $semester, $subject, $assignment, $actor] = $this->fixtures();
        $service = app(SemesterGradeEntryService::class);

        $otherSubject = Subject::create(['subject_code' => 'SUBJ-GRD-2', 'subject_name' => 'Other Subject']);
        $this->expectException(InvalidArgumentException::class);
        $service->save($student, $semester, $otherSubject, $actor, ['score' => 60, 'source_teaching_assignment_id' => $assignment->id]);
    }

    public function test_missing_grade_is_not_created_as_zero(): void
    {
        [$student, $semester, $subject] = $this->fixtures();

        $this->assertNull(SemesterSubjectGrade::query()->where('student_id', $student->id)->where('semester_id', $semester->id)->where('subject_id', $subject->id)->first());
    }

    private function fixtures(): array
    {
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-GRD', 'unit_name' => 'Grade Unit', 'unit_type' => 'SCHOOL']);
        $gradeLevel = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '1', 'display_name' => 'Tingkat 1', 'sequence_no' => 1]);
        $year = AcademicYear::create(['year_code' => '2026-GRD', 'display_name' => '2026/2027', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $semester = Semester::create(['academic_year_id' => $year->id, 'semester_code' => 'ODD', 'display_name' => 'Ganjil', 'sequence_no' => 1, 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-31']);
        $class = AcademicClass::create(['class_code' => 'CLASS-GRD-A', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $gradeLevel->id, 'section_code' => 'A', 'display_name' => 'Kelas Grade']);
        $subject = Subject::create(['subject_code' => 'SUBJ-GRD', 'subject_name' => 'Grade Subject']);
        $teacher = Staff::create(['staff_code' => 'STAFF-GRD', 'full_name' => 'Grade Teacher']);
        $student = Student::create(['student_code' => 'STU-GRD', 'full_name' => 'Grade Student']);
        $actor = User::factory()->create();
        $assignment = TeachingAssignment::create(['assignment_code' => 'TA-GRD', 'semester_id' => $semester->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $teacher->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);

        return [$student, $semester, $subject, $assignment, $actor];
    }
}
