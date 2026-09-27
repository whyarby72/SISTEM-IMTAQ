<?php

namespace App\Http\Controllers\Academic;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Services\AcademicAuthorizationService;
use App\Domains\Academic\Services\AttendanceExceptionMonitor;
use App\Domains\Academic\Services\CancellationService;
use App\Domains\Academic\Services\SessionParticipantSnapshotter;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AttendanceExceptionController
{
    public function index(Request $request, AttendanceExceptionMonitor $monitor): View
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        $exceptions = $monitor->incompleteSessions($user);
        $listFilters = $request->validate(['list_class_id' => ['nullable', 'uuid'], 'list_month' => ['nullable', 'date_format:Y-m'], 'list_sort' => ['nullable', 'in:oldest,newest']]);
        $request->merge([
            'from' => $this->normalizeDateInput($request->input('from')),
            'to' => $this->normalizeDateInput($request->input('to')),
        ]);
        $filters = $request->validate(['class_id' => ['nullable', 'uuid'], 'from' => ['nullable', 'date'], 'to' => ['nullable', 'date']]);
        $bulkFormFilters = $filters;
        if (empty($bulkFormFilters['from']) && ! empty($listFilters['list_month'])) {
            $month = Carbon::createFromFormat('Y-m', $listFilters['list_month']);
            $bulkFormFilters['from'] = $month->copy()->startOfMonth()->format('Y-m-d');
            $bulkFormFilters['to'] = $month->copy()->endOfMonth()->format('Y-m-d');
        }
        if (empty($bulkFormFilters['class_id'])) {
            $bulkFormFilters['class_id'] = $listFilters['list_class_id'] ?? null;
        }
        $classes = AcademicClass::query()->where('status', 'ACTIVE')->whereHas('academicYear', fn ($query) => $query->where('year_code', 'not like', '%-PILOT'))->orderBy('display_name')->get();
        $bulkSessions = $this->bulkCandidates($filters);
        $exceptions = $exceptions
            ->when($listFilters['list_class_id'] ?? null, fn ($items, $classId) => $items->where('session.class_id', $classId))
            ->when($listFilters['list_month'] ?? null, fn ($items, $month) => $items->filter(fn (array $item): bool => $item['session']->planned_start_at->format('Y-m') === $month))
            ->sortBy(fn (array $item) => $item['session']->planned_start_at->getTimestamp(), SORT_NUMERIC, ($listFilters['list_sort'] ?? 'newest') !== 'oldest')
            ->values();

        return view('academic.attendance.exceptions', compact('exceptions', 'classes', 'filters', 'bulkFormFilters', 'bulkSessions', 'listFilters'));
    }

    public function bulkCancel(Request $request, CancellationService $cancellationService): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);
        $allowed = app(AcademicAuthorizationService::class)->hasAcademicFullAuthority($user);
        abort_unless($allowed, 403, 'Hanya Waka Akademik atau Super Admin yang dapat membatalkan sesi secara massal.');
        $request->merge([
            'from' => $this->normalizeDateInput($request->input('from')),
            'to' => $this->normalizeDateInput($request->input('to')),
        ]);
        $payload = $request->validate(['class_id' => ['nullable', 'uuid'], 'from' => ['required', 'date'], 'to' => ['required', 'date', 'after_or_equal:from'], 'reason' => ['required', 'string', 'max:1000']]);
        $result = $cancellationService->applyBulk($this->bulkCandidates($payload), $user->id, $payload['reason']);

        return to_route('academic.attendance.exceptions', collect($payload)->except('reason')->all())->with('status', $result['cancelled'].' sesi berhasil dibatalkan. '.$result['skipped'].' sesi dilewati karena sudah berubah atau memiliki data kehadiran.');
    }

    public function bulkSnapshot(Request $request, SessionParticipantSnapshotter $snapshotter): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);
        $allowed = app(AcademicAuthorizationService::class)->hasAcademicFullAuthority($user);
        abort_unless($allowed, 403, 'Hanya Waka Akademik atau Super Admin yang dapat membuat roster massal.');

        $payload = $request->validate([
            'session_ids' => ['required', 'array', 'min:1'],
            'session_ids.*' => ['uuid', 'exists:class_sessions,id'],
            'list_month' => ['nullable', 'date_format:Y-m'],
            'list_class_id' => ['nullable', 'uuid'],
            'list_sort' => ['nullable', 'in:oldest,newest'],
        ]);

        $sessions = ClassSession::query()
            ->whereIn('id', $payload['session_ids'])
            ->where('session_status', 'PLANNED')
            ->whereDoesntHave('studentParticipants')
            ->whereHas('academicClass.academicYear', fn ($query) => $query->where('year_code', 'not like', '%-PILOT'))
            ->get();
        $created = 0;
        foreach ($sessions as $session) {
            $created += $snapshotter->snapshot($session)->count();
        }

        return to_route('academic.attendance.exceptions', collect($payload)->except('session_ids')->all())->with('status', $sessions->count().' roster sesi diproses. '.$created.' peserta berhasil dibuat.');
    }

    private function bulkCandidates(array $filters)
    {
        if (empty($filters['from']) || empty($filters['to'])) {
            return collect();
        }

        $from = Carbon::parse($filters['from'])->startOfDay();
        $to = Carbon::parse($filters['to'])->endOfDay();
        if ($to->lessThan($from)) {
            return collect();
        }

        return ClassSession::query()
            ->with('academicClass')
            ->where('session_status', 'PLANNED')
            ->whereBetween('planned_start_at', [$from, $to])
            ->when($filters['class_id'] ?? null, fn ($query, $classId) => $query->where('class_id', $classId))
            ->whereHas('academicClass.academicYear', fn ($query) => $query->where('year_code', 'not like', '%-PILOT'))
            ->whereDoesntHave('studentParticipants.attendance')
            ->orderBy('planned_start_at')->get();
    }

    private function normalizeDateInput(mixed $value): mixed
    {
        if (! is_string($value) || $value === '') {
            return $value;
        }

        if (! str_contains($value, '/')) {
            return $value;
        }

        try {
            return Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
        } catch (\Throwable) {
            return $value;
        }
    }
}
