<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\AcademicClass;
use Illuminate\Support\Carbon;

class AttendanceSemanticMetricsService
{
    public function __construct(private readonly CanonicalAttendanceSemanticService $canonicalSemantic) {}

    public function forClassPeriod(AcademicClass $class, Carbon $from, Carbon $to): array
    {
        $canonical = $this->canonicalSemantic->forClassPeriod($class, $from, $to);
        $resolved = (int) $canonical['resolved_opportunities'];
        $counts = $canonical['counts'];
        $rate = static fn (int $numerator, int $denominator) => $denominator === 0 ? null : round(($numerator / $denominator) * 100, 2);

        return [
            ...$canonical,
            'counts' => $counts,
            'physical_presence_rate' => $canonical['attendance_rate'],
            'unexcused_absence_rate' => $rate((int) ($counts['ABSENT'] ?? 0), $resolved),
        ];
    }
}
