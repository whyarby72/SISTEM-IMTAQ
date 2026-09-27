<?php

namespace App\Domains\Academic\AI\Services;

use App\Domains\Academic\AI\AcademicAiFunctionSchemaAdapter;
use App\Domains\Academic\AI\AcademicAiToolRegistry;
use App\Domains\Academic\AI\Contracts\AcademicAiToolContext;
use App\Domains\Academic\AI\Exceptions\AiProviderAdminOperationException;
use App\Domains\Academic\AI\Exceptions\AiProviderDiscoveryException;
use App\Domains\Academic\AI\Exceptions\AiProviderVerificationException;
use App\Domains\Academic\AI\Models\AiProviderActiveConfiguration;
use App\Domains\Academic\AI\Models\AiProviderConfiguration;
use App\Domains\Academic\AI\Models\AiProviderCredential;
use App\Models\User;
use App\Shared\Platform\Audit\Services\AuditLogger;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class AiProviderConfigurationService
{
    public function __construct(
        private readonly AcademicAiToolRegistry $registry,
        private readonly AcademicAiFunctionSchemaAdapter $schemaAdapter,
        private readonly AuditLogger $auditLogger,
        private readonly DatabaseManager $database,
    ) {}

    public function createCredential(User $actor, string $label, string $secret): AiProviderCredential
    {
        $secret = trim($secret);
        if ($secret === '') {
            throw ValidationException::withMessages(['secret' => 'Credential wajib diisi.']);
        }

        $credential = AiProviderCredential::create([
            'provider' => 'openai',
            'label' => trim($label),
            'encrypted_secret' => $secret,
            'secret_last4' => substr($secret, -4),
            'status' => 'PENDING',
        ]);
        $this->audit($actor, 'AI_PROVIDER_CREDENTIAL_CREATED', $credential->id, ['provider' => 'openai', 'status' => 'PENDING'], 'AiProviderCredential');

        return $credential;
    }

    public function verifyCredential(User $actor, AiProviderCredential $credential): AiProviderCredential
    {
        try {
            $this->assertUsableCredential($credential);
            $response = $this->client($credential->encrypted_secret)->get('/v1/models');
            $response->throw();
            $credential->forceFill(['status' => 'VERIFIED', 'verified_at' => now(), 'revoked_at' => null])->save();
            $this->audit($actor, 'AI_PROVIDER_CREDENTIAL_VERIFIED', $credential->id, ['provider' => $credential->provider, 'status' => 'VERIFIED'], 'AiProviderCredential');

            return $credential;
        } catch (RequestException $exception) {
            $category = $this->providerFailureCategory($exception->response?->status());
            $requestId = $exception->response?->header('x-request-id');
            $this->audit($actor, 'AI_PROVIDER_CREDENTIAL_VERIFICATION_FAILED', $credential->id, array_merge(['provider' => $credential->provider, 'failure_category' => $category, 'provider_request_id' => $requestId, 'operation' => 'credential_verification'], $this->safeProviderErrorMetadata($exception->response?->json())), 'AiProviderCredential');
            throw new AiProviderAdminOperationException($category, $requestId);
        } catch (ValidationException $exception) {
            $this->audit($actor, 'AI_PROVIDER_CREDENTIAL_VERIFICATION_FAILED', $credential->id, ['provider' => $credential->provider, 'failure_category' => 'CONFIGURATION_STATE_INVALID', 'operation' => 'credential_verification'], 'AiProviderCredential');
            throw new AiProviderAdminOperationException('CONFIGURATION_STATE_INVALID');
        } catch (\Throwable $exception) {
            $this->audit($actor, 'AI_PROVIDER_CREDENTIAL_VERIFICATION_FAILED', $credential->id, ['provider' => $credential->provider, 'failure_category' => 'UNKNOWN_PROVIDER_ERROR', 'operation' => 'credential_verification'], 'AiProviderCredential');
            throw new AiProviderAdminOperationException('UNKNOWN_PROVIDER_ERROR');
        }
    }

    public function discoverModels(AiProviderCredential $credential, ?User $actor = null): array
    {
        try {
            $this->assertUsableCredential($credential);
            $response = $this->client($credential->encrypted_secret)->get('/v1/models');
            $response->throw();
            $body = $response->json();
            if (! is_array($body) || ! array_key_exists('data', $body) || ! is_array($body['data'])) {
                throw new AiProviderDiscoveryException('INVALID_PROVIDER_RESPONSE', $response->header('x-request-id'));
            }

            return collect($body['data'])->filter(fn ($model): bool => is_array($model) && isset($model['id']))->pluck('id')->filter()->map(fn ($id): string => (string) $id)->sort()->values()->all();
        } catch (AiProviderDiscoveryException $exception) {
            if ($actor !== null) {
                $this->audit($actor, 'AI_PROVIDER_CREDENTIAL_VERIFICATION_FAILED', $credential->id, ['provider' => $credential->provider, 'failure_category' => $exception->category, 'provider_request_id' => $exception->providerRequestId, 'operation' => 'model_discovery'], 'AiProviderCredential');
            }
            throw $exception;
        } catch (RequestException $exception) {
            $status = $exception->response?->status();
            $category = match (true) {
                $status === 401 => 'AUTHENTICATION_FAILED',
                $status === 403 => 'PERMISSION_DENIED',
                $status !== null && $status >= 500 => 'PROVIDER_UNAVAILABLE',
                default => 'UNKNOWN_PROVIDER_ERROR',
            };
            $normalized = new AiProviderDiscoveryException($category, $exception->response?->header('x-request-id'));
            if ($actor !== null) {
                $this->audit($actor, 'AI_PROVIDER_CREDENTIAL_VERIFICATION_FAILED', $credential->id, array_merge(['provider' => $credential->provider, 'failure_category' => $category, 'provider_request_id' => $normalized->providerRequestId, 'operation' => 'model_discovery'], $this->safeProviderErrorMetadata($exception->response?->json())), 'AiProviderCredential');
            }
            throw $normalized;
        } catch (ValidationException $exception) {
            $normalized = new AiProviderDiscoveryException('AUTHENTICATION_FAILED');
            if ($actor !== null) {
                $this->audit($actor, 'AI_PROVIDER_CREDENTIAL_VERIFICATION_FAILED', $credential->id, ['provider' => $credential->provider, 'failure_category' => $normalized->category, 'operation' => 'model_discovery'], 'AiProviderCredential');
            }
            throw $normalized;
        } catch (\Throwable $exception) {
            $normalized = new AiProviderDiscoveryException('UNKNOWN_PROVIDER_ERROR');
            if ($actor !== null) {
                $this->audit($actor, 'AI_PROVIDER_CREDENTIAL_VERIFICATION_FAILED', $credential->id, ['provider' => $credential->provider, 'failure_category' => $normalized->category, 'operation' => 'model_discovery'], 'AiProviderCredential');
            }
            throw $normalized;
        }
    }

    public function createConfiguration(User $actor, AiProviderCredential $credential, string $model, int $maxOutputTokens): AiProviderConfiguration
    {
        $this->assertVerifiedCredential($credential);
        $this->assertOutputTokenLimit($maxOutputTokens);
        $model = trim($model);
        if (! in_array($model, $this->discoverModels($credential, $actor), true)) {
            throw ValidationException::withMessages(['model' => 'Model tidak tersedia untuk credential yang dipilih. Muat ulang discovery lalu pilih model yang tersedia.']);
        }

        $configuration = $this->database->transaction(function () use ($credential, $model, $maxOutputTokens): AiProviderConfiguration {
            $this->lockEquivalentDraftKey('openai', (string) $credential->id, $model, $maxOutputTokens);
            $existing = AiProviderConfiguration::query()->where('provider', 'openai')->where('credential_id', $credential->id)->where('model', $model)->where('max_output_tokens', $maxOutputTokens)->where('status', 'DRAFT')->lockForUpdate()->first();
            if ($existing !== null) {
                throw ValidationException::withMessages(['configuration' => 'Konfigurasi DRAFT yang setara sudah ada. Gunakan DRAFT tersebut atau ubah model/token.']);
            }

            return AiProviderConfiguration::create([
                'provider' => 'openai',
                'credential_id' => $credential->id,
                'model' => $model,
                'max_output_tokens' => $maxOutputTokens,
                'status' => 'DRAFT',
            ]);
        });
        $this->audit($actor, 'AI_PROVIDER_CONFIGURATION_CREATED', $configuration->id, ['provider' => 'openai', 'model' => $configuration->model, 'status' => 'DRAFT']);

        return $configuration;
    }

    private function lockEquivalentDraftKey(string $provider, string $credentialId, string $model, int $maxOutputTokens): void
    {
        if ($this->database->connection()->getDriverName() !== 'pgsql') {
            return;
        }

        $key = abs((int) (hexdec(substr(hash('sha256', implode('|', [$provider, $credentialId, $model, $maxOutputTokens])), 0, 8)) % 2147483647));
        $this->database->select('select pg_advisory_xact_lock(?)', [$key]);
    }

    public function verifyConfiguration(User $actor, AiProviderConfiguration $configuration): AiProviderConfiguration
    {
        $configuration->load('credential');
        $providerRequestId = null;

        try {
            if ($configuration->status !== 'DRAFT') {
                throw new AiProviderVerificationException('CONFIGURATION_STATE_INVALID');
            }
            if ($configuration->credential->status === 'REVOKED') {
                throw new AiProviderVerificationException('CONFIGURATION_STATE_INVALID');
            }
            $this->assertVerifiedCredential($configuration->credential);
            $this->assertOutputTokenLimit((int) $configuration->max_output_tokens);

            $schemas = array_values(array_map(fn ($tool): array => $this->schemaAdapter->schema($tool), $this->registry->all()));
            if (count($schemas) !== 5 || ! array_is_list($schemas)) {
                throw new AiProviderVerificationException('SCHEMA_REJECTED');
            }

            $token = 'IMTAQ_SYNTHETIC_PROVIDER_VERIFY_'.Str::upper(Str::random(16));
            $first = $this->verificationRequest($configuration, $schemas, $token, [['role' => 'user', 'content' => 'Resolve synthetic token '.$token]], ['type' => 'function', 'name' => 'resolve_student']);
            $providerRequestId = $first['request_id'];
            $call = $this->singleFunctionCall($first['body']);
            if (($call['name'] ?? null) !== 'resolve_student' || ! is_string($call['call_id'] ?? null) || $call['call_id'] === '') {
                throw new AiProviderVerificationException('FUNCTION_ROUNDTRIP_FAILED', $providerRequestId);
            }

            $arguments = json_decode((string) ($call['arguments'] ?? ''), true);
            if (! is_array($arguments) || ($arguments['query'] ?? null) !== $token) {
                throw new AiProviderVerificationException('FUNCTION_ROUNDTRIP_FAILED', $providerRequestId);
            }

            $toolResult = $this->registry->execute('resolve_student', new AcademicAiToolContext($actor, $providerRequestId), $arguments);
            if ($toolResult->status !== 'NOT_FOUND') {
                throw new AiProviderVerificationException('FUNCTION_ROUNDTRIP_FAILED', $providerRequestId);
            }

            $continuationInput = array_merge(
                [['role' => 'user', 'content' => 'Resolve synthetic token '.$token]],
                $first['body']['output'],
                [[
                    'type' => 'function_call_output',
                    'call_id' => $call['call_id'],
                    'output' => json_encode($toolResult->toArray(), JSON_THROW_ON_ERROR),
                ]],
            );
            $second = $this->verificationRequest($configuration, $schemas, $token, $continuationInput, 'none');
            $providerRequestId = $second['request_id'] ?: $providerRequestId;
            $completion = $this->completionText($second['body']);
            if ($completion !== 'IMTAQ_SYNTHETIC_VERIFICATION_COMPLETE') {
                throw new AiProviderVerificationException('FUNCTION_ROUNDTRIP_FAILED', $providerRequestId);
            }

            $configuration->forceFill([
                'status' => 'VERIFIED',
                'verified_at' => now(),
                'verification_metadata' => ['store' => false, 'tool_count' => count($schemas), 'provider_request_id' => $providerRequestId, 'verification' => 'SYNTHETIC_FUNCTION_ROUNDTRIP'],
            ])->save();
            $this->audit($actor, 'AI_PROVIDER_CONFIGURATION_VERIFIED', $configuration->id, ['provider' => 'openai', 'model' => $configuration->model, 'status' => 'VERIFIED', 'tool_count' => count($schemas), 'verification' => 'SYNTHETIC_FUNCTION_ROUNDTRIP']);

            return $configuration;
        } catch (AiProviderVerificationException $exception) {
            $this->auditFailure($actor, $configuration, $exception->category, $exception->providerRequestId ?: $providerRequestId);
            throw $exception;
        } catch (RequestException $exception) {
            $safeProviderError = $this->safeProviderErrorMetadata($exception->response?->json());
            $category = $this->providerFailureCategory($exception->response?->status(), $safeProviderError);
            $requestId = $exception->response?->header('x-request-id');
            $this->auditFailure($actor, $configuration, $category, $requestId, 'AI_PROVIDER_CONFIGURATION_VERIFICATION_FAILED', $safeProviderError);
            throw new AiProviderVerificationException($category, $requestId, $exception->getCode(), $exception);
        } catch (ValidationException $exception) {
            $this->auditFailure($actor, $configuration, 'CONFIGURATION_STATE_INVALID', $providerRequestId);
            throw new AiProviderVerificationException('CONFIGURATION_STATE_INVALID', $providerRequestId, $exception->getCode(), $exception);
        } catch (\Throwable $exception) {
            $this->auditFailure($actor, $configuration, 'UNKNOWN_PROVIDER_ERROR', $providerRequestId);
            throw new AiProviderVerificationException('UNKNOWN_PROVIDER_ERROR', $providerRequestId, $exception->getCode(), $exception);
        }
    }

    /** @return array{body: array, response_id: string, request_id: ?string} */
    private function verificationRequest(AiProviderConfiguration $configuration, array $schemas, string $token, ?array $input = null, string|array $toolChoice = 'auto'): array
    {
        $payload = [
            'model' => $configuration->model,
            'instructions' => 'Synthetic provider verification only. Use only the registered resolve_student tool. Never return Academic facts. After NOT_FOUND, return exactly IMTAQ_SYNTHETIC_VERIFICATION_COMPLETE.',
            'input' => $input ?? 'Resolve synthetic token '.$token,
            'tools' => $schemas,
            'tool_choice' => $toolChoice,
            'parallel_tool_calls' => false,
            'store' => false,
            'max_output_tokens' => (int) $configuration->max_output_tokens,
        ];
        $response = $this->client($configuration->credential->encrypted_secret)->post('/v1/responses', $payload);
        $response->throw();
        $body = $response->json();
        if (! is_array($body) || ! is_string($body['id'] ?? null) || ! is_array($body['output'] ?? null)) {
            throw new AiProviderVerificationException('INVALID_PROVIDER_RESPONSE', $response->header('x-request-id'));
        }

        return ['body' => $body, 'response_id' => $body['id'], 'request_id' => $response->header('x-request-id')];
    }

    private function singleFunctionCall(array $body): array
    {
        $calls = array_values(array_filter($body['output'], fn ($item): bool => is_array($item) && ($item['type'] ?? null) === 'function_call'));
        if (count($calls) !== 1) {
            throw new AiProviderVerificationException('FUNCTION_ROUNDTRIP_FAILED');
        }

        return $calls[0];
    }

    private function completionText(array $body): ?string
    {
        $texts = [];
        foreach ($body['output'] as $item) {
            if (! is_array($item) || ($item['type'] ?? null) !== 'message') {
                continue;
            }
            foreach ((array) ($item['content'] ?? []) as $content) {
                if (($content['type'] ?? null) === 'output_text') {
                    $texts[] = (string) ($content['text'] ?? '');
                }
            }
        }

        return count($texts) === 1 ? trim($texts[0]) : null;
    }

    private function providerFailureCategory(?int $status, array $safeProviderError = []): string
    {
        if ($status === 400
            && ($safeProviderError['provider_error_type'] ?? null) === 'invalid_request_error'
            && ($safeProviderError['provider_error_code'] ?? null) === 'invalid_type'
            && ($safeProviderError['provider_error_param'] ?? null) === 'input') {
            return 'INVALID_INPUT_TYPE';
        }

        return match (true) {
            $status === 401 => 'AUTHENTICATION_FAILED',
            $status === 403 => 'PERMISSION_DENIED',
            $status === 404 => 'MODEL_UNAVAILABLE',
            in_array($status, [408, 409, 429], true) => 'RATE_OR_QUOTA_LIMITED',
            $status !== null && $status >= 500 => 'PROVIDER_UNAVAILABLE',
            $status === 400 => 'SCHEMA_REJECTED',
            default => 'UNKNOWN_PROVIDER_ERROR',
        };
    }

    public function activate(User $actor, AiProviderConfiguration $configuration): AiProviderActiveConfiguration
    {
        try {
            return $this->database->transaction(function () use ($actor, $configuration): AiProviderActiveConfiguration {
                $configuration->load('credential');
                $this->assertVerifiedCredential($configuration->credential);
                if ($configuration->status !== 'VERIFIED') {
                    throw ValidationException::withMessages(['configuration' => 'Configuration harus VERIFIED sebelum diaktifkan.']);
                }
                $pointer = AiProviderActiveConfiguration::query()->where('provider', 'openai')->lockForUpdate()->first();
                if ($pointer === null) {
                    $pointer = new AiProviderActiveConfiguration(['provider' => 'openai', 'version_no' => 1]);
                } else {
                    $pointer->version_no++;
                }
                AiProviderConfiguration::query()->where('status', 'ACTIVE')->where('id', '!=', $configuration->id)->update(['status' => 'SUPERSEDED']);
                $configuration->forceFill(['status' => 'ACTIVE', 'activated_at' => now()])->save();
                $pointer->fill(['configuration_id' => $configuration->id, 'runtime_enabled' => true])->save();
                $this->audit($actor, 'AI_PROVIDER_CONFIGURATION_ACTIVATED', $configuration->id, ['provider' => 'openai', 'model' => $configuration->model, 'version_no' => $pointer->version_no]);

                return $pointer;
            });
        } catch (ValidationException $exception) {
            $this->auditFailure($actor, $configuration, 'CONFIGURATION_STATE_INVALID', null, 'AI_PROVIDER_CONFIGURATION_ACTIVATION_FAILED');
            throw $exception;
        } catch (\Throwable $exception) {
            $this->auditFailure($actor, $configuration, 'UNKNOWN_PROVIDER_ERROR', null, 'AI_PROVIDER_CONFIGURATION_ACTIVATION_FAILED');
            throw new AiProviderAdminOperationException('UNKNOWN_PROVIDER_ERROR');
        }
    }

    public function setRuntimeEnabled(User $actor, bool $enabled): AiProviderActiveConfiguration
    {
        try {
            $pointer = AiProviderActiveConfiguration::query()->where('provider', 'openai')->firstOrFail();
            $pointer->forceFill(['runtime_enabled' => $enabled])->save();
            $this->audit($actor, $enabled ? 'AI_PROVIDER_RUNTIME_ENABLED' : 'AI_PROVIDER_RUNTIME_DISABLED', $pointer->id, ['provider' => 'openai', 'runtime_enabled' => $enabled], 'AiProviderActiveConfiguration');

            return $pointer;
        } catch (\Throwable $exception) {
            $this->audit($actor, 'AI_PROVIDER_RUNTIME_OPERATION_FAILED', 'openai', ['provider' => 'openai', 'failure_category' => 'CONFIGURATION_STATE_INVALID', 'runtime_enabled' => $enabled], 'AiProviderRuntime');
            throw new AiProviderAdminOperationException('CONFIGURATION_STATE_INVALID');
        }
    }

    public function revoke(User $actor, AiProviderCredential $credential): void
    {
        $active = AiProviderActiveConfiguration::query()->with('configuration')->where('provider', 'openai')->first();
        if ($active?->configuration?->credential_id === $credential->id && $active->runtime_enabled) {
            throw ValidationException::withMessages(['credential' => 'Credential aktif tidak dapat direvoke sebelum konfigurasi diganti atau runtime dimatikan.']);
        }
        $credential->forceFill(['status' => 'REVOKED', 'revoked_at' => now()])->save();
        $this->audit($actor, 'AI_PROVIDER_CREDENTIAL_REVOKED', $credential->id, ['provider' => 'openai', 'status' => 'REVOKED'], 'AiProviderCredential');
    }

    public function activeRuntime(): ?array
    {
        $pointer = AiProviderActiveConfiguration::query()->with('configuration.credential')->where('provider', 'openai')->first();
        if (! $pointer?->runtime_enabled || ! $pointer->configuration || $pointer->configuration->status !== 'ACTIVE') {
            return null;
        }
        $credential = $pointer->configuration->credential;
        if (! $credential || $credential->status === 'REVOKED') {
            return null;
        }

        return ['credential' => $credential->encrypted_secret, 'model' => $pointer->configuration->model, 'max_output_tokens' => $pointer->configuration->max_output_tokens];
    }

    private function client(string $secret): PendingRequest
    {
        return Http::withToken($secret)->acceptJson()->timeout((int) config('services.openai.timeout_seconds', 30))->baseUrl(rtrim((string) config('services.openai.base_url'), '/'));
    }

    private function assertUsableCredential(AiProviderCredential $credential): void
    {
        if ($credential->provider !== 'openai' || $credential->status === 'REVOKED') {
            throw ValidationException::withMessages(['credential' => 'Credential provider tidak dapat digunakan.']);
        }
    }

    private function assertVerifiedCredential(AiProviderCredential $credential): void
    {
        $this->assertUsableCredential($credential);
        if (! in_array($credential->status, ['VERIFIED', 'STANDBY'], true)) {
            throw ValidationException::withMessages(['credential' => 'Credential harus diverifikasi terlebih dahulu.']);
        }
    }

    private function assertOutputTokenLimit(int $value): void
    {
        if ($value < 128 || $value > 4000) {
            throw ValidationException::withMessages(['max_output_tokens' => 'Nilai harus berada di antara 128 dan 4000.']);
        }
    }

    private function audit(User $actor, string $action, string $entityId, array $metadata, string $entityType = 'AiProviderConfiguration'): void
    {
        $this->auditLogger->record(['actor_user_id' => $actor->id, 'action' => $action, 'entity_type' => $entityType, 'entity_id' => $entityId, 'source_channel' => 'WEB', 'technical_metadata' => $metadata]);
    }

    private function safeProviderErrorMetadata(mixed $body): array
    {
        $error = is_array($body) && is_array($body['error'] ?? null) ? $body['error'] : [];

        return collect(['type', 'code', 'param'])
            ->mapWithKeys(fn (string $field): array => ['provider_error_'.$field => $this->safeProviderErrorValue($error[$field] ?? null)])
            ->all();
    }

    private function safeProviderErrorValue(mixed $value): ?string
    {
        if (! is_string($value) || @preg_match('//u', $value) !== 1) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : Str::limit($value, 160, '');
    }

    private function auditFailure(User $actor, AiProviderConfiguration $configuration, string $category, ?string $providerRequestId, string $action = 'AI_PROVIDER_CONFIGURATION_VERIFICATION_FAILED', array $safeProviderError = []): void
    {
        $safeProviderError = array_intersect_key($safeProviderError, array_flip(['provider_error_type', 'provider_error_code', 'provider_error_param']));
        $this->audit($actor, $action, $configuration->id, [
            'provider' => 'openai',
            'credential_id' => (string) $configuration->credential_id,
            'model' => $configuration->model,
            'failure_category' => $category,
            'provider_request_id' => $providerRequestId,
            ...$safeProviderError,
        ]);
    }
}
