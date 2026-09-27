<?php

namespace App\Shared\Platform\Audit\Services;

use App\Shared\Platform\Audit\Models\CorrectionRequest;

class CorrectionRequestService
{
    public function submit(array $attributes): CorrectionRequest
    {
        return CorrectionRequest::create([
            'status' => 'PENDING',
            'version_no' => 1,
            ...$attributes,
        ]);
    }
}
