<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\GradeLevel;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Models\TeachingAssignment;
use App\Domains\Academic\Services\SessionSemanticMetricsService;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use App\Shared\Core\Models\Staff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionSemanticMetricsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_session_metrics_exclude_cancelled_and_rescheduled_source_without_double_counting(): void
    {
        [$class, $assignment] = $this->fixture();
        $this->makeSession($assignment, 0, 'COMPLETED', 'SCHEDULED');
        $this->makeSession($assignment, 1, 'CANCELLED', 'SCHEDULED');
        $this->makeSession($assignment, 2, 'RESCHEDULED', 'SCHEDULED');
        $this->makeSession($assignment, 3, 'COMPLETED', 'RESCHEDULED');
        $this->makeSession($assignment, 4, 'PLANNED', 'EXTRA');

        $metrics = app(SessionSemanticMetricsService::class)->forClassPeriod($class, now()->startOfMonth(), now()->endOfMonth());

        $this->assertSame(3, $metrics['counted_sessions']);
        $this->assertSame(2, $metrics['completed_sessions']);
        $this->assertSame(1, $metrics['open_sessions']);
        $this->assertSame(1, $metrics['cancelled_sessions']);
        $this->assertSame(1, $metrics['rescheduled_source_sessions']);
        $this->assertSame(1, $metrics['extra_sessions']);
        $this->assertSame(66.67, $metrics['completion_rate']);
    }

    private function fixture(): array
    {
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-SESSION-METRIC', 'unit_name' => 'Session Metric Unit', 'unit_type' => 'SCHOOL']);
        $grade = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '1', 'display_name' => 'Tingkat 1', 'sequence_no' => 1]);
        $year = AcademicYear::create(['year_code' => '2026-SESSION-METRIC', 'display_name' => '2026/2027', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $semester = Semester::create(['academic_year_id' => $year->id, 'semester_code' => 'ODD', 'display_name' => 'Ganjil', 'sequence_no' => 1, 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-31']);
        $class = AcademicClass::create(['class_code' => 'CLASS-SESSION-METRIC', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id, 'section_code' => 'A', 'display_name' => 'Kelas Session Metric']);
        $subject = Subject::create(['subject_code' => 'SUBJ-SESSION-METRIC', 'subject_name' => 'Session Metric']);
        $teacher = Staff::create(['staff_code' => 'STAFF-SESSION-METRIC', 'full_name' => 'Session Metric Teacher']);
        $assignment = TeachingAssignment::create(['assignment_code' => 'TA-SESSION-METRIC', 'semester_id' => $semester->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $teacher->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);

        return [$class, $assignment];
    }

    private function makeSession(TeachingAssignment $assignment, int $index, string $status, string $source): ClassSession
    {
        $start = now()->startOfMonth()->addDays($index)->setTime(8, 0);

        return ClassSession::create(['session_code' => "SESSION-SEM-METRIC-{$index}", 'teaching_assignment_id' => $assignment->id, 'class_id' => $assignment->class_id, 'subject_id' => $assignment->subject_id, 'planned_start_at' => $start, 'planned_end_at' => $start->copy()->addHour(), 'session_source' => $source, 'participant_scope' => 'FULL_CLASS', 'session_status' => $status]);
    }
}
