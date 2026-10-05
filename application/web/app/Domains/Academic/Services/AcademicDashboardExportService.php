<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\Semester;
use App\Models\User;
use App\Shared\Platform\Presentation\AcademicBusinessTime;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Carbon;

class AcademicDashboardExportService
{
    public function __construct(private readonly AcademicRoleDashboardService $dashboard) {}

    public function csv(User $actor, Carbon $from, Carbon $to, ?Semester $semester = null): string
    {
        $current = Carbon::now(AcademicBusinessTime::timezone());
        if (! $actor->roleAssignments()->effectiveAt($current)->whereHas('role.permissions', fn ($query) => $query->where('code', 'academic.dashboard.export'))->exists()) {
            throw new AuthorizationException('Export permission is required for the Academic dashboard.');
        }

        $data = $this->dashboard->forUser($actor, $from, $to, $semester);
        $stream = fopen('php://temp', 'r+');
        fputcsv($stream, ['role', 'class', 'attendance_completeness_pct', 'physical_presence_pct', 'session_completion_pct', 'extra_sessions', 'semester', 'subject', 'grade_expected', 'grade_locked', 'grade_mean', 'grade_official']);
        foreach ($data['classes'] as $item) {
            $grades = $semester === null || $item['grades'] === null || $item['grades']->isEmpty() ? [null] : $item['grades'];
            foreach ($grades as $grade) {
                fputcsv($stream, [$data['role'], $item['class']->display_name, $item['attendance']['completeness_rate'], $item['attendance']['physical_presence_rate'], $item['sessions']['completion_rate'], $item['sessions']['extra_sessions'], $semester?->display_name, $grade === null ? null : $grade['subject']->subject_name, $grade === null ? null : $grade['expected_count'], $grade === null ? null : $grade['locked_count'], $grade === null ? null : $grade['mean_score'], $grade === null ? null : ($grade['is_official'] ? 'YES' : 'NO')]);
            }
        }
        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);

        return $csv;
    }
}
