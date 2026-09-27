<?php

namespace App\Domains\Academic\Semantics;

use App\Domains\Academic\Semantics\Enums\CanonicalAttendanceStatus;

final class CanonicalAttendanceStatusMapper
{
    public function map(?string $status): ?CanonicalAttendanceStatus
    {
        return match ($status) {
            null => null,
            'PRESENT' => CanonicalAttendanceStatus::PRESENT,
            'IZIN', 'PERMISSION' => CanonicalAttendanceStatus::PERMISSION,
            'SICK' => CanonicalAttendanceStatus::SICK,
            'ABSENT' => CanonicalAttendanceStatus::ABSENT,
            'LATE' => CanonicalAttendanceStatus::PRESENT,
            'EXCUSED' => null,
            default => throw new \InvalidArgumentException("Unsupported attendance status: {$status}"),
        };
    }

    public function normalize(?string $status): ?CanonicalAttendanceSemanticResult
    {
        if ($status === null) {
            return null;
        }

        return match ($status) {
            'PRESENT' => new CanonicalAttendanceSemanticResult(CanonicalAttendanceStatus::PRESENT, 'ON_TIME', true, true, 'RESOLVED', $status, false),
            'LATE' => new CanonicalAttendanceSemanticResult(CanonicalAttendanceStatus::PRESENT, 'LATE', true, true, 'RESOLVED', $status, false),
            'IZIN', 'PERMISSION' => new CanonicalAttendanceSemanticResult(CanonicalAttendanceStatus::PERMISSION, null, true, false, 'RESOLVED', $status, false),
            'SICK' => new CanonicalAttendanceSemanticResult(CanonicalAttendanceStatus::SICK, null, true, false, 'RESOLVED', $status, false),
            'ABSENT' => new CanonicalAttendanceSemanticResult(CanonicalAttendanceStatus::ABSENT, null, true, false, 'RESOLVED', $status, false),
            'EXCUSED' => new CanonicalAttendanceSemanticResult(null, null, false, false, 'RECONCILIATION_REQUIRED', $status, true),
            default => throw new \InvalidArgumentException("Unsupported attendance status: {$status}"),
        };
    }
}
