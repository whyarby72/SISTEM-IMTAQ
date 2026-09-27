<?php

namespace App\Domains\Academic\Semantics;

use App\Domains\Academic\Semantics\Enums\CanonicalAttendanceStatus;

final readonly class CanonicalAttendanceSemanticResult
{
    public function __construct(
        public ?CanonicalAttendanceStatus $attendanceOutcome,
        public ?string $punctualityStatus,
        public bool $resolved,
        public bool $physicalPresent,
        public string $canonicalResolutionState,
        public string $legacySourceStatus,
        public bool $reconciliationRequired,
    ) {}

    public function outcomeValue(): ?string
    {
        return $this->attendanceOutcome?->value;
    }
}
