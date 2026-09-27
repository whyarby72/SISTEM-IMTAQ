<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\SemesterSubjectGrade;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Services\AcademicHistoryService;
use App\Models\User;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicHistoryServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_only_locked_grades_in_semester_order_with_provenance(): void
    {
        [$student, $oldSemester, $newSemester, $subjectA, $subjectB, $actor] = $this->fixtures();
        $this->grade($student, $oldSemester, $subjectA, 70, $actor);
        $this->grade($student, $newSemester, $subjectB, 90, $actor);
        $this->grade($student, $newSemester, $subjectA, 80, $actor, 'DRAFT');

        $history = app(AcademicHistoryService::class)->forStudent($student);

        $this->assertCount(2, $history);
        $this->assertSame('90.00', $history[0]['score']);
        $this->assertSame('New Semester', $history[0]['semester_name']);
        $this->assertSame('Subject B', $history[0]['subject_name']);
        $this->assertSame('70.00', $history[1]['score']);
        $this->assertNull($history[0]['source_teaching_assignment_id']);
    }

    public function test_history_is_empty_for_student_without_official_grades(): void
    {
        [$student] = $this->fixtures();

        $this->assertCount(0, app(AcademicHistoryService::class)->forStudent($student));
    }

    private function grade(Student $student, Semester $semester, Subject $subject, int $score, User $actor, string $status = 'LOCKED'): void
    {
        SemesterSubjectGrade::create(['student_id' => $student->id, 'semester_id' => $semester->id, 'subject_id' => $subject->id, 'score' => $score, 'workflow_status' => $status, 'entered_by' => $actor->id, 'entered_at' => now(), 'updated_by' => $actor->id, 'updated_at' => now()]);
    }

    private function fixtures(): array
    {
        $student = Student::create(['student_code' => 'STU-HIS', 'full_name' => 'History Student']);
        $actor = User::factory()->create();
        $year = AcademicYear::create(['year_code' => '2026-HIS', 'display_name' => '2026/2027', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $oldSemester = Semester::create(['academic_year_id' => $year->id, 'semester_code' => 'OLD', 'display_name' => 'Old Semester', 'sequence_no' => 1, 'starts_on' => '2026-01-01', 'ends_on' => '2026-06-30']);
        $newSemester = Semester::create(['academic_year_id' => $year->id, 'semester_code' => 'NEW', 'display_name' => 'New Semester', 'sequence_no' => 2, 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-31']);
        $subjectA = Subject::create(['subject_code' => 'SUBJ-HIS-A', 'subject_name' => 'Subject A']);
        $subjectB = Subject::create(['subject_code' => 'SUBJ-HIS-B', 'subject_name' => 'Subject B']);

        return [$student, $oldSemester, $newSemester, $subjectA, $subjectB, $actor];
    }
}
