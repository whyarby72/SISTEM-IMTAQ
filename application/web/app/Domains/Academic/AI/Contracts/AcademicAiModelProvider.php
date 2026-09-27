<?php

namespace App\Domains\Academic\AI\Contracts;

interface AcademicAiModelProvider
{
    public function name(): string;

    public function respond(AcademicAiProviderRequest $request): AcademicAiProviderResponse;
}
