<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Models\AcademicCalendarEvent;
use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\GradeLevel;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use Tests\TestCase;

class AcademicCalendarEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_calendar_event_has_required_scope_and_policy_fields(): void
    {
        $this->assertTrue(Schema::hasTable('academic_calendar_events'));
        foreach (['academic_year_id', 'event_type', 'start_at', 'end_at', 'regular_session_policy', 'workflow_status'] as $column) {
            $this->assertTrue(Schema::hasColumn('academic_calendar_events', $column));
        }
    }

    public function test_calendar_event_can_be_institution_wide_or_class_scoped(): void
    {
        [$year, $class] = $this->fixtures();
        $institutionEvent = AcademicCalendarEvent::create([
            'academic_year_id' => $year->id,
            'event_type' => 'HOLIDAY',
            'title' => 'Institutional Holiday',
            'start_at' => '2026-08-17 00:00:00',
            'end_at' => '2026-08-18 00:00:00',
            'regular_session_policy' => 'BLOCK',
            'workflow_status' => 'PUBLISHED',
        ]);
        $classEvent = AcademicCalendarEvent::create([
            'academic_year_id' => $year->id,
            'class_id' => $class->id,
            'event_type' => 'EXAM_PERIOD',
            'title' => 'Class Exam',
            'start_at' => '2026-10-01 08:00:00',
            'end_at' => '2026-10-01 10:00:00',
            'regular_session_policy' => 'REVIEW_REQUIRED',
            'workflow_status' => 'DRAFT',
        ]);

        $this->assertNull($institutionEvent->class_id);
        $this->assertSame($class->id, $classEvent->academicClass->id);
        $this->assertSame('BLOCK', $institutionEvent->regular_session_policy);
    }

    public function test_event_requires_a_positive_half_open_time_interval(): void
    {
        [$year] = $this->fixtures();

        $this->expectException(InvalidArgumentException::class);
        AcademicCalendarEvent::create([
            'academic_year_id' => $year->id,
            'event_type' => 'OTHER',
            'title' => 'Invalid Event',
            'start_at' => '2026-08-17 10:00:00',
            'end_at' => '2026-08-17 10:00:00',
            'regular_session_policy' => 'ALLOW',
            'workflow_status' => 'DRAFT',
        ]);
    }

    private function fixtures(): array
    {
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-CALENDAR', 'unit_name' => 'Calendar Unit', 'unit_type' => 'SCHOOL']);
        $grade = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '1', 'display_name' => 'Tingkat 1', 'sequence_no' => 1]);
        $year = AcademicYear::create(['year_code' => '2026-CALENDAR', 'display_name' => '2026/2027', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $class = AcademicClass::create([
            'class_code' => 'CLASS-CALENDAR-A', 'academic_year_id' => $year->id,
            'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id,
            'section_code' => 'A', 'display_name' => 'Kelas 1 A',
        ]);

        return [$year, $class];
    }
}
