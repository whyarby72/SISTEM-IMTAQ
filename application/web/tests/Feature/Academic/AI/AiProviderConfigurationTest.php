<?php

namespace Tests\Feature\Academic\AI;

use App\Domains\Academic\AI\AcademicAiToolRegistry;
use App\Domains\Academic\AI\Contracts\AcademicAiProviderRequest;
use App\Domains\Academic\AI\Contracts\AcademicAiTool;
use App\Domains\Academic\AI\Contracts\AcademicAiToolContext;
use App\Domains\Academic\AI\Contracts\AcademicAiToolResult;
use App\Domains\Academic\AI\Exceptions\AiProviderVerificationException;
use App\Domains\Academic\AI\Models\AiProviderActiveConfiguration;
use App\Domains\Academic\AI\Models\AiProviderConfiguration;
use App\Domains\Academic\AI\Models\AiProviderCredential;
use App\Domains\Academic\AI\Providers\OpenAiResponsesProvider;
use App\Domains\Academic\AI\Services\AiProviderConfigurationService;
use App\Models\User;
use App\Shared\Platform\Authorization\Models\Permission;
use App\Shared\Platform\Authorization\Models\Role;
use App\Shared\Platform\Authorization\Models\UserRoleAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class AiProviderConfigurationTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_super_admin_can_open_provider_settings(): void
    {
        $this->get(route('admin.system.ai-provider.index'))->assertRedirect(route('login'));
        $waka = User::factory()->create();
        $this->actingAs($waka)->get(route('admin.system.ai-provider.index'))->assertForbidden();
    }

    public function test_super_admin_can_discover_provider_settings_from_sidebar_and_empty_state_is_safe(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->get(route('admin.system.ai-provider.index'))
            ->assertOk()
            ->assertSee('Pengaturan Sistem')
            ->assertSee('Pengaturan AI')
            ->assertSee('Belum dikonfigurasi')
            ->assertSee('Public Academic AI')
            ->assertSee('OFF')
            ->assertSee('Tambah API Key')
            ->assertSee('data-config-edit-panel hidden', false)
            ->assertDontSee('<input id="model" name="model"', false)
            ->assertSee('Pengaturan Lanjutan')
            ->assertSee('AbortController')
            ->assertSee('discoveryGeneration');

        $this->assertDatabaseCount('ai_provider_credentials', 0);
        $this->assertDatabaseCount('ai_provider_configurations', 0);
        $this->assertDatabaseCount('ai_provider_active_configurations', 0);
        $this->assertFalse((bool) config('academic.ai.assistant_enabled'));
    }

    public function test_provider_page_reads_public_ai_gate_without_exposing_activation_control(): void
    {
        $admin = $this->superAdmin();
        config(['academic.ai.assistant_enabled' => true]);
        $this->assertTrue((bool) config('academic.ai.assistant_enabled'));

        $this->actingAs($admin)->get(route('admin.system.ai-provider.index'))
            ->assertOk()
            ->assertSee('Public Academic AI')
            ->assertSee('aria-label="Public Academic AI: ON"', false)
            ->assertDontSee('Aktifkan Public Academic AI')
            ->assertDontSee('assistant_enabled');
    }

    public function test_provider_page_groups_primary_workflow_and_hides_advanced_controls_by_default(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->get(route('admin.system.ai-provider.index'))
            ->assertOk()
            ->assertDontSee('Konfigurasi AI')
            ->assertSee('Tambah API Key')
            ->assertSee('Pengaturan Lanjutan')
            ->assertSee('<details class="card advanced" id="advanced-settings"', false)
            ->assertSee('Max output tokens')
            ->assertDontSee('Create DRAFT')
            ->assertDontSee('Activate Configuration');
    }

    public function test_read_mode_uses_canonical_lifecycle_labels_and_keeps_draft_history_advanced(): void
    {
        $admin = $this->superAdmin();
        $credential = AiProviderCredential::create(['provider' => 'openai', 'label' => 'Primary', 'encrypted_secret' => 'key-primary', 'secret_last4' => 'mary', 'status' => 'VERIFIED']);
        $configuration = AiProviderConfiguration::create(['provider' => 'openai', 'credential_id' => $credential->id, 'model' => 'model-a', 'max_output_tokens' => 800, 'status' => 'DRAFT']);

        $this->actingAs($admin)->get(route('admin.system.ai-provider.index'))
            ->assertOk()
            ->assertSee('Perlu diuji')
            ->assertSee('Belum diuji')
            ->assertSee('Riwayat Konfigurasi')
            ->assertSee('ID '.substr((string) $configuration->id, 0, 8))
            ->assertSee('data-config-edit-panel hidden', false)
            ->assertSee('config-edit-template', false);
    }

    public function test_verified_and_active_states_have_distinct_read_mode_statuses(): void
    {
        $admin = $this->superAdmin();
        $credential = AiProviderCredential::create(['provider' => 'openai', 'label' => 'Primary', 'encrypted_secret' => 'key-primary', 'secret_last4' => 'mary', 'status' => 'VERIFIED']);
        $verified = AiProviderConfiguration::create(['provider' => 'openai', 'credential_id' => $credential->id, 'model' => 'model-a', 'max_output_tokens' => 800, 'status' => 'VERIFIED']);

        $this->actingAs($admin)->get(route('admin.system.ai-provider.index'))
            ->assertOk()
            ->assertSee('Siap digunakan')
            ->assertSee('Ubah Konfigurasi')
            ->assertSee('data-config-edit-panel hidden', false);

        $verified->forceFill(['status' => 'ACTIVE'])->save();
        AiProviderActiveConfiguration::create(['provider' => 'openai', 'configuration_id' => $verified->id, 'runtime_enabled' => true, 'version_no' => 1]);

        $this->actingAs($admin)->get(route('admin.system.ai-provider.index'))
            ->assertOk()
            ->assertSee('Sedang digunakan')
            ->assertDontSee('Public Academic AI: ON');
    }

    public function test_invalid_credential_submission_does_not_flash_or_render_secret(): void
    {
        $admin = $this->superAdmin();
        $secret = 'sk-validation-secret-only';

        $response = $this->actingAs($admin)->from(route('admin.system.ai-provider.index'))->post(route('admin.system.ai-provider.credentials.store'), [
            'label' => '',
            'secret' => $secret,
        ]);

        $response->assertRedirect(route('admin.system.ai-provider.index'));
        $this->assertStringNotContainsString($secret, json_encode($response->getSession()->all()));
        $this->assertStringNotContainsString($secret, $response->getContent());
        $this->assertNull(AiProviderCredential::query()->first());
    }

    public function test_provider_verification_failure_is_sanitized_and_uses_credential_entity(): void
    {
        $admin = $this->superAdmin();
        $credential = AiProviderCredential::create(['provider' => 'openai', 'label' => 'Synthetic', 'encrypted_secret' => 'synthetic-only', 'secret_last4' => 'only', 'status' => 'PENDING']);
        Http::fake(['*/v1/models' => Http::response(['error' => ['message' => 'Bearer synthetic-only leaked body']], 401, ['x-request-id' => 'req-safe'])]);

        $response = $this->actingAs($admin)->post(route('admin.system.ai-provider.credentials.verify', $credential));

        $response->assertRedirect()->assertSessionHasErrors('credential');
        $response->assertSessionMissing('secret');
        $this->assertStringNotContainsString('Bearer synthetic-only', json_encode($response->getSession()->all()));
        $audit = DB::table('audit_logs')->where('action', 'AI_PROVIDER_CREDENTIAL_VERIFICATION_FAILED')->latest('id')->first();
        $this->assertSame('AiProviderCredential', $audit->entity_type);
        $this->assertSame((string) $credential->id, (string) $audit->entity_id);
        $this->assertStringNotContainsString('synthetic-only', json_encode($audit));
    }

    public function test_waka_sidebar_does_not_expose_provider_settings(): void
    {
        $waka = User::factory()->create();
        $role = Role::create(['code' => 'WAKA_AKADEMIK', 'name' => 'Waka Akademik']);
        UserRoleAssignment::create(['user_id' => $waka->id, 'role_id' => $role->id, 'effective_from' => '2026-01-01']);

        $this->actingAs($waka)->get(route('academic.dashboard'))
            ->assertOk()
            ->assertDontSee('Pengaturan Sistem')
            ->assertDontSee(route('admin.system.ai-provider.index'));
    }

    public function test_wali_backend_is_denied_and_sensitive_admin_limiter_applies(): void
    {
        $wali = User::factory()->create();
        $role = Role::create(['code' => 'WALI_KELAS', 'name' => 'Wali Kelas']);
        UserRoleAssignment::create(['user_id' => $wali->id, 'role_id' => $role->id, 'effective_from' => '2026-01-01']);

        $this->actingAs($wali)->get(route('admin.system.ai-provider.index'))->assertForbidden();

        $admin = $this->superAdmin();
        RateLimiter::clear((string) $admin->id);
        for ($attempt = 0; $attempt < 12; $attempt++) {
            $this->actingAs($admin)->get(route('admin.system.ai-provider.index'))->assertOk();
        }
        $this->actingAs($admin)->get(route('admin.system.ai-provider.index'))->assertTooManyRequests();
    }

    public function test_waka_akademik_is_denied_by_backend_on_read_and_sensitive_mutations(): void
    {
        $waka = User::factory()->create();
        $role = Role::create(['code' => 'WAKA_AKADEMIK', 'name' => 'Waka Akademik']);
        UserRoleAssignment::create(['user_id' => $waka->id, 'role_id' => $role->id, 'effective_from' => '2026-01-01']);

        $this->actingAs($waka)->get(route('admin.system.ai-provider.index'))->assertForbidden();
        $this->actingAs($waka)->post(route('admin.system.ai-provider.credentials.store'), [
            'label' => 'Synthetic Waka attempt',
            'secret' => 'sk-synthetic-waka-denied',
        ])->assertForbidden();
        $this->actingAs($waka)->post(route('admin.system.ai-provider.runtime.toggle'), ['enabled' => 1])->assertForbidden();
    }

    public function test_runtime_credential_and_configuration_audits_use_canonical_entities(): void
    {
        $admin = $this->superAdmin();
        $credential = app(AiProviderConfigurationService::class)->createCredential($admin, 'Synthetic entity', 'sk-synthetic-entity');
        $credential->forceFill(['status' => 'VERIFIED'])->save();
        Http::fake(['*/v1/models' => Http::response(['data' => [['id' => 'gpt-synthetic']]], 200)]);
        $configuration = app(AiProviderConfigurationService::class)->createConfiguration($admin, $credential->fresh(), 'gpt-synthetic', 800);
        $configuration->forceFill(['status' => 'ACTIVE'])->save();
        $pointer =
            AiProviderActiveConfiguration::create([
                'provider' => 'openai',
                'configuration_id' => $configuration->id,
                'runtime_enabled' => false,
                'version_no' => 1,
            ]);

        $service = app(AiProviderConfigurationService::class);
        $service->setRuntimeEnabled($admin, true);
        $service->setRuntimeEnabled($admin, false);

        $this->assertDatabaseHas('audit_logs', ['action' => 'AI_PROVIDER_CREDENTIAL_CREATED', 'entity_type' => 'AiProviderCredential', 'entity_id' => $credential->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'AI_PROVIDER_RUNTIME_ENABLED', 'entity_type' => 'AiProviderActiveConfiguration', 'entity_id' => $pointer->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'AI_PROVIDER_RUNTIME_DISABLED', 'entity_type' => 'AiProviderActiveConfiguration', 'entity_id' => $pointer->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'AI_PROVIDER_CONFIGURATION_CREATED', 'entity_type' => 'AiProviderConfiguration', 'entity_id' => $configuration->id]);
    }

    public function test_provider_failure_audits_exclude_authorization_headers_bodies_and_secrets(): void
    {
        $admin = $this->superAdmin();
        $secret = 'sk-synthetic-e1-secret';
        $credential = AiProviderCredential::create([
            'provider' => 'openai',
            'label' => 'Synthetic E1',
            'encrypted_secret' => $secret,
            'secret_last4' => 'cret',
            'status' => 'VERIFIED',
        ]);
        $configuration = AiProviderConfiguration::create([
            'provider' => 'openai',
            'credential_id' => $credential->id,
            'model' => 'gpt-synthetic',
            'max_output_tokens' => 800,
            'status' => 'DRAFT',
        ]);

        Http::fake(function (Request $request) {
            return Http::response([
                'error' => [
                    'message' => 'synthetic provider body must never enter audit',
                    'request_headers' => ['Authorization' => 'Bearer sk-synthetic-e1-secret'],
                    'request_body' => ['api_key' => 'sk-synthetic-e1-secret'],
                ],
            ], 503, ['x-request-id' => 'synthetic-e1-request']);
        });

        try {
            app(AiProviderConfigurationService::class)->discoverModels($credential, $admin);
        } catch (\Throwable) {
        }
        try {
            app(AiProviderConfigurationService::class)->verifyConfiguration($admin, $configuration);
        } catch (\Throwable) {
        }

        $audits = DB::table('audit_logs')->whereIn('action', [
            'AI_PROVIDER_CREDENTIAL_VERIFICATION_FAILED',
            'AI_PROVIDER_CONFIGURATION_VERIFICATION_FAILED',
        ])->get();
        $this->assertGreaterThanOrEqual(2, $audits->count());
        foreach ($audits as $audit) {
            $serialized = json_encode($audit);
            $this->assertStringNotContainsString('Authorization', $serialized);
            $this->assertStringNotContainsString('Bearer sk-synthetic-e1-secret', $serialized);
            $this->assertStringNotContainsString($secret, $serialized);
            $this->assertStringNotContainsString('synthetic provider body', $serialized);
            $this->assertStringNotContainsString('request_body', $serialized);
        }
    }

    public function test_verified_credential_can_discover_models_for_configuration_dropdown(): void
    {
        $admin = $this->superAdmin();
        $credential = app(AiProviderConfigurationService::class)->createCredential($admin, 'Primary synthetic', 'sk-synthetic-a5k-secret');
        Http::fake(['*/v1/models' => Http::response(['data' => [['id' => 'gpt-synthetic']]], 200)]);
        app(AiProviderConfigurationService::class)->verifyCredential($admin, $credential);

        $this->actingAs($admin)
            ->postJson(route('admin.system.ai-provider.credentials.models', $credential))
            ->assertOk()
            ->assertJsonPath('models.0', 'gpt-synthetic');
        $this->assertSame('Bearer sk-synthetic-a5k-secret', Http::recorded()[0][0]->header('Authorization')[0]);
    }

    public function test_server_rechecks_selected_credential_model_binding_and_rejects_arbitrary_model(): void
    {
        $admin = $this->superAdmin();
        $credentialA = AiProviderCredential::create(['provider' => 'openai', 'label' => 'A', 'encrypted_secret' => 'key-a', 'secret_last4' => 'y-a', 'status' => 'VERIFIED']);
        $credentialB = AiProviderCredential::create(['provider' => 'openai', 'label' => 'B', 'encrypted_secret' => 'key-b', 'secret_last4' => 'y-b', 'status' => 'VERIFIED']);
        Http::fake(function (Request $request) {
            $authorization = $request->header('Authorization')[0] ?? '';

            return Http::response(['data' => [['id' => str_contains($authorization, 'key-a') ? 'model-a' : 'model-b']]], 200);
        });

        $configuration = app(AiProviderConfigurationService::class)->createConfiguration($admin, $credentialA, 'model-a', 800);
        $this->assertSame('model-a', $configuration->model);
        $this->expectException(ValidationException::class);
        app(AiProviderConfigurationService::class)->createConfiguration($admin, $credentialB, 'model-a', 800);
    }

    public function test_equivalent_draft_is_rejected_without_new_row_and_non_equivalent_is_allowed(): void
    {
        $admin = $this->superAdmin();
        $credential = AiProviderCredential::create(['provider' => 'openai', 'label' => 'Primary', 'encrypted_secret' => 'key-primary', 'secret_last4' => 'mary', 'status' => 'VERIFIED']);
        Http::fake(['*/v1/models' => Http::response(['data' => [['id' => 'model-a']]], 200)]);

        app(AiProviderConfigurationService::class)->createConfiguration($admin, $credential, 'model-a', 800);
        $this->assertSame(1, AiProviderConfiguration::count());

        try {
            app(AiProviderConfigurationService::class)->createConfiguration($admin, $credential, 'model-a', 800);
            $this->fail('Expected equivalent DRAFT rejection.');
        } catch (ValidationException $exception) {
            $this->assertSame('Konfigurasi DRAFT yang setara sudah ada. Gunakan DRAFT tersebut atau ubah model/token.', $exception->errors()['configuration'][0]);
        }
        $this->assertSame(1, AiProviderConfiguration::count());

        $nonEquivalent = app(AiProviderConfigurationService::class)->createConfiguration($admin, $credential, 'model-a', 900);
        $this->assertSame('model-a', $nonEquivalent->model);
        $this->assertSame(2, AiProviderConfiguration::count());
    }

    public function test_discovery_failure_is_normalized_without_provider_body(): void
    {
        $admin = $this->superAdmin();
        $credential = AiProviderCredential::create(['provider' => 'openai', 'label' => 'Primary', 'encrypted_secret' => 'key-primary', 'secret_last4' => 'mary', 'status' => 'VERIFIED']);
        Http::fake(['*/v1/models' => Http::response(['error' => ['message' => 'secret provider body']], 503)]);

        $this->actingAs($admin)
            ->postJson(route('admin.system.ai-provider.credentials.models', $credential))
            ->assertStatus(422)
            ->assertJsonPath('error', 'PROVIDER_UNAVAILABLE')
            ->assertJsonMissing(['message' => 'secret provider body']);
    }

    public function test_existing_drafts_render_safe_distinguishing_metadata(): void
    {
        $admin = $this->superAdmin();
        $credential = AiProviderCredential::create(['provider' => 'openai', 'label' => 'Primary', 'encrypted_secret' => 'key-primary', 'secret_last4' => 'mary', 'status' => 'VERIFIED']);
        $configuration = AiProviderConfiguration::create(['provider' => 'openai', 'credential_id' => $credential->id, 'model' => 'model-a', 'max_output_tokens' => 800, 'status' => 'DRAFT']);

        $this->actingAs($admin)->get(route('admin.system.ai-provider.index'))
            ->assertOk()
            ->assertSee('API Key: Primary')
            ->assertSee('Dibuat')
            ->assertSee('ID '.substr((string) $configuration->id, 0, 8))
            ->assertDontSee('key-primary');
    }

    public function test_credential_is_encrypted_masked_and_not_flashing_plaintext(): void
    {
        $admin = $this->superAdmin();
        $secret = 'sk-synthetic-a5k-secret';

        $response = $this->actingAs($admin)->post(route('admin.system.ai-provider.credentials.store'), ['label' => 'Primary synthetic', 'secret' => $secret]);

        $response->assertSessionHas('status')->assertSessionMissing('errors');
        $this->assertDatabaseHas('ai_provider_credentials', ['status' => 'PENDING', 'secret_last4' => 'cret']);
        $raw = DB::table('ai_provider_credentials')->value('encrypted_secret');
        $this->assertNotSame($secret, $raw);
        $this->assertStringNotContainsString($secret, json_encode($response->getSession()->all()));
        $this->assertArrayNotHasKey('encrypted_secret', AiProviderCredential::first()->toArray());
    }

    public function test_verify_and_activate_are_explicit_and_active_revoke_is_rejected(): void
    {
        $admin = $this->superAdmin();
        $credential = app(AiProviderConfigurationService::class)->createCredential($admin, 'Synthetic', 'sk-synthetic-a5k-secret');
        $this->useSyntheticVerificationRegistry();
        Http::fake(function (Request $request) {
            if (str_ends_with($request->url(), '/v1/models')) {
                return Http::response(['data' => [['id' => 'gpt-synthetic']]], 200);
            }
            if (($request->data()['tool_choice'] ?? null) === 'none') {
                return Http::response(['id' => 'resp_verify', 'output' => [['type' => 'message', 'content' => [['type' => 'output_text', 'text' => 'IMTAQ_SYNTHETIC_VERIFICATION_COMPLETE']]]], 'model' => 'gpt-synthetic'], 200, ['x-request-id' => 'req_verify']);
            }
            $requestInput = $request->data()['input'] ?? '';
            $requestInputText = is_array($requestInput) ? (string) (($requestInput[0]['content'] ?? '')) : (string) $requestInput;
            preg_match('/IMTAQ_SYNTHETIC_PROVIDER_VERIFY_[A-Z0-9]+/', $requestInputText, $matches);

            return Http::response([
                'id' => 'resp_call',
                'output' => [[
                    'type' => 'function_call',
                    'name' => 'resolve_student',
                    'arguments' => json_encode(['query' => $matches[0] ?? '']),
                    'call_id' => 'call_verify',
                ]],
            ], 200, ['x-request-id' => 'req_call']);
        });
        app(AiProviderConfigurationService::class)->verifyCredential($admin, $credential);
        $configuration = app(AiProviderConfigurationService::class)->createConfiguration($admin, $credential->fresh(), 'gpt-synthetic', 800);
        app(AiProviderConfigurationService::class)->verifyConfiguration($admin, $configuration);
        Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/v1/responses') && array_is_list($request->data()['tools'] ?? []));
        app(AiProviderConfigurationService::class)->activate($admin, $configuration->fresh());

        $this->expectException(ValidationException::class);
        app(AiProviderConfigurationService::class)->revoke($admin, $credential->fresh());
    }

    public function test_http_success_without_function_call_keeps_draft_and_audits_failure(): void
    {
        $admin = $this->superAdmin();
        $credential = AiProviderCredential::create(['provider' => 'openai', 'label' => 'Synthetic', 'encrypted_secret' => 'synthetic-only', 'secret_last4' => 'only', 'status' => 'VERIFIED']);
        $configuration = AiProviderConfiguration::create(['provider' => 'openai', 'credential_id' => $credential->id, 'model' => 'gpt-synthetic', 'max_output_tokens' => 800, 'status' => 'DRAFT']);
        $this->useSyntheticVerificationRegistry();
        Http::fake(['*/v1/responses' => Http::response(['id' => 'resp_no_call', 'model' => 'gpt-synthetic', 'output' => [['type' => 'message', 'content' => [['type' => 'output_text', 'text' => 'ok']]]]], 200)]);

        try {
            app(AiProviderConfigurationService::class)->verifyConfiguration($admin, $configuration);
            $this->fail('Expected verification failure.');
        } catch (AiProviderVerificationException $exception) {
            $this->assertSame('FUNCTION_ROUNDTRIP_FAILED', $exception->category);
        }

        $this->assertSame('DRAFT', $configuration->fresh()->status);
        $this->assertDatabaseHas('audit_logs', ['action' => 'AI_PROVIDER_CONFIGURATION_VERIFICATION_FAILED', 'entity_id' => $configuration->id]);
    }

    public function test_synthetic_function_roundtrip_requires_not_found_and_final_completion(): void
    {
        $admin = $this->superAdmin();
        $credential = AiProviderCredential::create(['provider' => 'openai', 'label' => 'Synthetic', 'encrypted_secret' => 'synthetic-only', 'secret_last4' => 'only', 'status' => 'VERIFIED']);
        $configuration = AiProviderConfiguration::create(['provider' => 'openai', 'credential_id' => $credential->id, 'model' => 'gpt-synthetic', 'max_output_tokens' => 800, 'status' => 'DRAFT']);
        $this->useSyntheticVerificationRegistry();
        Http::fake(function (Request $request) {
            if (($request->data()['tool_choice'] ?? null) === 'none') {
                return Http::response(['id' => 'resp_final', 'model' => 'gpt-synthetic', 'output' => [['type' => 'message', 'content' => [['type' => 'output_text', 'text' => 'IMTAQ_SYNTHETIC_VERIFICATION_COMPLETE']]]]], 200, ['x-request-id' => 'req_final']);
            }

            $requestInput = $request->data()['input'] ?? '';
            $requestInputText = is_array($requestInput) ? (string) (($requestInput[0]['content'] ?? '')) : (string) $requestInput;
            preg_match('/IMTAQ_SYNTHETIC_PROVIDER_VERIFY_[A-Z0-9]+/', $requestInputText, $matches);
            $query = $matches[0] ?? '';

            return Http::response([
                'id' => 'resp_call',
                'model' => 'gpt-synthetic',
                'output' => [
                    ['type' => 'reasoning', 'id' => 'rs_synthetic', 'summary' => [], 'encrypted_content' => 'synthetic-reasoning-marker'],
                    [
                        'type' => 'function_call',
                        'name' => 'resolve_student',
                        'arguments' => json_encode(['query' => $query]),
                        'call_id' => 'call_synthetic',
                    ],
                ],
            ], 200, ['x-request-id' => 'req_call']);
        });

        $verified = app(AiProviderConfigurationService::class)->verifyConfiguration($admin, $configuration);

        $this->assertSame('VERIFIED', $verified->status);
        $this->assertSame('SYNTHETIC_FUNCTION_ROUNDTRIP', $verified->verification_metadata['verification']);
        $requests = Http::recorded();
        $this->assertCount(2, $requests);
        $initialInput = $requests[0][0]->data()['input'];
        $this->assertIsArray($initialInput);
        $this->assertTrue(array_is_list($initialInput));
        $this->assertCount(1, $initialInput);
        $this->assertSame(['role', 'content'], array_keys($initialInput[0]));
        $this->assertSame('user', $initialInput[0]['role']);
        $this->assertStringContainsString('IMTAQ_SYNTHETIC_PROVIDER_VERIFY_', $initialInput[0]['content']);
        $this->assertSame(['type' => 'function', 'name' => 'resolve_student'], $requests[0][0]->data()['tool_choice']);
        $this->assertTrue(array_is_list($requests[0][0]->data()['tools']));
        $this->assertCount(5, $requests[0][0]->data()['tools']);
        $this->assertTrue(array_reduce($requests[0][0]->data()['tools'], fn (bool $valid, array $tool): bool => $valid && ($tool['strict'] ?? false) === true, true));
        $this->assertFalse($requests[0][0]->data()['store']);
        $continuationInput = $requests[1][0]->data()['input'];
        $this->assertTrue(array_is_list($continuationInput));
        $this->assertCount(4, $continuationInput);
        $this->assertSame(['role', 'content'], array_keys($continuationInput[0]));
        $this->assertSame(['type', 'id', 'summary', 'encrypted_content'], array_keys($continuationInput[1]));
        $this->assertSame('reasoning', $continuationInput[1]['type']);
        $this->assertSame('synthetic-reasoning-marker', $continuationInput[1]['encrypted_content']);
        $this->assertSame('function_call', $continuationInput[2]['type']);
        $this->assertSame('call_synthetic', $continuationInput[2]['call_id']);
        $this->assertSame(['type', 'call_id', 'output'], array_keys($continuationInput[3]));
        $this->assertSame('function_call_output', $continuationInput[3]['type']);
        $this->assertSame('call_synthetic', $continuationInput[3]['call_id']);
        $this->assertNotEmpty($continuationInput[3]['output']);
        $this->assertFalse($requests[0][0]->data()['store']);
        $this->assertFalse($requests[1][0]->data()['store']);
        $this->assertSame('none', $requests[1][0]->data()['tool_choice']);
        $this->assertArrayNotHasKey('previous_response_id', $requests[1][0]->data());
        $this->assertStringNotContainsString('synthetic-reasoning-marker', json_encode(DB::table('audit_logs')->latest('created_at')->first()));
    }

    public function test_invalid_input_provider_error_uses_precise_failure_category(): void
    {
        $admin = $this->superAdmin();
        $credential = AiProviderCredential::create(['provider' => 'openai', 'label' => 'Synthetic', 'encrypted_secret' => 'synthetic-only', 'secret_last4' => 'only', 'status' => 'VERIFIED']);
        $configuration = AiProviderConfiguration::create(['provider' => 'openai', 'credential_id' => $credential->id, 'model' => 'gpt-synthetic', 'max_output_tokens' => 800, 'status' => 'DRAFT']);
        Http::fake(['*/v1/responses' => Http::response(['error' => [
            'type' => 'invalid_request_error',
            'code' => 'invalid_type',
            'param' => 'input',
            'message' => 'must not be persisted',
        ]], 400)]);

        try {
            app(AiProviderConfigurationService::class)->verifyConfiguration($admin, $configuration);
            $this->fail('Expected invalid input provider failure.');
        } catch (AiProviderVerificationException $exception) {
            $this->assertSame('INVALID_INPUT_TYPE', $exception->category);
        }

        $this->assertSame('DRAFT', $configuration->fresh()->status);
        $this->assertStringNotContainsString('must not be persisted', json_encode(DB::table('audit_logs')->latest('created_at')->first()));
    }

    public function test_d3_final_responses_request_contract_is_exact_and_strict(): void
    {
        $admin = $this->superAdmin();
        $credential = AiProviderCredential::create(['provider' => 'openai', 'label' => 'Synthetic', 'encrypted_secret' => 'synthetic-only', 'secret_last4' => 'only', 'status' => 'VERIFIED']);
        $configuration = AiProviderConfiguration::create(['provider' => 'openai', 'credential_id' => $credential->id, 'model' => 'gpt-synthetic', 'max_output_tokens' => 800, 'status' => 'DRAFT']);
        $this->useSyntheticVerificationRegistry();
        Http::fake(['*/v1/responses' => Http::response(['id' => 'resp_contract', 'output' => []], 200)]);

        try {
            app(AiProviderConfigurationService::class)->verifyConfiguration($admin, $configuration);
        } catch (AiProviderVerificationException) {
        }

        $payload = Http::recorded()[0][0]->data();
        $this->assertSame(['model', 'instructions', 'input', 'tools', 'tool_choice', 'parallel_tool_calls', 'store', 'max_output_tokens'], array_keys($payload));
        $this->assertSame('gpt-synthetic', $payload['model']);
        $this->assertSame(['type' => 'function', 'name' => 'resolve_student'], $payload['tool_choice']);
        $this->assertFalse($payload['parallel_tool_calls']);
        $this->assertFalse($payload['store']);
        $this->assertSame(800, $payload['max_output_tokens']);
        $this->assertCount(5, $payload['tools']);

        foreach ($payload['tools'] as $tool) {
            $this->assertSame(['type', 'name', 'description', 'parameters', 'strict'], array_keys($tool));
            $this->assertSame('function', $tool['type']);
            $this->assertTrue($tool['strict']);
            $this->assertArrayNotHasKey('function', $tool);
            $this->assertStrictSchema($tool['parameters']);
        }
    }

    public function test_d3_provider_error_audit_whitelists_safe_fields_only(): void
    {
        $admin = $this->superAdmin();
        $credential = AiProviderCredential::create(['provider' => 'openai', 'label' => 'Synthetic', 'encrypted_secret' => 'synthetic-only', 'secret_last4' => 'only', 'status' => 'VERIFIED']);
        $configuration = AiProviderConfiguration::create(['provider' => 'openai', 'credential_id' => $credential->id, 'model' => 'gpt-synthetic', 'max_output_tokens' => 800, 'status' => 'DRAFT']);
        Http::fake(['*/v1/responses' => Http::response([
            'error' => [
                'type' => 'invalid_request_error',
                'code' => 'invalid_tool',
                'param' => 'tools[0].parameters',
                'message' => 'provider secret message must never persist',
            ],
            'authorization' => 'Bearer synthetic-secret',
            'raw_body' => ['api_key' => 'synthetic-secret'],
        ], 400, ['x-request-id' => 'req-d3-safe']), ]);

        try {
            app(AiProviderConfigurationService::class)->verifyConfiguration($admin, $configuration);
        } catch (AiProviderVerificationException $exception) {
            $this->assertSame('SCHEMA_REJECTED', $exception->category);
        }

        $audit = DB::table('audit_logs')->where('action', 'AI_PROVIDER_CONFIGURATION_VERIFICATION_FAILED')->latest('created_at')->first();
        $serialized = json_encode($audit);
        $metadata = json_decode((string) $audit->technical_metadata, true);
        $this->assertSame('invalid_request_error', $metadata['provider_error_type']);
        $this->assertSame('invalid_tool', $metadata['provider_error_code']);
        $this->assertSame('tools[0].parameters', $metadata['provider_error_param']);
        $this->assertStringNotContainsString('provider_error_message', $serialized);
        $this->assertStringNotContainsString('provider secret message', $serialized);
        $this->assertStringNotContainsString('authorization', $serialized);
        $this->assertStringNotContainsString('Bearer synthetic-secret', $serialized);
        $this->assertStringNotContainsString('raw_body', $serialized);
        $this->assertStringNotContainsString('synthetic-secret', $serialized);
    }

    public function test_illegal_configuration_states_and_revoked_credentials_cannot_verify(): void
    {
        $admin = $this->superAdmin();
        $credential = AiProviderCredential::create(['provider' => 'openai', 'label' => 'Synthetic', 'encrypted_secret' => 'synthetic-only', 'secret_last4' => 'only', 'status' => 'VERIFIED']);

        foreach (['ACTIVE', 'SUPERSEDED'] as $status) {
            $configuration = AiProviderConfiguration::create(['provider' => 'openai', 'credential_id' => $credential->id, 'model' => 'gpt-synthetic', 'max_output_tokens' => 800, 'status' => $status]);
            try {
                app(AiProviderConfigurationService::class)->verifyConfiguration($admin, $configuration);
                $this->fail('Expected illegal state rejection.');
            } catch (AiProviderVerificationException $exception) {
                $this->assertSame('CONFIGURATION_STATE_INVALID', $exception->category);
            }
        }

        $revoked = AiProviderCredential::create(['provider' => 'openai', 'label' => 'Revoked', 'encrypted_secret' => 'synthetic-only', 'secret_last4' => 'only', 'status' => 'REVOKED']);
        $configuration = AiProviderConfiguration::create(['provider' => 'openai', 'credential_id' => $revoked->id, 'model' => 'gpt-synthetic', 'max_output_tokens' => 800, 'status' => 'DRAFT']);
        try {
            app(AiProviderConfigurationService::class)->verifyConfiguration($admin, $configuration);
            $this->fail('Expected revoked credential rejection.');
        } catch (AiProviderVerificationException $exception) {
            $this->assertSame('CONFIGURATION_STATE_INVALID', $exception->category);
        }
    }

    public function test_provider_failures_are_normalized_without_exposing_body(): void
    {
        $admin = $this->superAdmin();
        $cases = [
            [401, 'AUTHENTICATION_FAILED'],
            [403, 'PERMISSION_DENIED'],
            [404, 'MODEL_UNAVAILABLE'],
            [429, 'RATE_OR_QUOTA_LIMITED'],
            [503, 'PROVIDER_UNAVAILABLE'],
            [400, 'SCHEMA_REJECTED'],
        ];
        $responseIndex = 0;
        Http::fake(function () use ($cases, &$responseIndex) {
            [$status] = $cases[$responseIndex++];

            return Http::response(['error' => ['message' => 'provider secret body must not surface']], $status, ['x-request-id' => 'req-'.$status]);
        });

        foreach ($cases as [$status, $category]) {
            $credential = AiProviderCredential::create(['provider' => 'openai', 'label' => 'Synthetic '.$status, 'encrypted_secret' => 'synthetic-only', 'secret_last4' => 'only', 'status' => 'VERIFIED']);
            $configuration = AiProviderConfiguration::create(['provider' => 'openai', 'credential_id' => $credential->id, 'model' => 'gpt-synthetic', 'max_output_tokens' => 800, 'status' => 'DRAFT']);

            try {
                app(AiProviderConfigurationService::class)->verifyConfiguration($admin, $configuration);
                $this->fail('Expected provider failure.');
            } catch (AiProviderVerificationException $exception) {
                $this->assertSame($category, $exception->category);
                $this->assertSame('DRAFT', $configuration->fresh()->status);
                $this->assertStringNotContainsString('provider secret body', json_encode(session()->all()));
            }
        }
    }

    public function test_unknown_or_incomplete_function_call_cannot_verify(): void
    {
        $admin = $this->superAdmin();
        $scenarios = [
            ['name' => 'execute_sql', 'call_id' => 'call_unknown'],
            ['name' => 'resolve_student', 'call_id' => null],
        ];
        $scenarioIndex = 0;
        Http::fake(function (Request $request) use (&$scenarioIndex, $scenarios) {
            $scenario = $scenarios[$scenarioIndex];

            return Http::response(['id' => 'resp_invalid', 'output' => [[
                'type' => 'function_call',
                'name' => $scenario['name'],
                'arguments' => json_encode(['query' => 'IMTAQ_SYNTHETIC_PROVIDER_VERIFY_TEST']),
                'call_id' => $scenario['call_id'],
            ]]], 200);
        });

        foreach ($scenarios as $index => $scenario) {
            $scenarioIndex = $index;
            $credential = AiProviderCredential::create(['provider' => 'openai', 'label' => 'Synthetic '.$index, 'encrypted_secret' => 'synthetic-only', 'secret_last4' => 'only', 'status' => 'VERIFIED']);
            $configuration = AiProviderConfiguration::create(['provider' => 'openai', 'credential_id' => $credential->id, 'model' => 'gpt-synthetic', 'max_output_tokens' => 800, 'status' => 'DRAFT']);
            $this->useSyntheticVerificationRegistry();

            try {
                app(AiProviderConfigurationService::class)->verifyConfiguration($admin, $configuration);
                $this->fail('Expected function-call rejection.');
            } catch (AiProviderVerificationException $exception) {
                $this->assertSame('FUNCTION_ROUNDTRIP_FAILED', $exception->category);
                $this->assertSame('DRAFT', $configuration->fresh()->status);
            }
        }
    }

    public function test_factual_completion_without_deterministic_synthetic_marker_cannot_verify(): void
    {
        $admin = $this->superAdmin();
        $credential = AiProviderCredential::create(['provider' => 'openai', 'label' => 'Synthetic', 'encrypted_secret' => 'synthetic-only', 'secret_last4' => 'only', 'status' => 'VERIFIED']);
        $configuration = AiProviderConfiguration::create(['provider' => 'openai', 'credential_id' => $credential->id, 'model' => 'gpt-synthetic', 'max_output_tokens' => 800, 'status' => 'DRAFT']);
        $this->useSyntheticVerificationRegistry();
        $round = 0;
        Http::fake(function (Request $request) use (&$round) {
            if ($round++ === 0) {
                $requestInput = $request->data()['input'] ?? '';
                $requestInputText = is_array($requestInput) ? (string) (($requestInput[0]['content'] ?? '')) : (string) $requestInput;
                preg_match('/IMTAQ_SYNTHETIC_PROVIDER_VERIFY_[A-Z0-9]+/', $requestInputText, $matches);

                return Http::response(['id' => 'resp_call', 'output' => [[
                    'type' => 'function_call',
                    'name' => 'resolve_student',
                    'arguments' => json_encode(['query' => $matches[0] ?? '']),
                    'call_id' => 'call_synthetic',
                ]]], 200);
            }

            return Http::response(['id' => 'resp_final', 'output' => [['type' => 'message', 'content' => [['type' => 'output_text', 'text' => 'Jumlah santri adalah 84.']]]]], 200);
        });

        try {
            app(AiProviderConfigurationService::class)->verifyConfiguration($admin, $configuration);
            $this->fail('Expected deterministic completion rejection.');
        } catch (AiProviderVerificationException $exception) {
            $this->assertSame('FUNCTION_ROUNDTRIP_FAILED', $exception->category);
            $this->assertSame('DRAFT', $configuration->fresh()->status);
        }
    }

    private function useSyntheticVerificationRegistry(): void
    {
        $tools = [];
        foreach (['resolve_student', 'get_student_attendance_summary', 'get_student_attendance_detail', 'get_class_attendance_summary', 'get_class_attendance_roster'] as $name) {
            $tool = Mockery::mock(AcademicAiTool::class);
            $tool->shouldReceive('name')->andReturn($name);
            $tool->shouldReceive('description')->andReturn('Synthetic '.$name);
            $tool->shouldReceive('inputSchema')->andReturn(['query' => ['type' => 'string', 'required' => true]]);
            $tools[$name] = $tool;
        }
        $registry = Mockery::mock(AcademicAiToolRegistry::class);
        $registry->shouldReceive('all')->andReturn($tools);
        $registry->shouldReceive('execute')->with('resolve_student', Mockery::type(AcademicAiToolContext::class), Mockery::type('array'))->andReturn(new AcademicAiToolResult('resolve_student', 'NOT_FOUND'));
        app()->instance(AcademicAiToolRegistry::class, $registry);
    }

    private function assertStrictSchema(array $schema): void
    {
        $type = $schema['type'] ?? null;
        $this->assertNotEmpty($type);
        if (is_array($type)) {
            $this->assertCount(2, $type);
            $this->assertSame('null', $type[1] ?? null);
            $this->assertContains($type[0] ?? null, ['string', 'integer', 'boolean', 'object', 'array']);
        } else {
            $this->assertContains($type, ['string', 'integer', 'boolean', 'object', 'array']);
        }

        if ($type === 'object') {
            $this->assertFalse($schema['additionalProperties'] ?? true);
            $properties = $schema['properties'] ?? [];
            $this->assertSame(array_keys($properties), $schema['required'] ?? []);
            foreach ($properties as $property) {
                $this->assertStrictSchema($property);
            }
        }
    }

    public function test_runtime_has_no_env_fallback_when_database_pointer_is_missing(): void
    {
        config()->set('services.openai.provider_enabled', true);
        config()->set('services.openai.api_key', 'must-not-be-used');
        config()->set('services.openai.model', 'env-model');

        $this->expectException(\RuntimeException::class);
        (new OpenAiResponsesProvider)->respond(new AcademicAiProviderRequest('synthetic', 'instructions', [], [], null, 'corr'));
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $role = Role::create(['code' => 'SUPER_ADMIN', 'name' => 'Super Admin']);
        $permission = Permission::create(['code' => 'platform.institution.manage', 'name' => 'Institution management']);
        $role->permissions()->attach($permission);
        UserRoleAssignment::create(['user_id' => $user->id, 'role_id' => $role->id, 'effective_from' => '2026-01-01']);

        return $user;
    }
}
