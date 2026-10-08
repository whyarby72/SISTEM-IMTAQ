<?php

namespace App\Shared\Platform\Reports;

use App\Domains\Academic\Models\MonthlyAttendanceSummary;
use App\Domains\Academic\Models\MonthlyStudentAttendanceSnapshot;
use Illuminate\Support\Collection;

final class MonthlyAttendanceReportContext
{
    public const PERIOD = '2026-07';

    public function resolve(?Collection $summaries = null, ?Collection $snapshots = null): array
    {
        $summaries ??= MonthlyAttendanceSummary::query()->with('importBatch')->where('period', self::PERIOD)->get();
        $snapshots ??= MonthlyStudentAttendanceSnapshot::query()->with('importBatch')->where('period', self::PERIOD)->get();

        $batches = $summaries->concat($snapshots)
            ->map(fn ($row) => $row->importBatch)
            ->filter()
            ->unique('id')
            ->values();
        $batchCodes = $batches->pluck('batch_code')->filter()->unique()->values();
        $sourceSystems = $batches->pluck('source_system')->filter()->unique()->values();
        $conflict = $batchCodes->count() > 1 || $sourceSystems->count() > 1;
        $publicationStatus = $summaries->isNotEmpty() && $summaries->every(fn ($row): bool => $row->status === 'PUBLISHED') ? 'PUBLISHED' : 'DRAFT';

        return [
            'period' => self::PERIOD,
            'period_label' => 'Juli 2026',
            'source_type' => 'LEGACY_MONTHLY_SNAPSHOT',
            'source_label' => 'Snapshot historis legacy',
            'source_system' => $sourceSystems->count() === 1 ? $sourceSystems->first() : 'IMTAQ_LEGACY',
            'is_live' => false,
            'is_historical_snapshot' => true,
            'data_grain' => 'MONTHLY_AGGREGATE_SNAPSHOT',
            'publication_status' => $publicationStatus,
            'publication_label' => $publicationStatus === 'PUBLISHED' ? 'Status arsip: Sudah disahkan' : 'Status arsip: Menunggu persetujuan',
            'source_reference' => $conflict ? null : $batchCodes->first(),
            'source_reference_label' => $conflict ? 'Referensi sumber tidak tersedia' : ($batchCodes->first() ?? 'Referensi sumber tidak tersedia'),
            'snapshot_updated_at' => $summaries->concat($snapshots)->max('updated_at'),
            'provenance_conflict' => $conflict,
            'historical_notice' => 'Rekap ini adalah snapshot historis hasil impor untuk Juli 2026, bukan perhitungan langsung dari transaksi kehadiran saat ini.',
            'live_boundary_notice' => 'Perubahan pada dashboard atau kehadiran live tidak otomatis mengubah arsip ini.',
            'data_grain_label' => 'Agregat bulanan historis, bukan urutan sesi fisik.',
        ];
    }
}
