<?php

namespace App\Domains\Academic\AI\Contracts;

final class AcademicAiToolResult
{
    public function __construct(
        public readonly string $tool,
        public readonly string $status,
        public readonly array $filters = [],
        public readonly array $entities = [],
        public readonly array $metrics = [],
        public readonly array $records = [],
        public readonly array $warnings = [],
        public readonly array $evidence = [],
        public readonly ?string $dataAsOf = null,
    ) {}

    public function toArray(): array
    {
        return [
            'tool' => $this->tool,
            'status' => $this->status,
            'data_as_of' => $this->dataAsOf ?? now()->toIso8601String(),
            'filters' => $this->filters,
            'entities' => $this->entities,
            'metrics' => $this->metrics,
            'records' => $this->records,
            'warnings' => $this->warnings,
            'evidence' => $this->evidence,
        ];
    }

    public static function ok(string $tool, AcademicAiToolContext $context, array $data = []): self
    {
        return new self(
            tool: $tool,
            status: 'OK',
            filters: $data['filters'] ?? [],
            entities: $data['entities'] ?? [],
            metrics: $data['metrics'] ?? [],
            records: $data['records'] ?? [],
            warnings: $data['warnings'] ?? [],
            evidence: [
                'source_tool' => $tool,
                'authorized_user_id' => (string) $context->user->id,
                'canonical_entity_ids' => $data['canonical_entity_ids'] ?? [],
                'basis_count' => $data['basis_count'] ?? count($data['records'] ?? []),
                'semantic_regime' => $data['semantic_regime'] ?? null,
            ],
        );
    }
}
