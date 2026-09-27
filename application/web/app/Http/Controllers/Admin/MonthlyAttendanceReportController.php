<?php

namespace App\Http\Controllers\Admin;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\ClassHomeroomAssignment;
use App\Domains\Academic\Models\MonthlyAttendanceSummary;
use App\Domains\Academic\Models\MonthlyStudentAttendanceSnapshot;
use App\Domains\Academic\Services\AcademicAuthorizationService;
use App\Models\User;
use App\Shared\Platform\Audit\Services\AuditLogger;
use App\Shared\Platform\Imports\Services\July2026OfficialClassMappingService;
use App\Shared\Platform\Reports\MonthlyAttendanceReportExportService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MonthlyAttendanceReportController
{
    public function index(Request $request, July2026OfficialClassMappingService $classMapping): View
    {
        $role = $this->authorizeViewer($request);
        $mapping = $classMapping->resolve();
        $report = MonthlyAttendanceSummary::query()->with(['academicClass', 'publishedBy'])->where('period', '2026-07')
            ->whereIn('class_id', $mapping['valid'] ? array_map(fn ($class): string => (string) $class->id, $mapping['classes']) : []);
        if ($role === 'WALI_KELAS') {
            $report->whereIn('class_id', $this->waliClassIds($request->user()));
        }

        return view('admin.academic.monthly-reports.index', [
            'report' => $report->orderBy('class_id')->get(),
            'viewerRole' => $role,
            'backRoute' => $request->routeIs('academic.*') ? 'academic.dashboard' : 'admin.academic.dashboard',
        ]);
    }

    public function publish(Request $request, AuditLogger $auditLogger, July2026OfficialClassMappingService $classMapping): RedirectResponse
    {
        $this->authorizeReviewer($request);
        $mapping = $classMapping->resolve();
        abort_unless($mapping['valid'], 422, 'Master kelas resmi Juli belum lengkap atau ambigu.');
        $expectedIds = collect($mapping['classes'])->map(fn ($class): string => (string) $class->id)->sort()->values()->all();
        $actor = $request->user();
        DB::transaction(function () use ($actor, $auditLogger, $expectedIds): void {
            $rows = MonthlyAttendanceSummary::query()->where('period', '2026-07')
                ->lockForUpdate()->get();
            $actualIds = $rows->pluck('class_id')->map(fn ($id): string => (string) $id)->sort()->values()->all();
            abort_unless($actualIds === $expectedIds, 422, 'Laporan Juli harus berisi tepat lima kelas resmi sebelum dipublikasikan.');
            $oldStatuses = $rows->pluck('status', 'id')->all();
            $rows->each(fn (MonthlyAttendanceSummary $row) => $row->update(['status' => 'PUBLISHED', 'published_by_user_id' => $actor->id, 'published_at' => now()]));
            $auditLogger->record(['actor_user_id' => $actor->id, 'action' => 'MONTHLY_ATTENDANCE_REPORT_PUBLISHED', 'entity_type' => MonthlyAttendanceSummary::class, 'entity_id' => '2026-07', 'old_values' => ['statuses' => $oldStatuses], 'new_values' => ['status' => 'PUBLISHED', 'period' => '2026-07', 'row_count' => $rows->count()], 'reason' => 'Waka Akademik menyetujui rekap bulanan Juli 2026', 'source_channel' => 'WEB']);
        });

        return redirect()->route('admin.academic.monthly-reports.index')->with('status', 'Rekap Juli 2026 sudah disetujui dan dipublikasikan.');
    }

    public function detail(Request $request, AcademicClass $class): View
    {
        $role = $this->authorizeViewer($request);
        abort_if(str_ends_with(strtoupper((string) $class->academicYear?->year_code), '-PILOT'), 404);
        $summary = MonthlyAttendanceSummary::query()->where('period', '2026-07')->where('class_id', $class->id)->firstOrFail();
        if ($role === 'WALI_KELAS' && ! in_array($class->id, $this->waliClassIds($request->user()), true)) {
            throw new AuthorizationException('Anda hanya dapat melihat detail kelas yang menjadi tanggung jawab Anda.');
        }

        $detailAvailable = Schema::hasTable('monthly_student_attendance_snapshots');
        $students = $detailAvailable
            ? MonthlyStudentAttendanceSnapshot::query()
                ->with('student')
                ->where('period', '2026-07')
                ->where('class_id', $class->id)
                ->orderBy('id')
                ->get()
                ->map(fn (MonthlyStudentAttendanceSnapshot $snapshot): object => (object) [
                    'source_record_id' => $snapshot->source_record_id,
                    'name_indonesia' => $snapshot->student?->full_name,
                    'name_arabic' => $snapshot->student?->arabic_name,
                    'present' => $snapshot->present,
                    'permission' => $snapshot->permission,
                    'sick' => $snapshot->sick,
                    'absent' => $snapshot->absent,
                    'eligible' => $snapshot->eligible,
                    'non_eligible' => $snapshot->non_eligible,
                    'attendance_rate' => $snapshot->attendance_rate,
                ])
            : collect();

        return view('admin.academic.monthly-reports.detail', compact('class', 'summary', 'students', 'role', 'detailAvailable'));
    }

    public function exportCsv(Request $request, MonthlyAttendanceReportExportService $exporter): StreamedResponse
    {
        $this->authorizeReviewer($request);

        return response()->streamDownload(fn () => print $exporter->csv(), 'rekap-kehadiran-juli-2026.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportPdf(Request $request, MonthlyAttendanceReportExportService $exporter): Response
    {
        $this->authorizeReviewer($request);

        return response($exporter->pdf(), 200, ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'attachment; filename="rekap-kehadiran-juli-2026.pdf"']);
    }

    public function detailCsv(Request $request, AcademicClass $class, MonthlyAttendanceReportExportService $exporter): StreamedResponse
    {
        $role = $this->authorizeViewer($request);
        abort_if(str_ends_with(strtoupper((string) $class->academicYear?->year_code), '-PILOT'), 404);
        if ($role === 'WALI_KELAS' && ! in_array($class->id, $this->waliClassIds($request->user()), true)) {
            throw new AuthorizationException('Anda hanya dapat melihat detail kelas yang menjadi tanggung jawab Anda.');
        }

        return response()->streamDownload(fn () => print $exporter->studentCsv($class, $this->monthlyStudents($class)), 'detail-rekap-'.$class->class_code.'-juli-2026.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function detailPdf(Request $request, AcademicClass $class, MonthlyAttendanceReportExportService $exporter): Response
    {
        $role = $this->authorizeViewer($request);
        abort_if(str_ends_with(strtoupper((string) $class->academicYear?->year_code), '-PILOT'), 404);
        if ($role === 'WALI_KELAS' && ! in_array($class->id, $this->waliClassIds($request->user()), true)) {
            throw new AuthorizationException('Anda hanya dapat melihat detail kelas yang menjadi tanggung jawab Anda.');
        }

        return response($exporter->studentPdf($class, $this->monthlyStudents($class)), 200, ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'attachment; filename="detail-rekap-'.$class->class_code.'-juli-2026.pdf"']);
    }

    private function authorizeReviewer(Request $request): void
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);
        $allowed = app(AcademicAuthorizationService::class)->hasAcademicFullAuthority($user, Carbon::now());
        if (! $allowed) {
            throw new AuthorizationException('Hanya Waka Akademik atau Super Admin yang dapat menyetujui laporan bulanan.');
        }
    }

    private function authorizeViewer(Request $request): string
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);
        foreach (['SUPER_ADMIN', 'WAKA_AKADEMIK', 'WALI_KELAS'] as $role) {
            if ($user->roleAssignments()->effectiveAt(Carbon::create(2026, 7, 31))->whereHas('role', fn ($query) => $query->where('code', $role))->exists()) {
                return $role;
            }
        }
        throw new AuthorizationException('Hanya Wali Kelas, Waka Akademik, atau Super Admin yang dapat melihat laporan bulanan.');
    }

    private function waliClassIds(User $user): array
    {
        $staffId = $user->staffLink()
            ->where(fn ($query) => $query->whereNull('effective_from')->orWhereDate('effective_from', '<=', '2026-07-31'))
            ->where(fn ($query) => $query->whereNull('effective_until')->orWhereDate('effective_until', '>', '2026-07-31'))
            ->value('staff_id');

        return ClassHomeroomAssignment::query()
            ->where('staff_id', $staffId)
            ->where('status', 'ACTIVE')
            ->whereDate('effective_from', '<=', '2026-07-31')
            ->where(fn ($query) => $query->whereNull('effective_until')->orWhereDate('effective_until', '>', '2026-07-01'))
            ->pluck('class_id')
            ->all();
    }

    private function monthlyStudents(AcademicClass $class)
    {
        abort_unless(Schema::hasTable('monthly_student_attendance_snapshots'), 404, 'Rincian per santri belum tersedia.');

        return MonthlyStudentAttendanceSnapshot::query()->with('student')->where('period', '2026-07')->where('class_id', $class->id)->orderBy('id')->get()->map(fn (MonthlyStudentAttendanceSnapshot $snapshot): object => (object) [
            'source_record_id' => $snapshot->source_record_id, 'name_indonesia' => $snapshot->student?->full_name, 'name_arabic' => $snapshot->student?->arabic_name,
            'present' => $snapshot->present, 'permission' => $snapshot->permission, 'sick' => $snapshot->sick, 'absent' => $snapshot->absent,
            'eligible' => $snapshot->eligible, 'non_eligible' => $snapshot->non_eligible, 'attendance_rate' => $snapshot->attendance_rate,
        ]);
    }
}
