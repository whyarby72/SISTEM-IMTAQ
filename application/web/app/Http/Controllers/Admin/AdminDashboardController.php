<?php

namespace App\Http\Controllers\Admin;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\MonthlyAttendanceSummary;
use App\Domains\Academic\Models\MonthlyStudentAttendanceSnapshot;
use App\Domains\Academic\Models\ScheduleRule;
use App\Domains\Academic\Models\StudentAttendance;
use App\Domains\Academic\Services\AcademicAuthorizationService;
use App\Models\User;
use App\Shared\Core\Models\Staff;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AdminDashboardController
{
    public function index(Request $request): View|RedirectResponse
    {
        $this->authorizeAdmin($request);
        $user = $request->user();
        if ($user instanceof User && app(AcademicAuthorizationService::class)->hasAcademicFullAuthority($user, Carbon::now())) {
            return to_route('academic.dashboard');
        }

        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();
        $julyReport = MonthlyAttendanceSummary::query()->with('academicClass')->where('period', '2026-07')->orderBy('class_id')->get();
        $officialClassIds = AcademicClass::query()
            ->where('status', 'ACTIVE')
            ->whereHas('academicYear', fn ($query) => $query->where('year_code', 'not like', '%-PILOT'))
            ->pluck('id');
        $officialTeachingAssignments = fn ($query) => $query
            ->where(fn ($assignment) => $assignment->whereNull('source_reference')->orWhere('source_reference', '!=', 'SAMPLE-PILOT'));
        $operationalAttendance = fn ($query) => $query->whereDoesntHave('participant.classSession.teachingAssignment', fn ($assignment) => $assignment->where('source_reference', 'SAMPLE-PILOT'));

        return view('admin.dashboard', [
            'metrics' => [
                'activeClasses' => $officialClassIds->count(),
                'activeStudents' => MonthlyStudentAttendanceSnapshot::query()->where('period', '2026-07')->distinct('student_id')->count('student_id'),
                'activeStaff' => Staff::query()->where('record_status', 'ACTIVE')->whereHas('teachingAssignments', $officialTeachingAssignments)->count(),
                'approvedSchedules' => ScheduleRule::query()->where('workflow_status', 'APPROVED')->count(),
                'presentAttendance' => $operationalAttendance(StudentAttendance::query())->where('attendance_status', 'PRESENT')->count(),
            ],
            'attendanceBreakdown' => $operationalAttendance(StudentAttendance::query())
                ->selectRaw('attendance_status, COUNT(*) as total')
                ->groupBy('attendance_status')
                ->pluck('total', 'attendance_status'),
            'recentSessions' => ClassSession::query()
                ->with(['academicClass', 'studentParticipants.attendance'])
                ->whereDoesntHave('teachingAssignment', fn ($assignment) => $assignment->where('source_reference', 'SAMPLE-PILOT'))
                ->whereBetween('planned_start_at', [$monthStart, $monthEnd])
                ->orderByRaw("CASE WHEN session_status = 'COMPLETED' THEN 0 ELSE 1 END")
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
        $allowed = app(AcademicAuthorizationService::class)->hasAcademicFullAuthority($user, Carbon::now());
        if (! $allowed) {
            throw new AuthorizationException('Only Waka Akademik or Super Admin may open the admin dashboard.');
        }
    }
}
