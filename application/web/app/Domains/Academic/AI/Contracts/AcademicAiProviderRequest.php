<?php

namespace App\Domains\Academic\AI\Contracts;

final class AcademicAiProviderRequest
{
    public function __construct(
        public readonly string $question,
        public readonly string $instructions,
        public readonly array $tools,
        public readonly array $input = [],
        public readonly ?string $previousResponseId = null,
        public readonly string $correlationId = '',
    ) {}
}
