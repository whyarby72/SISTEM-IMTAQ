<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Services\TeacherObligationLockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherObligationLockServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_lock_namespace_is_deterministic_and_teacher_specific(): void
    {
        $service = app(TeacherObligationLockService::class);

        $this->assertSame('academic-teacher-obligation:teacher-a', $service->lockName('teacher-a'));
        $this->assertSame($service->lockName('teacher-a'), $service->lockName('teacher-a'));
        $this->assertNotSame($service->lockName('teacher-a'), $service->lockName('teacher-b'));
    }

    public function test_multiple_teacher_lock_order_is_canonical_and_unique(): void
    {
        $service = app(TeacherObligationLockService::class);

        $this->assertSame(['teacher-a', 'teacher-b', 'teacher-c'], $service->orderedTeacherIds(['teacher-c', 'teacher-a', 'teacher-b', 'teacher-a']));
    }
}
