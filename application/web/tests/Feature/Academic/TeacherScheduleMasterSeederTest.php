<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Models\Subject;
use App\Shared\Core\Models\Staff;
use Database\Seeders\TeacherScheduleMasterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherScheduleMasterSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_schedule_masters_are_seeded_idempotently_without_sport_or_pilot_overwrite(): void
    {
        Staff::create(['staff_code' => 'PILOT-GURU-3A', 'full_name' => 'Guru Sample 3A', 'record_status' => 'ACTIVE']);

        $this->seed(TeacherScheduleMasterSeeder::class);
        $this->seed(TeacherScheduleMasterSeeder::class);

        $this->assertDatabaseCount('staff', 18);
        $this->assertDatabaseHas('staff', ['staff_code' => 'AKH', 'full_name' => 'Akhen']);
        $this->assertDatabaseHas('staff', ['staff_code' => 'ADT', 'full_name' => 'Aditya']);
        $this->assertDatabaseHas('staff', ['staff_code' => 'PILOT-GURU-3A', 'full_name' => 'Guru Sample 3A']);
        $this->assertSame(15, Subject::query()->count());
        $this->assertDatabaseMissing('subjects', ['subject_code' => 'SUB-SPORT']);
    }
}
