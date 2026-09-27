<?php

namespace App\Domains\Academic\AI\Exceptions;

use RuntimeException;

final class AiProviderAdminOperationException extends RuntimeException
{
    public function __construct(
        public readonly string $category,
        public readonly ?string $providerRequestId = null,
        int $code = 0,
    ) {
        parent::__construct($category, $code);
    }
}
