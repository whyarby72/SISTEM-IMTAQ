<?php

namespace Tests\Feature\Academic\AI;

use App\Domains\Academic\AI\AcademicAiOrchestrator;
use App\Domains\Academic\AI\Contracts\AcademicAiRuntimeResult;
use App\Domains\Academic\AI\Contracts\AcademicAiToolContext;
use App\Models\User;
use App\Shared\Platform\Authorization\Models\Permission;
use App\Shared\Platform\Authorization\Models\Role;
use App\Shared\Platform\Authorization\Models\UserRoleAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Mockery;
use Tests\TestCase;

class AcademicAiHttpTest extends TestCase
{
    use RefreshDatabase;

    public function test_feature_gate_off_does_not_invoke_orchestrator(): void
    {
        config()->set('academic.ai.assistant_enabled', false);
        $orchestrator = Mockery::mock(AcademicAiOrchestrator::class);
        $orchestrator->shouldNotReceive('ask');
        app()->instance(AcademicAiOrchestrator::class, $orchestrator);

        $response = $this->actingAs(User::factory()->create())->postJson('/academic/ai-assistant/query', ['question' => 'Ringkas kehadiran kelas.']);

        $response->assertStatus(503)->assertJsonPath('status', 'UNAVAILABLE')->assertJsonMissingPath('provider');
    }

    public function test_unauthenticated_request_is_rejected_by_web_authentication(): void
    {
        $this->postJson('/academic/ai-assistant/query', ['question' => 'Ringkas data.'])->assertUnauthorized();
    }

    public function test_authenticated_non_waka_is_forbidden(): void
    {
        config()->set('academic.ai.assistant_enabled', true);

        $this->actingAs(User::factory()->create())
            ->postJson('/academic/ai-assistant/query', ['question' => 'Ringkas data.'])
            ->assertForbidden();
    }

    public function test_authorized_waka_uses_server_identity_and_returns_safe_envelope(): void
    {
        config()->set('academic.ai.assistant_enabled', true);
        $waka = $this->waka();
        $orchestrator = Mockery::mock(AcademicAiOrchestrator::class);
        $orchestrator->shouldReceive('ask')
            ->once()
            ->with(Mockery::on(fn (AcademicAiToolContext $context): bool => $context->user->is($waka) && $context->correlationId !== null), 'Ringkas data.')
            ->andReturn(new AcademicAiRuntimeResult('COMPLETED', 'Jawaban aman.', 'corr', 'fake', 'hidden-model', 'provider-secret-id'));
        app()->instance(AcademicAiOrchestrator::class, $orchestrator);

        $response = $this->actingAs($waka)->postJson('/academic/ai-assistant/query', ['question' => 'Ringkas data.']);

        $response->assertOk()->assertJsonStructure(['status', 'request_id', 'answer', 'warnings'])->assertJsonPath('status', 'OK')->assertJsonPath('answer', 'Jawaban aman.')->assertJsonMissing(['provider' => 'fake', 'model' => 'hidden-model', 'provider_request_id' => 'provider-secret-id']);
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }

    public function test_request_rejects_unknown_control_fields_and_invalid_question(): void
    {
        $user = $this->waka();
        config()->set('academic.ai.assistant_enabled', true);

        $this->actingAs($user)->postJson('/academic/ai-assistant/query', ['question' => 'Ringkas data.', 'model' => 'attacker-model', 'tools' => []])->assertUnprocessable();
        $this->actingAs($user)->postJson('/academic/ai-assistant/query', ['question' => '   '])->assertUnprocessable();
        $this->actingAs($user)->postJson('/academic/ai-assistant/query', ['question' => str_repeat('x', 4001)])->assertUnprocessable();
    }

    public function test_provider_failure_is_sanitized_and_no_write_is_exposed(): void
    {
        config()->set('academic.ai.assistant_enabled', true);
        $waka = $this->waka();
        $orchestrator = Mockery::mock(AcademicAiOrchestrator::class);
        $orchestrator->shouldReceive('ask')->once()->andReturn(new AcademicAiRuntimeResult('FAILED', null, 'corr', 'fake', 'hidden-model', 'raw-provider-id', [], [], [['code' => 'PROVIDER_ORCHESTRATION_FAILURE']]));
        app()->instance(AcademicAiOrchestrator::class, $orchestrator);

        $response = $this->actingAs($waka)->postJson('/academic/ai-assistant/query', ['question' => 'Ubah absensi menjadi hadir.']);

        $response->assertStatus(503)->assertJsonPath('status', 'ERROR')->assertJsonPath('answer', 'Asisten AI sedang tidak tersedia. Silakan coba lagi nanti.')->assertJsonMissing(['raw-provider-id', 'hidden-model']);
    }

    public function test_rate_limit_is_applied_to_authenticated_identity(): void
    {
        config()->set('academic.ai.assistant_enabled', false);
        config()->set('academic.ai.rate_limit_per_minute', 1);
        $user = $this->waka();
        RateLimiter::clear($user->getAuthIdentifier());

        $this->actingAs($user)->postJson('/academic/ai-assistant/query', ['question' => 'Pertama.'])->assertStatus(503);
        $this->actingAs($user)->postJson('/academic/ai-assistant/query', ['question' => 'Kedua.'])->assertStatus(429);
    }

    public function test_endpoint_is_post_only_and_does_not_accept_get_question(): void
    {
        $this->get('/academic/ai-assistant/query?question=rahasia')->assertStatus(405);
    }

    public function test_endpoint_keeps_web_auth_and_rate_limit_middleware(): void
    {
        $middleware = app('router')->getRoutes()->getByName('academic.ai-assistant.query')->gatherMiddleware();

        $this->assertContains('web', $middleware);
        $this->assertContains('auth', $middleware);
        $this->assertContains('throttle:academic-ai', $middleware);
    }

    private function waka(): User
    {
        $user = User::factory()->create();
        $role = Role::create(['code' => 'WAKA_AKADEMIK', 'name' => 'Waka Akademik']);
        $permission = Permission::create(['code' => 'academic.domain.manage', 'name' => 'Manage Academic domain']);
        $role->permissions()->attach($permission);
        UserRoleAssignment::create(['user_id' => $user->id, 'role_id' => $role->id, 'effective_from' => '2026-07-01']);

        return $user;
    }
}
