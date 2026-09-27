<?php

namespace Tests\Feature\Shared;

use App\Shared\Platform\Imports\Services\July2026OfficialClassMappingService;
use Database\Seeders\AcademicPilotSampleSeeder;
use Database\Seeders\OfficialAcademicStructure2026Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class July2026OfficialClassMappingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_source_codes_resolve_to_official_classes_when_pilot_collision_exists(): void
    {
        $this->seed(OfficialAcademicStructure2026Seeder::class);
        $this->seed(AcademicPilotSampleSeeder::class);

        $result = app(July2026OfficialClassMappingService::class)->resolve();

        $this->assertTrue($result['valid']);
        $this->assertSame([], $result['errors']);
        $this->assertSame('IMTAQ-2026-3B', $result['classes']['3B']->class_code);
        $this->assertSame('2026/2027', $result['classes']['3B']->academicYear->year_code);
        $this->assertCount(5, $result['classes']);
    }
}
