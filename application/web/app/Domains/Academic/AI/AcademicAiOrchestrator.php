<?php

namespace App\Domains\Academic\AI;

use App\Domains\Academic\AI\Contracts\AcademicAiModelProvider;
use App\Domains\Academic\AI\Contracts\AcademicAiProviderRequest;
use App\Domains\Academic\AI\Contracts\AcademicAiRuntimeResult;
use App\Domains\Academic\AI\Contracts\AcademicAiToolContext;
use App\Shared\Platform\Audit\Services\AuditLogger;
use Illuminate\Support\Str;
use Throwable;

class AcademicAiOrchestrator
{
    private const SUBSTANTIVE_TOOLS = [
        'get_student_attendance_summary',
        'get_student_attendance_detail',
        'get_class_attendance_summary',
        'get_class_attendance_roster',
    ];

    public function __construct(
        private readonly AcademicAiModelProvider $provider,
        private readonly AcademicAiToolRegistry $registry,
        private readonly AcademicAiFunctionSchemaAdapter $schemaAdapter,
        private readonly AcademicAiInstructions $instructions,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function ask(AcademicAiToolContext $context, string $question): AcademicAiRuntimeResult
    {
        $correlationId = $context->correlationId ?: (string) Str::uuid();
        $tools = array_values(array_map(fn ($tool): array => $this->schemaAdapter->schema($tool), $this->registry->all()));
        $invoked = [];
        $statuses = [];
        $warnings = [];
        $usage = [];
        $successfulTools = [];
        $roundCount = 0;
        $providerRequestId = null;
        $model = null;
        $previousResponseId = null;
        $input = [];
        $ambiguousStudentResolution = false;

        try {
            for ($round = 0; $round <= (int) config('academic.ai.max_tool_rounds', 4); $round++) {
                $roundCount = $round + 1;
                $response = $this->provider->respond(new AcademicAiProviderRequest(
                    question: $question,
                    instructions: $this->instructions->text(),
                    tools: $tools,
                    input: $input,
                    previousResponseId: $previousResponseId,
                    correlationId: $correlationId,
                ));
                $previousResponseId = $response->responseId;
                $providerRequestId = $response->providerRequestId ?: $providerRequestId;
                $model = $response->model;
                $usage = $response->usage;
                $calls = $response->functionCalls();
                $text = $response->text();
                if ($calls === []) {
                    if ($text === null) {
                        throw new \RuntimeException('Provider tidak mengembalikan jawaban akhir.');
                    }
                    $groundingStatus = $this->groundingStatus($invoked, $successfulTools, $warnings, $statuses);
                    if ($groundingStatus === AcademicAiGroundingStatus::TOOL_GROUNDED) {
                        $result = new AcademicAiRuntimeResult('COMPLETED', $text, $correlationId, $this->provider->name(), $model, $providerRequestId, $invoked, $statuses, $warnings, $usage, $successfulTools, $groundingStatus, $roundCount);
                        $this->audit($context, $result, $question);

                        return $result;
                    }

                    $terminal = $this->terminalResponse($question, $groundingStatus);
                    $result = new AcademicAiRuntimeResult(
                        $terminal['status'],
                        $terminal['text'],
                        $correlationId,
                        $this->provider->name(),
                        $model,
                        $providerRequestId,
                        $invoked,
                        $statuses,
                        array_merge($warnings, $terminal['warnings']),
                        $usage,
                        $successfulTools,
                        $terminal['grounding_status'],
                        $roundCount,
                    );
                    $this->audit($context, $result, $question);

                    return $result;
                }
                if ($round === (int) config('academic.ai.max_tool_rounds', 4)) {
                    throw new \RuntimeException('Batas tool-call AI tercapai.');
                }

                $input = [];
                foreach ($calls as $call) {
                    $name = $call['name'] ?? '';
                    $callId = $call['call_id'] ?? '';
                    if ($callId === '' || $name === '') {
                        throw new \RuntimeException('Function call provider tidak lengkap.');
                    }
                    $invoked[] = $name;
                    $decoded = json_decode((string) ($call['arguments'] ?? ''), true);
                    if (! is_array($decoded) || json_last_error() !== JSON_ERROR_NONE) {
                        throw new \RuntimeException('Argument function call provider tidak valid.');
                    }
                    if ($ambiguousStudentResolution && in_array($name, ['get_student_attendance_summary', 'get_student_attendance_detail'], true)) {
                        throw new \RuntimeException('Identitas santri ambigu; klarifikasi diperlukan sebelum membaca absensi.');
                    }
                    $toolResult = $this->registry->execute($name, $context, $decoded);
                    $ambiguousStudentResolution = $ambiguousStudentResolution || ($name === 'resolve_student' && $toolResult->status === 'AMBIGUOUS');
                    $statuses[] = ['tool' => $name, 'status' => $toolResult->status, 'call_id' => $callId];
                    $warnings = array_merge($warnings, $toolResult->warnings);
                    if ($toolResult->status === 'OK') {
                        $successfulTools[] = $name;
                    }
                    $input[] = ['type' => 'function_call_output', 'call_id' => $callId, 'output' => json_encode($toolResult->toArray(), JSON_THROW_ON_ERROR)];
                }
            }
        } catch (Throwable $exception) {
            $groundingStatus = $this->groundingStatus($invoked, $successfulTools, $warnings, $statuses);
            $result = new AcademicAiRuntimeResult('FAILED', null, $correlationId, $this->provider->name(), $model, $providerRequestId, $invoked, $statuses, array_merge($warnings, [['code' => 'PROVIDER_ORCHESTRATION_FAILURE']]), $usage, $successfulTools, $groundingStatus, $roundCount);
            $this->audit($context, $result, $question, $exception->getMessage());

            return $result;
        }

        throw new \LogicException('Unreachable AI orchestration state.');
    }

    private function audit(AcademicAiToolContext $context, AcademicAiRuntimeResult $result, string $question, ?string $error = null): void
    {
        $this->auditLogger->record([
            'actor_user_id' => $context->user->id,
            'actor_type' => 'USER',
            'action' => 'AI_ACADEMIC_RUNTIME_INVOKED',
            'entity_type' => 'AcademicAiRuntime',
            'entity_id' => $result->correlationId,
            'source_channel' => 'AI',
            'correlation_id' => $result->correlationId,
            'technical_metadata' => [
                'question_digest_algorithm' => 'HMAC-SHA256',
                'question_hmac_sha256' => hash_hmac('sha256', $question, (string) config('app.key', '')),
                'instruction_version' => AcademicAiInstructions::VERSION,
                'provider' => $result->provider,
                'model' => $result->model,
                'provider_request_id' => $result->providerRequestId,
                'tools_invoked' => $result->toolsInvoked,
                'tool_statuses' => $result->toolStatuses,
                'warning_count' => count($result->warnings),
                'runtime_status' => $result->status,
                'usage' => $result->usage,
                'successful_tools' => $result->successfulTools,
                'grounding_status' => $result->groundingStatus,
                'round_count' => $result->roundCount,
                'tool_warning_count' => count($result->warnings),
                'error_code' => $error === null ? null : 'RUNTIME_FAILURE',
            ],
        ]);
    }

    private function groundingStatus(array $invoked, array $successfulTools, array $warnings, array $statuses): string
    {
        foreach ($statuses as $status) {
            if (($status['status'] ?? null) === AcademicAiGroundingStatus::AMBIGUOUS) {
                return AcademicAiGroundingStatus::AMBIGUOUS;
            }
        }
        foreach ($statuses as $status) {
            if (($status['status'] ?? null) === AcademicAiGroundingStatus::NOT_FOUND) {
                return AcademicAiGroundingStatus::NOT_FOUND;
            }
        }
        if (array_intersect($successfulTools, self::SUBSTANTIVE_TOOLS) !== []) {
            return AcademicAiGroundingStatus::TOOL_GROUNDED;
        }
        if ($invoked === []) {
            return AcademicAiGroundingStatus::CLARIFICATION_REQUIRED;
        }

        return $warnings === []
            ? AcademicAiGroundingStatus::TOOL_INCOMPLETE
            : AcademicAiGroundingStatus::TOOL_ERROR;
    }

    private function terminalResponse(string $question, string $groundingStatus): array
    {
        if ($groundingStatus === AcademicAiGroundingStatus::AMBIGUOUS) {
            return ['status' => 'COMPLETED', 'text' => 'Saya menemukan lebih dari satu santri yang cocok. Mohon sebutkan identitas yang lebih spesifik.', 'grounding_status' => $groundingStatus, 'warnings' => []];
        }
        if ($groundingStatus === AcademicAiGroundingStatus::NOT_FOUND) {
            return ['status' => 'COMPLETED', 'text' => 'Santri yang diminta tidak ditemukan pada data akademik yang dapat diakses.', 'grounding_status' => $groundingStatus, 'warnings' => []];
        }
        if ($this->isCapabilityRequest($question)) {
            return ['status' => 'COMPLETED', 'text' => 'Saya dapat membaca ringkasan dan detail kehadiran santri serta ringkasan dan roster kehadiran kelas. Saya tidak dapat mengubah data akademik.', 'grounding_status' => AcademicAiGroundingStatus::CAPABILITY_RESPONSE, 'warnings' => []];
        }
        if ($this->isUnsupportedWriteRequest($question)) {
            return ['status' => 'COMPLETED', 'text' => 'Permintaan perubahan data tidak didukung. Asisten ini hanya menyediakan pembacaan data akademik.', 'grounding_status' => AcademicAiGroundingStatus::UNSUPPORTED_REQUEST, 'warnings' => []];
        }
        if ($groundingStatus === AcademicAiGroundingStatus::TOOL_ERROR) {
            return ['status' => 'FAILED', 'text' => null, 'grounding_status' => $groundingStatus, 'warnings' => []];
        }
        if ($groundingStatus === AcademicAiGroundingStatus::TOOL_INCOMPLETE) {
            return ['status' => 'FAILED', 'text' => null, 'grounding_status' => $groundingStatus, 'warnings' => [['code' => 'FACTUAL_TOOL_EVIDENCE_REQUIRED']]];
        }

        return ['status' => 'FAILED', 'text' => null, 'grounding_status' => AcademicAiGroundingStatus::CLARIFICATION_REQUIRED, 'warnings' => [['code' => 'FACTUAL_TOOL_EVIDENCE_REQUIRED']]];
    }

    private function isCapabilityRequest(string $question): bool
    {
        $normalized = mb_strtolower(trim($question));

        return in_array($normalized, ['bantuan', 'help', 'fitur apa saja', 'kamu bisa apa', 'apa yang bisa kamu lakukan'], true);
    }

    private function isUnsupportedWriteRequest(string $question): bool
    {
        $normalized = mb_strtolower(trim($question));

        return preg_match('/^(ubah|simpan|hapus|edit|perbarui|tandai|catat)\b/u', $normalized) === 1;
    }
}
