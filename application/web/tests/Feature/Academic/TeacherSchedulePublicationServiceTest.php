<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Models\ScheduleRule;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\TeachingAssignment;
use App\Domains\Academic\Services\TeacherScheduleImporter;
use App\Domains\Academic\Services\TeacherSchedulePublicationService;
use App\Shared\Platform\Audit\Models\AuditLog;
use Database\Seeders\OfficialAcademicStructure2026Seeder;
use Database\Seeders\TeacherScheduleMasterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TeacherSchedulePublicationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_schedule_can_be_validated_then_published_with_audit_entries(): void
    {
        $this->seed([TeacherScheduleMasterSeeder::class, OfficialAcademicStructure2026Seeder::class]);
        $this->importOneRule();
        $semester = Semester::where('semester_code', 'S1-2026-2027')->firstOrFail();

        $service = app(TeacherSchedulePublicationService::class);
        $this->assertSame(1, $service->validate($semester));
        $this->assertSame('VALIDATED', ScheduleRule::firstOrFail()->workflow_status);
        $this->assertSame('VALIDATED', TeachingAssignment::firstOrFail()->workflow_status);
        $this->assertSame(2, AuditLog::where('action', 'SCHEDULE_VALIDATED')->count());

        $this->assertSame(1, $service->publish($semester));
        $this->assertSame('PUBLISHED', ScheduleRule::firstOrFail()->workflow_status);
        $this->assertSame('PUBLISHED', TeachingAssignment::firstOrFail()->workflow_status);
        $this->assertSame(2, AuditLog::where('action', 'SCHEDULE_PUBLISHED')->count());
    }

    public function test_publish_rejects_schedule_that_has_not_been_validated(): void
    {
        $this->seed([TeacherScheduleMasterSeeder::class, OfficialAcademicStructure2026Seeder::class]);
        $this->importOneRule();
        $semester = Semester::where('semester_code', 'S1-2026-2027')->firstOrFail();

        $this->expectException(ValidationException::class);
        app(TeacherSchedulePublicationService::class)->publish($semester);
    }

    private function importOneRule(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'schedule-');
        file_put_contents($path, implode("\n", [
            'schedule_rule_key,day_of_week,start_time,end_time,teaching_group_code,subject_id,teacher_code,recurrence_type,week_of_month_set',
            'SCH-PUBLISH,Wednesday,08:00,09:30,TG-B,SUB-ENTREPRENEUR,AKH,WEEK_OF_MONTH,1|3',
        ]));

        app(TeacherScheduleImporter::class)->import($path);
        unlink($path);
    }
}
