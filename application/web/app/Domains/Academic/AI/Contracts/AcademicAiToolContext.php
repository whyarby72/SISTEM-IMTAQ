<?php

namespace App\Domains\Academic\AI\Contracts;

use App\Models\User;

final class AcademicAiToolContext
{
    public function __construct(
        public readonly User $user,
        public readonly ?string $correlationId = null,
    ) {}
}
