<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Models\ScheduleRule;
use App\Domains\Academic\Services\TeacherScheduleImporter;
use Database\Seeders\OfficialAcademicStructure2026Seeder;
use Database\Seeders\TeacherScheduleMasterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherScheduleImporterTest extends TestCase
{
    use RefreshDatabase;

    public function test_importer_is_idempotent_and_excludes_sport(): void
    {
        $this->seed([TeacherScheduleMasterSeeder::class, OfficialAcademicStructure2026Seeder::class]);
        $path = tempnam(sys_get_temp_dir(), 'schedule-');
        file_put_contents($path, implode("\n", [
            'schedule_rule_key,day_of_week,start_time,end_time,teaching_group_code,subject_id,teacher_code,recurrence_type,week_of_month_set',
            'SCH-TEST-1,Wednesday,08:00,09:30,TG-B,SUB-ENTREPRENEUR,AKH,WEEK_OF_MONTH,1|3',
            'SCH-SPORT,Tuesday,07:00,09:30,TG-SPORT-2-3,SUB-SPORT,HAR,WEEKLY,',
        ]));

        $first = app(TeacherScheduleImporter::class)->import($path);
        $second = app(TeacherScheduleImporter::class)->import($path);

        $this->assertSame(['imported_rules' => 1, 'excluded_sport_rules' => 1, 'class_scope_rows' => 2, 'calendar_exceptions' => 3], $first);
        $this->assertSame($first, $second);
        $this->assertSame(1, ScheduleRule::count());
        $this->assertDatabaseCount('teaching_assignments', 1);
        $this->assertDatabaseCount('schedule_rule_groups', 2);
        $this->assertDatabaseCount('class_sessions', 0);
        $this->assertDatabaseCount('student_attendance', 0);
        $this->assertDatabaseCount('academic_calendar_events', 3);
        $this->assertDatabaseCount('audit_logs', 4);

        unlink($path);
    }
}
