<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\SessionStudentParticipant;
use App\Domains\Academic\Models\StudentClassEnrollment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SessionParticipantSnapshotter
{
    public function snapshot(ClassSession $session): Collection
    {
        return DB::transaction(function () use ($session): Collection {
            $lockedSession = ClassSession::query()->whereKey($session->id)->lockForUpdate()->firstOrFail();
            $date = $lockedSession->planned_start_at->toDateString();
            $scopeClassIds = $lockedSession->scopeGroups()->pluck('class_id')->all();
            if ($scopeClassIds === []) {
                $scopeClassIds = [$lockedSession->class_id];
            }
            $enrollments = StudentClassEnrollment::query()
                ->whereIn('class_id', $scopeClassIds)
                ->where('status', 'ACTIVE')
                ->whereDate('effective_from', '<=', $date)
                ->where(function ($query) use ($date): void {
                    $query->whereNull('effective_until')->orWhereDate('effective_until', '>', $date);
                })
                ->get();

            return $enrollments->map(function (StudentClassEnrollment $enrollment) use ($lockedSession): SessionStudentParticipant {
                return SessionStudentParticipant::firstOrCreate(
                    ['class_session_id' => $lockedSession->id, 'student_id' => $enrollment->student_id],
                    ['participant_basis' => 'CLASS_ENROLLMENT', 'participant_status' => 'EXPECTED', 'is_required' => true]
                );
            });
        });
    }
}
