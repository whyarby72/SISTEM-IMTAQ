<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Models\AcademicTranscript;
use App\Domains\Academic\Models\AcademicTranscriptVersion;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\SemesterSubjectGrade;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Services\AcademicTranscriptDraftService;
use App\Models\User;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class AcademicTranscriptDraftServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_transcript_draft_from_locked_history_not_report_data(): void
    {
        [$student, $oldSemester, $newSemester, $subject, $actor] = $this->fixtures();
        $this->grade($student, $oldSemester, $subject, 70, $actor);
        $this->grade($student, $newSemester, $subject, 85, $actor);

        $version = app(AcademicTranscriptDraftService::class)->create($student, $actor);

        $this->assertSame('DRAFT', $version->status);
        $this->assertSame('History Student', $version->student_name_snapshot);
        $this->assertCount(2, $version->lines);
        $this->assertSame('85.00', $version->lines[0]->score);
        $this->assertSame('70.00', $version->lines[1]->score);
        $this->assertSame(1, AcademicTranscript::count());
    }

    public function test_each_generation_creates_new_version_and_previous_snapshot_is_immutable(): void
    {
        [$student, $oldSemester, , $subject, $actor] = $this->fixtures();
        $this->grade($student, $oldSemester, $subject, 70, $actor);

        $service = app(AcademicTranscriptDraftService::class);
        $first = $service->create($student, $actor);
        $second = $service->create($student, $actor);

        $this->assertSame(2, $second->version_no);
        $this->assertCount(2, AcademicTranscriptVersion::all());
        $this->expectException(LogicException::class);
        $first->update(['status' => 'PUBLISHED']);
    }

    private function grade(Student $student, Semester $semester, Subject $subject, int $score, User $actor): void
    {
        SemesterSubjectGrade::create(['student_id' => $student->id, 'semester_id' => $semester->id, 'subject_id' => $subject->id, 'score' => $score, 'workflow_status' => 'LOCKED', 'entered_by' => $actor->id, 'entered_at' => now(), 'updated_by' => $actor->id, 'updated_at' => now()]);
    }

    private function fixtures(): array
    {
        $student = Student::create(['student_code' => 'STU-TRN', 'full_name' => 'History Student']);
        $actor = User::factory()->create();
        $year = AcademicYear::create(['year_code' => '2026-TRN', 'display_name' => '2026/2027', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $oldSemester = Semester::create(['academic_year_id' => $year->id, 'semester_code' => 'OLD', 'display_name' => 'Old Semester', 'sequence_no' => 1, 'starts_on' => '2026-01-01', 'ends_on' => '2026-06-30']);
        $newSemester = Semester::create(['academic_year_id' => $year->id, 'semester_code' => 'NEW', 'display_name' => 'New Semester', 'sequence_no' => 2, 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-31']);
        $subject = Subject::create(['subject_code' => 'SUBJ-TRN', 'subject_name' => 'Transcript Subject']);

        return [$student, $oldSemester, $newSemester, $subject, $actor];
    }
}
