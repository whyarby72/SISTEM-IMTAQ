<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\StudentAttendance;

class StudentAttendanceCompletenessChecker
{
    public function check(ClassSession $session): array
    {
        if (in_array($session->session_status, ['CANCELLED', 'RESCHEDULED'], true)) {
            return [
                'session_id' => (string) $session->id,
                'status' => 'NOT_APPLICABLE',
                'is_complete' => true,
                'no_participants' => false,
                'missing_attendance_participant_ids' => [],
                'unresolved_attendance_participant_ids' => [],
            ];
        }

        $participants = $session->studentParticipants()
            ->where('participant_status', 'EXPECTED')
            ->where('is_required', true)
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
        ];
    }
}
