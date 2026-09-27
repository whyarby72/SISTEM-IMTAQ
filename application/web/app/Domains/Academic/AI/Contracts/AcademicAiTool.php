<?php

namespace App\Domains\Academic\AI\Contracts;

interface AcademicAiTool
{
    public function name(): string;

    public function description(): string;

    /** @return array<string, mixed> */
    public function inputSchema(): array;

    public function execute(AcademicAiToolContext $context, array $input): AcademicAiToolResult;
}
