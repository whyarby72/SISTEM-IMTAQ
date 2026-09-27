<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Services\TeacherScheduleImportPlanner;
use Database\Seeders\OfficialAcademicStructure2026Seeder;
use Database\Seeders\TeacherScheduleMasterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherScheduleImportPlannerTest extends TestCase
{
    use RefreshDatabase;

    public function test_planner_maps_groups_to_official_classes_and_excludes_sport_without_writing(): void
    {
        $this->seed([TeacherScheduleMasterSeeder::class, OfficialAcademicStructure2026Seeder::class]);

        $result = app(TeacherScheduleImportPlanner::class)->plan([
            ['schedule_rule_key' => 'SCH-1', 'teaching_group_code' => 'TG-1', 'teacher_code' => 'AKH', 'subject_id' => 'SUB-ENTREPRENEUR'],
            ['schedule_rule_key' => 'SCH-B', 'teaching_group_code' => 'TG-B', 'teacher_code' => 'ADT', 'subject_id' => 'SUB-ARABIC'],
            ['schedule_rule_key' => 'SPORT', 'teaching_group_code' => 'TG-SPORT-2-3', 'teacher_code' => 'HAR', 'subject_id' => 'SUB-SPORT'],
        ]);

        $this->assertCount(3, $result['plan']);
        $this->assertSame(['IMTAQ-2026-1', 'IMTAQ-2026-2B', 'IMTAQ-2026-3B'], array_column($result['plan'], 'class_code'));
        $this->assertSame([], $result['blocked']);
        $this->assertDatabaseCount('teaching_assignments', 0);
        $this->assertDatabaseCount('schedule_rules', 0);
        $this->assertDatabaseCount('class_sessions', 0);
    }
}
