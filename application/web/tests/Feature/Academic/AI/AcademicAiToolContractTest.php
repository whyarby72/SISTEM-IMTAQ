<?php

namespace Tests\Feature\Academic\AI;

use App\Domains\Academic\AI\AcademicAiAuthorization;
use App\Domains\Academic\AI\AcademicAiToolRegistry;
use App\Domains\Academic\AI\Contracts\AcademicAiToolContext;
use App\Domains\Academic\AI\Contracts\AcademicAiToolResult;
use App\Domains\Academic\Services\AcademicAuthorizationService;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use InvalidArgumentException;
use Mockery;
use Tests\TestCase;

class AcademicAiToolContractTest extends TestCase
{
    public function test_registry_contains_exactly_the_five_frozen_tools(): void
    {
        $registry = app(AcademicAiToolRegistry::class);

        $this->assertSame([
            'resolve_student',
            'get_student_attendance_summary',
            'get_student_attendance_detail',
            'get_class_attendance_summary',
            'get_class_attendance_roster',
        ], array_keys($registry->all()));
        $this->assertCount(5, $registry->all());
    }

    public function test_unknown_tool_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(AcademicAiToolRegistry::class)->get('execute_sql');
    }

    public function test_authorization_is_centrally_owned_and_denies_unauthorized_user(): void
    {
        $authorization = Mockery::mock(AcademicAuthorizationService::class);
        $authorization->shouldReceive('hasAcademicFullAuthority')->once()->andReturnFalse();
        $user = User::factory()->make();

        $this->expectException(AuthorizationException::class);
        (new AcademicAiAuthorization($authorization))->requireReadAccess($user);
    }

    public function test_context_does_not_accept_a_model_supplied_role(): void
    {
        $context = new AcademicAiToolContext(User::factory()->make(), 'corr-001');

        $this->assertSame('corr-001', $context->correlationId);
        $this->assertFalse(property_exists($context, 'role'));
    }

    public function test_result_envelope_contains_no_sql_or_credentials(): void
    {
        $result = AcademicAiToolResult::ok('resolve_student', new AcademicAiToolContext(User::factory()->make()), [
            'metrics' => ['count' => 0],
            'warnings' => [],
        ])->toArray();

        $encoded = json_encode($result, JSON_THROW_ON_ERROR);
        $this->assertStringNotContainsString('password', strtolower($encoded));
        $this->assertStringNotContainsString('select ', strtolower($encoded));
        $this->assertArrayHasKey('evidence', $result);
        $this->assertArrayHasKey('data_as_of', $result);
    }
}
