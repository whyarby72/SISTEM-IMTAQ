<?php

namespace Database\Seeders;

use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Services\SessionParticipantSnapshotter;
use App\Shared\Core\Models\AcademicYear;
use Illuminate\Database\Seeder;

class OfficialSessionParticipantReconciliationSeeder extends Seeder
{
    public function run(SessionParticipantSnapshotter $snapshotter): void
    {
        $officialYear = AcademicYear::query()->where('year_code', '2026/2027')->firstOrFail();

        ClassSession::query()
            ->whereNotIn('session_status', ['CANCELLED', 'RESCHEDULED'])
            ->whereHas('academicClass', fn ($query) => $query->where('academic_year_id', $officialYear->id))
            ->orderBy('planned_start_at')
            ->get()
            ->each(fn (ClassSession $session) => $snapshotter->snapshot($session));
    }
}
