<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\ClassSession;
use Illuminate\Support\Carbon;

class SessionSemanticMetricsService
{
    public function forClassPeriod(AcademicClass $class, Carbon $from, Carbon $to): array
    {
        $sessions = ClassSession::query()
            ->where(fn ($query) => $query
                ->where('class_id', $class->id)
                ->orWhereHas('scopeGroups', fn ($scopeQuery) => $scopeQuery->where('class_id', $class->id)))
            ->whereBetween('planned_start_at', [$from, $to])
            ->get();

        $cancelled = $sessions->where('session_status', 'CANCELLED');
        $rescheduledSources = $sessions->where('session_status', 'RESCHEDULED');
        $counted = $sessions->reject(fn (ClassSession $session) => in_array($session->session_status, ['CANCELLED', 'RESCHEDULED'], true));
        $completed = $counted->where('session_status', 'COMPLETED')->count();
        $countedTotal = $counted->count();

        return [
            'counted_sessions' => $countedTotal,
            'completed_sessions' => $completed,
            'open_sessions' => $counted->whereIn('session_status', ['PLANNED', 'CONFIRMED'])->count(),
            'cancelled_sessions' => $cancelled->count(),
            'rescheduled_source_sessions' => $rescheduledSources->count(),
            'extra_sessions' => $counted->whereIn('session_source', ['EXTRA', 'AD_HOC'])->count(),
            'completion_rate' => $countedTotal === 0 ? null : round(($completed / $countedTotal) * 100, 2),
        ];
    }
}
