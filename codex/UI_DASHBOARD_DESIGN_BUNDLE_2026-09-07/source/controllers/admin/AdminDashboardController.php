<?php

namespace App\Http\Controllers\Admin;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\ScheduleRule;
use App\Domains\Academic\Models\StudentAttendance;
use App\Domains\Academic\Models\MonthlyAttendanceSummary;
use App\Models\User;
use App\Shared\Core\Models\Staff;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AdminDashboardController
{
    public function index(Request $request): View
    {
        $this->authorizeAdmin($request);

        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();
        $julyReport = MonthlyAttendanceSummary::query()->with('academicClass')->where('period', '2026-07')->orderBy('class_id')->get();
        $operationalAttendance = fn ($query) => $query->whereDoesntHave('participant.classSession.teachingAssignment', fn ($assignment) => $assignment->where('source_reference', 'SAMPLE-PILOT'));

        return view('admin.dashboard', [
            'metrics' => [
                'activeClasses' => AcademicClass::query()->where('status', 'ACTIVE')->count(),
                'activeStaff' => Staff::query()->where('record_status', 'ACTIVE')->count(),
                'approvedSchedules' => ScheduleRule::query()->where('workflow_status', 'APPROVED')->count(),
                'presentAttendance' => $operationalAttendance(StudentAttendance::query())->where('attendance_status', 'PRESENT')->count(),
            ],
            'attendanceBreakdown' => $operationalAttendance(StudentAttendance::query())
                ->selectRaw('attendance_status, COUNT(*) as total')
                ->groupBy('attendance_status')
                ->pluck('total', 'attendance_status'),
            'recentSessions' => ClassSession::query()
                ->with('academicClass')
                ->whereDoesntHave('teachingAssignment', fn ($assignment) => $assignment->where('source_reference', 'SAMPLE-PILOT'))
                ->whereBetween('planned_start_at', [$monthStart, $monthEnd])
                ->latest('planned_start_at')
                ->limit(5)
                ->get(),
            'julyReport' => $julyReport,
            'julyReportTotals' => ['present' => $julyReport->sum('present'), 'permission' => $julyReport->sum('permission'), 'sick' => $julyReport->sum('sick'), 'absent' => $julyReport->sum('absent'), 'eligible' => $julyReport->sum('eligible'), 'non_eligible' => $julyReport->sum('non_eligible')],
        ]);
    }

    private function authorizeAdmin(Request $request): void
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);
        $allowed = $user->roleAssignments()->effectiveAt(Carbon::now())->whereHas('role', fn ($query) => $query->whereIn('code', ['SUPER_ADMIN', 'ADMIN_AKADEMIK']))->exists();
        if (! $allowed) {
            throw new AuthorizationException('Only Academic Admin or Super Admin may open the admin dashboard.');
        }
    }
}
