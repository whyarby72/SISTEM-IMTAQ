<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\ClassHomeroomAssignment;
use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\StudentClassEnrollment;
use Database\Seeders\AcademicPilotSampleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicPilotSampleSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_pilot_sample_seeder_is_idempotent_and_creates_required_scope(): void
    {
        $this->seed(AcademicPilotSampleSeeder::class);
        $counts = [
            'classes' => AcademicClass::whereIn('class_code', ['3A', '3B'])->count(),
            'homerooms' => ClassHomeroomAssignment::where('reason', 'SAMPLE-PILOT')->count(),
            'enrollments' => StudentClassEnrollment::where('source_reference', 'SAMPLE-PILOT')->count(),
            'sessions' => ClassSession::where('session_code', 'like', 'SESSION-%')->count(),
        ];

        $this->seed(AcademicPilotSampleSeeder::class);

        $this->assertSame(['classes' => 2, 'homerooms' => 2, 'enrollments' => 10, 'sessions' => 4], $counts);
        $this->assertSame(2, AcademicClass::whereIn('class_code', ['3A', '3B'])->count());
        $this->assertSame(2, ClassHomeroomAssignment::where('reason', 'SAMPLE-PILOT')->count());
        $this->assertSame(10, StudentClassEnrollment::where('source_reference', 'SAMPLE-PILOT')->count());
        $this->assertSame(4, ClassSession::where('session_code', 'like', 'SESSION-%')->count());
    }
}
