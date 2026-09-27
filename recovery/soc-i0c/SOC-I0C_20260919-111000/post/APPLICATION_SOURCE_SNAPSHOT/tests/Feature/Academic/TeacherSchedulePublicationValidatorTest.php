<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Models\ScheduleRule;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Services\TeacherScheduleImporter;
use App\Domains\Academic\Services\TeacherSchedulePublicationValidator;
use App\Shared\Core\Models\Staff;
use Database\Seeders\OfficialAcademicStructure2026Seeder;
use Database\Seeders\TeacherScheduleMasterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherSchedulePublicationValidatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_publish_validation_blocks_inactive_teacher_and_missing_scope(): void
    {
        $this->seed([TeacherScheduleMasterSeeder::class, OfficialAcademicStructure2026Seeder::class]);
        $path = tempnam(sys_get_temp_dir(), 'schedule-');
        file_put_contents($path, implode("\n", [
            'schedule_rule_key,day_of_week,start_time,end_time,teaching_group_code,subject_id,teacher_code,recurrence_type,week_of_month_set',
            'SCH-VALIDATE,Wednesday,08:00,09:30,TG-B,SUB-ENTREPRENEUR,AKH,WEEK_OF_MONTH,1|3',
        ]));
        app(TeacherScheduleImporter::class)->import($path);

        Staff::where('staff_code', 'AKH')->update(['record_status' => 'INACTIVE']);
        $rule = ScheduleRule::firstOrFail();
        $rule->groups()->delete();

        $errors = app(TeacherSchedulePublicationValidator::class)->validate(Semester::where('semester_code', 'S1-2026-2027')->firstOrFail());

        $this->assertCount(1, $errors);
        $this->assertContains('TEACHER_NOT_FOUND_OR_INACTIVE', $errors->first()['reasons']);
        $this->assertContains('TEACHING_GROUP_SCOPE_REQUIRED', $errors->first()['reasons']);
        unlink($path);
    }
}
