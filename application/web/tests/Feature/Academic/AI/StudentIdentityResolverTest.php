<?php

namespace Tests\Feature\Academic\AI;

use App\Domains\Academic\AI\Contracts\ResolveStudentRequest;
use App\Domains\Academic\AI\StudentIdentityResolver;
use App\Shared\Core\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentIdentityResolverTest extends TestCase
{
    use RefreshDatabase;

    public function test_unique_name_resolves_to_canonical_student_id(): void
    {
        $student = Student::create(['full_name' => 'Faizal Unique']);

        $result = app(StudentIdentityResolver::class)->resolve(new ResolveStudentRequest('Faizal Unique'));

        $this->assertSame('RESOLVED', $result['status']);
        $this->assertSame((string) $student->id, $result['student']['student_id']);
    }

    public function test_multiple_name_matches_are_ambiguous_and_never_auto_selected(): void
    {
        Student::create(['full_name' => 'Yusuf Similar']);
        Student::create(['full_name' => 'Yusuf Similar Two']);

        $result = app(StudentIdentityResolver::class)->resolve(new ResolveStudentRequest('Yusuf'));

        $this->assertSame('AMBIGUOUS', $result['status']);
        $this->assertNull($result['student']);
        $this->assertCount(2, $result['candidates']);
        $this->assertArrayHasKey('student_id', $result['candidates'][0]);
        $this->assertArrayHasKey('display_name', $result['candidates'][0]);
        $this->assertArrayNotHasKey('password', $result['candidates'][0]);
    }

    public function test_unknown_name_returns_not_found(): void
    {
        $result = app(StudentIdentityResolver::class)->resolve(new ResolveStudentRequest('No Such Student'));

        $this->assertSame('NOT_FOUND', $result['status']);
        $this->assertSame([], $result['candidates']);
    }
}
