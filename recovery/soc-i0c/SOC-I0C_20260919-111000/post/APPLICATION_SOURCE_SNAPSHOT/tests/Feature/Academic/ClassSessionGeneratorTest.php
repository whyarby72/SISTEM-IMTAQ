<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Models\AcademicCalendarEvent;
use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\GradeLevel;
use App\Domains\Academic\Models\ScheduleRule;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Models\TeachingAssignment;
use App\Domains\Academic\Services\ClassSessionGenerator;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use App\Shared\Core\Models\Staff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ClassSessionGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_class_sessions_have_required_idempotency_and_lifecycle_fields(): void
    {
        $this->assertTrue(Schema::hasTable('class_sessions'));
        foreach (['schedule_rule_id', 'class_id', 'subject_id', 'planned_start_at', 'planned_end_at', 'session_source', 'session_status'] as $column) {
            $this->assertTrue(Schema::hasColumn('class_sessions', $column));
        }
    }

    public function test_generation_is_idempotent_for_the_same_rule_and_period(): void
    {
        [$rule] = $this->fixtures();
        $generator = app(ClassSessionGenerator::class);
        $first = $generator->generate($rule, '2026-07-01', '2026-07-15');
        $second = $generator->generate($rule, '2026-07-01', '2026-07-15');

        $this->assertCount(2, $first);
        $this->assertSame($first->pluck('id')->all(), $second->pluck('id')->all());
        $first->first()->update(['session_status' => 'COMPLETED']);
        $third = $generator->generate($rule, '2026-07-01', '2026-07-15');
        $this->assertSame('COMPLETED', $third->first()->fresh()->session_status);
        $this->assertDatabaseCount('class_sessions', 2);
    }

    public function test_generation_skips_class_session_when_calendar_event_blocks_the_interval(): void
    {
        [$rule, $year, $class] = $this->fixtures();
        AcademicCalendarEvent::create([
            'academic_year_id' => $year->id,
            'class_id' => $class->id,
            'event_type' => 'NON_TEACHING_DAY',
            'title' => 'Class Holiday',
            'start_at' => '2026-07-13 00:00:00',
            'end_at' => '2026-07-14 00:00:00',
            'regular_session_policy' => 'BLOCK',
            'workflow_status' => 'PUBLISHED',
        ]);

        $sessions = app(ClassSessionGenerator::class)->generate($rule, '2026-07-01', '2026-07-15');

        $this->assertCount(1, $sessions);
        $this->assertSame('2026-07-06', $sessions->first()->planned_start_at->toDateString());
    }

    public function test_review_required_calendar_event_creates_session_for_exception_path(): void
    {
        [$rule, $year, $class] = $this->fixtures();
        AcademicCalendarEvent::create([
            'academic_year_id' => $year->id,
            'class_id' => $class->id,
            'event_type' => 'EXAM_PERIOD',
            'title' => 'Review Required Event',
            'start_at' => '2026-07-06 00:00:00',
            'end_at' => '2026-07-07 00:00:00',
            'regular_session_policy' => 'REVIEW_REQUIRED',
            'workflow_status' => 'PUBLISHED',
        ]);

        $sessions = app(ClassSessionGenerator::class)->generate($rule, '2026-07-01', '2026-07-15');

        $this->assertCount(2, $sessions);
        $this->assertSame('2026-07-06', $sessions->first()->planned_start_at->toDateString());
    }

    private function fixtures(): array
    {
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-SESSION', 'unit_name' => 'Session Unit', 'unit_type' => 'SCHOOL']);
        $grade = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '1', 'display_name' => 'Tingkat 1', 'sequence_no' => 1]);
        $year = AcademicYear::create(['year_code' => '2026-SESSION', 'display_name' => '2026/2027', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $semester = Semester::create(['academic_year_id' => $year->id, 'semester_code' => 'ODD', 'display_name' => 'Semester Ganjil', 'sequence_no' => 1, 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-31']);
        $class = AcademicClass::create(['class_code' => 'CLASS-SESSION-A', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id, 'section_code' => 'A', 'display_name' => 'Kelas 1 A']);
        $subject = Subject::create(['subject_code' => 'SUBJ-SESSION', 'subject_name' => 'Session Subject']);
        $staff = Staff::create(['staff_code' => 'STAFF-SESSION-001', 'full_name' => 'Session Staff']);
        $assignment = TeachingAssignment::create(['assignment_code' => 'TA-SESSION-001', 'semester_id' => $semester->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $staff->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);
        $rule = ScheduleRule::create(['teaching_assignment_id' => $assignment->id, 'weekday' => 1, 'start_time' => '08:00', 'end_time' => '09:30', 'recurrence_type' => 'EVERY_WEEK', 'effective_from' => '2026-07-01', 'effective_until' => '2026-12-31', 'workflow_status' => 'ACTIVE']);

        return [$rule, $year, $class];
    }
}
