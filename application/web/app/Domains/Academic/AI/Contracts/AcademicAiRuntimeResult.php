<?php

namespace App\Domains\Academic\AI\Contracts;

final class AcademicAiRuntimeResult
{
    public function __construct(
        public readonly string $status,
        public readonly ?string $text,
        public readonly string $correlationId,
        public readonly ?string $provider,
        public readonly ?string $model,
        public readonly ?string $providerRequestId,
        public readonly array $toolsInvoked = [],
        public readonly array $toolStatuses = [],
        public readonly array $warnings = [],
        public readonly array $usage = [],
        public readonly array $successfulTools = [],
        public readonly string $groundingStatus = 'CLARIFICATION_REQUIRED',
        public readonly int $roundCount = 0,
    ) {}

    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'text' => $this->text,
            'correlation_id' => $this->correlationId,
            'provider' => $this->provider,
            'model' => $this->model,
            'provider_request_id' => $this->providerRequestId,
            'tools_invoked' => $this->toolsInvoked,
            'tool_statuses' => $this->toolStatuses,
            'warnings' => $this->warnings,
            'usage' => $this->usage,
            'successful_tools' => $this->successfulTools,
            'grounding_status' => $this->groundingStatus,
            'round_count' => $this->roundCount,
        ];
    }
}
