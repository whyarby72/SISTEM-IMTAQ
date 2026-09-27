<?php

namespace App\Domains\Academic\AI;

final class AcademicAiGroundingStatus
{
    public const TOOL_GROUNDED = 'TOOL_GROUNDED';

    public const AMBIGUOUS = 'AMBIGUOUS';

    public const NOT_FOUND = 'NOT_FOUND';

    public const CLARIFICATION_REQUIRED = 'CLARIFICATION_REQUIRED';

    public const CAPABILITY_RESPONSE = 'CAPABILITY_RESPONSE';

    public const REFUSAL = 'REFUSAL';

    public const TOOL_ERROR = 'TOOL_ERROR';

    public const TOOL_INCOMPLETE = 'TOOL_INCOMPLETE';

    public const UNSUPPORTED_REQUEST = 'UNSUPPORTED_REQUEST';

    private function __construct() {}
}
