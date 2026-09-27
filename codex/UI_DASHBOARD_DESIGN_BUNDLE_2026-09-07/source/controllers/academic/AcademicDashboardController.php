<?php

namespace App\Http\Controllers\Academic;

use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Services\AcademicRoleDashboardService;
use App\Domains\Academic\Services\AcademicDashboardExportService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AcademicDashboardController
{
    public function index(Request $request, AcademicRoleDashboardService $dashboard): View
    {
        $request->validate(['from' => ['nullable', 'date'], 'to' => ['nullable', 'date'], 'semester_id' => ['nullable', 'uuid']]);
        $actor = $request->user();
        abort_unless($actor instanceof User, 403);
        $from = Carbon::parse($request->input('from', now()->startOfMonth()->toDateString()))->startOfDay();
        $to = Carbon::parse($request->input('to', now()->endOfMonth()->toDateString()))->endOfDay();
        abort_if($to->lessThan($from), 422, 'Tanggal akhir harus sama atau setelah tanggal mulai.');
        $semester = $request->filled('semester_id') ? Semester::find($request->input('semester_id')) : null;
        abort_if($request->filled('semester_id') && $semester === null, 404);
        $dashboardData = $dashboard->forUser($actor, $from, $to, $semester);

        return view('academic.dashboard', ['dashboard' => $dashboardData, 'from' => $from, 'to' => $to, 'semester' => $semester]);
    }

    public function export(Request $request, AcademicDashboardExportService $export): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $request->validate(['from' => ['nullable', 'date'], 'to' => ['nullable', 'date'], 'semester_id' => ['nullable', 'uuid']]);
        $actor = $request->user();
        abort_unless($actor instanceof User, 403);
        $from = Carbon::parse($request->input('from', now()->startOfMonth()->toDateString()))->startOfDay();
        $to = Carbon::parse($request->input('to', now()->endOfMonth()->toDateString()))->endOfDay();
        abort_if($to->lessThan($from), 422, 'Tanggal akhir harus sama atau setelah tanggal mulai.');
        $semester = $request->filled('semester_id') ? Semester::find($request->input('semester_id')) : null;
        abort_if($request->filled('semester_id') && $semester === null, 404);
        $csv = $export->csv($actor, $from, $to, $semester);
        return response()->streamDownload(fn () => print $csv, 'academic-dashboard.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
