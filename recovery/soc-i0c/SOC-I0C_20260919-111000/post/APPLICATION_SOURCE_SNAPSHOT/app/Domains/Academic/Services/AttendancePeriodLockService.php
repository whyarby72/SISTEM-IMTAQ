<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\AttendancePeriodLock;
use App\Models\User;
use App\Shared\Platform\Audit\Services\AuditLogger;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AttendancePeriodLockService
{
    public function __construct(
        private readonly StudentAttendanceCompletenessChecker $completenessChecker,
        private readonly AuditLogger $auditLogger,
        private readonly AcademicAuthorizationService $authorization,
    ) {}

    public function lock(AcademicClass $class, Carbon $month, User $actor, ?Carbon $asOf = null): AttendancePeriodLock
    {
        $asOf ??= now();
        $periodStart = $month->copy()->startOfMonth();
        $periodEnd = $month->copy()->endOfMonth();
        $lockDate = $periodEnd->copy()->addDays(15);

        if ($asOf->toDateString() < $lockDate->toDateString()) {
            throw new InvalidArgumentException('Attendance period cannot be locked before 15 days after month end.');
        }

        if (! $this->authorization->hasAcademicFullAuthority($actor, $asOf)) {
            throw new AuthorizationException('Only Waka Akademik may lock attendance periods.');
        }

        return DB::transaction(function () use ($class, $periodStart, $periodEnd, $actor, $asOf): AttendancePeriodLock {
            $period = AttendancePeriodLock::query()
                ->where('class_id', $class->id)
                ->whereDate('period_start', $periodStart->toDateString())
                ->whereDate('period_end', $periodEnd->toDateString())
                ->lockForUpdate()
                ->first();

            if ($period?->status === 'LOCKED') {
                return $period;
            }

            $sessions = $class->sessions()
                ->whereBetween('planned_start_at', [$periodStart, $periodEnd->copy()->endOfDay()])
                ->whereNotIn('session_status', ['CANCELLED', 'RESCHEDULED'])
                ->get();
            foreach ($sessions as $session) {
                $finding = $this->completenessChecker->check($session);
                if (! $finding['is_complete']) {
                    throw new InvalidArgumentException('Attendance period has incomplete sessions.');
                }
            }

            $period ??= new AttendancePeriodLock(['class_id' => $class->id, 'period_start' => $periodStart, 'period_end' => $periodEnd]);
            $beforeVersion = $period->version_no ?? 1;
            $period->fill(['status' => 'LOCKED', 'locked_by' => $actor->id, 'locked_at' => $asOf, 'version_no' => $beforeVersion]);
            $period->save();

            $this->auditLogger->record([
                'actor_user_id' => $actor->id,
                'action' => 'ATTENDANCE_PERIOD_LOCKED',
                'entity_type' => AttendancePeriodLock::class,
                'entity_id' => (string) $period->id,
                'version_before' => $period->wasRecentlyCreated ? null : $beforeVersion,
                'version_after' => $period->version_no,
                'old_values' => ['status' => 'OPEN'],
                'new_values' => ['status' => 'LOCKED', 'class_id' => (string) $class->id, 'period_start' => $periodStart->toDateString(), 'period_end' => $periodEnd->toDateString()],
            ]);

            return $period->fresh();
        });
    }
}
