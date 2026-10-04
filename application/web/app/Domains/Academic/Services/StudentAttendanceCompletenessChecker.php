<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\StudentAttendance;
use Illuminate\Auth\Access\AuthorizationException;

class StudentAttendanceCompletenessChecker
{
    public function check(ClassSession $session): array
    {
        return $this->checkForScope($session, [
            'mode' => 'FULL_SESSION',
            'authorized_participant_ids' => [],
            'unmapped_participant_ids' => [],
            'ambiguous_participant_ids' => [],
        ]);
    }

    public function checkForScope(ClassSession $session, array $scope): array
    {
        if (in_array($session->session_status, ['CANCELLED', 'RESCHEDULED'], true)) {
            return [
                'session_id' => (string) $session->id,
                'status' => 'NOT_APPLICABLE',
                'is_complete' => true,
                'no_participants' => false,
                'missing_attendance_participant_ids' => [],
                'unresolved_attendance_participant_ids' => [],
                'required_count' => 0,
                'resolved_count' => 0,
                'missing_count' => 0,
                'scope_mode' => $scope['mode'] ?? 'FULL_SESSION',
            ];
        }

        if (($scope['unmapped_participant_ids'] ?? []) !== [] || ($scope['ambiguous_participant_ids'] ?? []) !== []) {
            throw new AuthorizationException('Kelengkapan tidak dapat dihitung karena roster scope belum terpetakan secara aman.');
        }

        $authorizedIds = array_map('strval', $scope['authorized_participant_ids'] ?? []);
        $participants = $session->studentParticipants()
            ->where('participant_status', 'EXPECTED')
            ->where('is_required', true)
            ->when(($scope['mode'] ?? 'FULL_SESSION') !== 'FULL_SESSION', fn ($query) => $query->whereIn('id', $authorizedIds))
            ->get(['id']);
        $attendances = StudentAttendance::query()
            ->whereIn('session_student_participant_id', $participants->pluck('id'))
            ->get(['session_student_participant_id', 'attendance_status', 'workflow_status']);

        if ($participants->isEmpty()) {
            return [
                'session_id' => (string) $session->id,
                'status' => 'NO_PARTICIPANTS',
                'is_complete' => false,
                'no_participants' => true,
                'missing_attendance_participant_ids' => [],
                'unresolved_attendance_participant_ids' => [],
                'required_count' => 0,
                'resolved_count' => 0,
                'missing_count' => 0,
                'scope_mode' => $scope['mode'] ?? 'FULL_SESSION',
            ];
        }

        $missing = [];
        $unresolved = [];
        foreach ($participants as $participant) {
            $attendance = $attendances->firstWhere('session_student_participant_id', $participant->id);
            if ($attendance === null) {
                $missing[] = (string) $participant->id;

                continue;
            }

            if ($attendance->attendance_status === null || $attendance->workflow_status !== 'VALIDATED') {
                $unresolved[] = (string) $participant->id;
            }
        }

        $isComplete = $missing === [] && $unresolved === [];

        return [
            'session_id' => (string) $session->id,
            'status' => $isComplete ? 'COMPLETE' : 'INCOMPLETE',
            'is_complete' => $isComplete,
            'no_participants' => false,
            'missing_attendance_participant_ids' => $missing,
            'unresolved_attendance_participant_ids' => $unresolved,
            'required_count' => $participants->count(),
            'resolved_count' => $participants->count() - count($missing) - count($unresolved),
            'missing_count' => count($missing),
            'scope_mode' => $scope['mode'] ?? 'FULL_SESSION',
        ];
    }
}
