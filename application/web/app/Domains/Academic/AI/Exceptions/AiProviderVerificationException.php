<?php

namespace App\Domains\Academic\AI\Exceptions;

use RuntimeException;

final class AiProviderVerificationException extends RuntimeException
{
    public function __construct(
        public readonly string $category,
        public readonly ?string $providerRequestId = null,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($category, $code, $previous);
    }
}
