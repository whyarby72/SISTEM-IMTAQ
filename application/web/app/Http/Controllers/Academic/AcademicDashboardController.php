<?php

namespace App\Http\Controllers\Academic;

use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Services\AcademicDashboardExportService;
use App\Domains\Academic\Services\AcademicRoleDashboardService;
use App\Models\User;
use App\Shared\Core\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AcademicDashboardController
{
    public function index(Request $request, AcademicRoleDashboardService $dashboard): View|RedirectResponse
    {
        $request->validate(['semester_id' => ['nullable', 'uuid'], 'trend_days' => ['nullable', 'integer', 'in:7,14,30']]);
        $actor = $request->user();
        abort_unless($actor instanceof User, 403);
        ['from' => $from, 'to' => $to, 'month' => $month, 'academicYear' => $academicYear] = $this->resolvePeriod($request);
        if ($month !== null && ($request->filled('from') || $request->filled('to'))) {
            return redirect()->route('academic.dashboard', array_filter([
                'month' => $month,
                'trend_days' => $request->input('trend_days'),
                'semester_id' => $request->input('semester_id'),
            ], static fn ($value) => $value !== null && $value !== ''));
        }
        $semester = $request->filled('semester_id') ? Semester::find($request->input('semester_id')) : null;
        abort_if($request->filled('semester_id') && $semester === null, 404);
        $trendDays = (int) $request->input('trend_days', 14);
        $dashboardData = $dashboard->forUser($actor, $from, $to, $semester, $trendDays);

        $months = $academicYear === null ? collect() : collect(Carbon::parse($academicYear->starts_on)->startOfMonth()->toPeriod(Carbon::parse($academicYear->ends_on)->startOfMonth(), '1 month'));

        return view('academic.dashboard', ['dashboard' => $dashboardData, 'from' => $from, 'to' => $to, 'month' => $month, 'months' => $months, 'semester' => $semester]);
    }

    public function export(Request $request, AcademicDashboardExportService $export): StreamedResponse
    {
        $request->validate(['semester_id' => ['nullable', 'uuid']]);
        $actor = $request->user();
        abort_unless($actor instanceof User, 403);
        ['from' => $from, 'to' => $to] = $this->resolvePeriod($request);
        $semester = $request->filled('semester_id') ? Semester::find($request->input('semester_id')) : null;
        abort_if($request->filled('semester_id') && $semester === null, 404);
        $csv = $export->csv($actor, $from, $to, $semester);

        return response()->streamDownload(fn () => print $csv, 'academic-dashboard.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** @return array{from: Carbon, to: Carbon, month: ?string, academicYear: ?AcademicYear} */
    private function resolvePeriod(Request $request): array
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'month' => ['nullable', 'date_format:Y-m'],
        ]);

        $academicYear = AcademicYear::query()
            ->where('year_code', 'not like', '%-PILOT')
            ->whereDate('starts_on', '<=', now()->toDateString())
            ->whereDate('ends_on', '>=', now()->toDateString())
            ->orderByDesc('starts_on')
            ->first();
        $month = $request->input('month');

        if ($month !== null) {
            $from = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
            $to = $from->copy()->endOfMonth();
            abort_if($academicYear === null || $from->lessThan($academicYear->starts_on->startOfMonth()) || $from->greaterThan($academicYear->ends_on->startOfMonth()), 422, 'Bulan berada di luar tahun ajaran aktif.');
        } else {
            $from = Carbon::parse($request->input('from', now()->startOfMonth()->toDateString()))->startOfDay();
            $to = Carbon::parse($request->input('to', now()->endOfMonth()->toDateString()))->endOfDay();
        }

        abort_if($to->lessThan($from), 422, 'Tanggal akhir harus sama atau setelah tanggal mulai.');

        return compact('from', 'to', 'month', 'academicYear');
    }
}
