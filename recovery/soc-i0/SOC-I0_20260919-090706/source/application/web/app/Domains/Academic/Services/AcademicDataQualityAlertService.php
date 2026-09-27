<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\ClassSession;
use App\Shared\Platform\Alerts\Models\Alert;
use App\Shared\Platform\Alerts\Models\AlertRule;
use App\Shared\Platform\Alerts\Services\AlertService;

class AcademicDataQualityAlertService
{
    public function __construct(
        private readonly StudentAttendanceCompletenessChecker $attendanceChecker,
        private readonly AcademicAlertOwnerResolver $ownerResolver,
        private readonly AlertService $alerts,
    ) {}

    public function evaluateMissingAttendance(ClassSession $session, AlertRule $rule): ?Alert
    {
        $finding = $this->attendanceChecker->check($session);
        $fingerprint = 'student-attendance-session:'.$session->id;
        $active = Alert::query()->where('alert_rule_id', $rule->id)->where('fingerprint', $fingerprint)->whereIn('status', ['OPEN', 'ACKNOWLEDGED', 'RESOLVED'])->first();

        if ($finding['status'] === 'NOT_APPLICABLE' || $finding['is_complete']) {
            return $active === null ? null : $this->alerts->transition($active, $this->ownerResolver->resolveForSession($session), 'CLOSED', 'Attendance condition cleared.');
        }

        $owner = $this->ownerResolver->resolveForSession($session);

        return $this->alerts->raise($rule, $fingerprint, 'HIGH', $owner, [
            'no_participants' => $finding['no_participants'] ?? false,
            'missing_attendance_participant_ids' => $finding['missing_attendance_participant_ids'],
            'unresolved_attendance_participant_ids' => $finding['unresolved_attendance_participant_ids'],
        ], ClassSession::class, (string) $session->id);
    }
}
