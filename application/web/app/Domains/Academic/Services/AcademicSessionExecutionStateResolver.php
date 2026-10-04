<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\ClassSession;
use App\Shared\Platform\Presentation\AcademicBusinessTime;
use Illuminate\Support\Carbon;

/**
 * Provides the single server-owned interpretation of a session's execution
 * state for dashboard and attendance workflows.
 */
class AcademicSessionExecutionStateResolver
{
    public function __construct(private readonly SessionOccurrenceCutover $cutover) {}

    /**
     * @return array{
     *     occurrence_regime:string,
     *     execution_state:string,
     *     attendance_obligation_exists:bool,
     *     attendance_input_allowed:bool,
     *     occurrence_action_required:bool,
     *     effective_occurrence:?object,
     *     business_date:string,
     *     today_business_date:string,
     *     is_historical:bool
     * }
     */
    public function resolve(ClassSession $session, ?Carbon $now = null): array
    {
        $now ??= Carbon::now(AcademicBusinessTime::timezone());
        $regime = $this->cutover->regime($session);
        $businessDate = AcademicBusinessTime::date($session->planned_start_at);
        $today = $now->copy()->setTimezone(AcademicBusinessTime::timezone())->toDateString();

        if ($regime === SessionOccurrenceCutover::CANONICAL) {
            $effective = $session->relationLoaded('effectiveOccurrenceVersion')
                ? $session->effectiveOccurrenceVersion
                : $session->effectiveOccurrenceVersion()->first();
            $status = $effective?->occurrence_status;

            return [
                'occurrence_regime' => $regime,
                'execution_state' => match ($status) {
                    'SCHEDULED' => 'SCHEDULED',
                    'HELD' => 'HELD',
                    'CANCELLED' => 'CANCELLED',
                    'RESCHEDULED' => 'RESCHEDULED',
                    default => 'OCCURRENCE_PENDING',
                },
                'attendance_obligation_exists' => $status === 'HELD',
                'attendance_input_allowed' => $status === 'HELD',
                'occurrence_action_required' => in_array($status, [null, 'SCHEDULED'], true),
                'effective_occurrence' => $effective,
                'business_date' => $businessDate,
                'today_business_date' => $today,
                'is_historical' => $businessDate < $today,
            ];
        }

        $attendanceAllowed = ! in_array($session->session_status, ['CANCELLED', 'RESCHEDULED'], true);

        return [
            'occurrence_regime' => $regime,
            'execution_state' => 'LEGACY',
            'attendance_obligation_exists' => $attendanceAllowed,
            // The legacy finalizer keeps its accepted idempotent COMPLETED
            // behavior; the legacy draft/teacher services retain their own
            // PLANNED/CONFIRMED guards below this resolver.
            'attendance_input_allowed' => $attendanceAllowed,
            'occurrence_action_required' => false,
            'effective_occurrence' => null,
            'business_date' => $businessDate,
            'today_business_date' => $today,
            'is_historical' => $businessDate < $today,
        ];
    }
}
