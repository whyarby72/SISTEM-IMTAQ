<?php

namespace App\Domains\Academic\AI\Contracts;

final class AcademicAiProviderResponse
{
    public function __construct(
        public readonly string $responseId,
        public readonly ?string $providerRequestId,
        public readonly string $model,
        public readonly array $items = [],
        public readonly array $usage = [],
    ) {}

    public function functionCalls(): array
    {
        return array_values(array_filter($this->items, fn (array $item): bool => ($item['type'] ?? null) === 'function_call'));
    }

    public function text(): ?string
    {
        foreach ($this->items as $item) {
            if (($item['type'] ?? null) === 'text' && isset($item['text'])) {
                return (string) $item['text'];
            }
        }

        return null;
    }
}
