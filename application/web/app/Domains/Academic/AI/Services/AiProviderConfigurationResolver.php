<?php

namespace App\Domains\Academic\AI\Services;

final class AiProviderConfigurationResolver
{
    public function __construct(private readonly AiProviderConfigurationService $service) {}

    public function resolve(): ?array
    {
        return $this->service->activeRuntime();
    }
}
