<?php

namespace App\Shared\Platform\Imports\Services;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\MonthlyAttendanceSummary;
use App\Models\User;
use App\Shared\Platform\Imports\Models\ImportLineage;
use Illuminate\Support\Facades\DB;

class July2026MonthlySummaryImportService
{
    public function __construct(private readonly July2026HistoricalSummaryValidator $validator, private readonly ImportIntakeService $intake, private readonly July2026OfficialClassMappingService $classMapping, private readonly MonthlyStudentAttendanceSnapshotMappingService $snapshotMapping) {}

    /** Read-only reconciliation preview. It never creates import, summary, or snapshot rows. */
    public function dryRun(string $path, ?string $expectedChecksum = null): array
    {
        $validated = $this->validator->validateFile($path, $expectedChecksum);
        $payload = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        $resolved = $this->classMapping->resolve();
        if (! $validated['valid'] || ! $resolved['valid']) {
            return ['ready' => false, 'source' => $validated, 'class_mapping' => $resolved, 'rows' => []];
        }
        $mapped = $this->snapshotMapping->dryRun('2026-07');
        $mappedByClass = collect($mapped['rows'])->groupBy('class_admin');
        $currentSnapshots = DB::table('monthly_student_attendance_snapshots')->where('period', '2026-07')->get()->groupBy('class_id');
        $currentSummaries = DB::table('monthly_attendance_summaries')->where('period', '2026-07')->get()->groupBy('class_id');
        $currentBySource = DB::table('monthly_student_attendance_snapshots')->where('period', '2026-07')->pluck('class_id', 'source_record_id');
        $rows = collect($payload['class_attendance_summary'])->map(function (array $source) use ($resolved, $mappedByClass, $currentSnapshots, $currentSummaries, $currentBySource): array {
            $class = $resolved['classes'][$source['class_admin']];
            $proposedSnapshots = $mappedByClass->get($source['class_admin'], collect());
            $matches = $proposedSnapshots->filter(fn (array $row): bool => (string) $currentBySource->get($row['source_record_id']) === (string) $class->id)->count();

            return [
                'source_class_admin' => $source['class_admin'],
                'target_class_id' => (string) $class->id,
                'target_class_code' => $class->class_code,
                'target_display_name' => $class->display_name,
                'target_academic_year' => $class->academicYear?->year_code,
                'source_roster_count' => $source['roster'],
                'mapped_student_count' => $proposedSnapshots->count(),
                'unmapped_student_count' => max(0, (int) $source['roster'] - $proposedSnapshots->count()),
                'ambiguous_student_count' => 0,
                'proposed_summary_row_count' => 1,
                'proposed_snapshot_row_count' => $proposedSnapshots->count(),
                'current_pilot_summary_row_count' => $currentSummaries->get((string) $this->pilotClassId($source['class_admin']))?->count() ?? 0,
                'current_pilot_snapshot_row_count' => $currentSnapshots->get((string) $this->pilotClassId($source['class_admin']))?->count() ?? 0,
                'source_identity_matches_current_count' => $matches,
            ];
        })->values()->all();
        $officialIds = array_map(fn ($class): string => (string) $class->id, $resolved['classes']);

        return ['ready' => $mapped['valid'] && count($officialIds) === 5 && count(array_unique($officialIds)) === 5, 'source' => $validated, 'class_mapping' => ['valid' => true, 'classes' => $resolved['classes'], 'errors' => []], 'student_mapping' => $mapped, 'rows' => $rows, 'dry_run_writes' => 0];
    }

    private function pilotClassId(string $sourceCode): ?string
    {
        return AcademicClass::query()->where('class_code', $sourceCode)->where('status', 'ACTIVE')->whereHas('academicYear', fn ($query) => $query->where('year_code', '2026/2027-PILOT'))->value('id');
    }

    public function import(User $actor, string $path, ?string $expectedChecksum = null): array
    {
        $validated = $this->validator->validateFile($path, $expectedChecksum);
        if (! $validated['valid']) {
            throw new \InvalidArgumentException('July 2026 source failed validation: '.implode(', ', $validated['blocking_errors']));
        }

        $payload = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        $checksum = $validated['source']['sha256_checksum'];

        $officialClasses = $this->classMapping->resolveOrFail();

        return DB::transaction(function () use ($actor, $path, $payload, $checksum, $officialClasses): array {
            $batch = $this->intake->createBatch($actor, ['batch_code' => 'IMTAQ-JUL-2026-MONTHLY', 'source_system' => 'IMTAQ_LEGACY', 'source_period' => '2026-07', 'received_at' => now()]);
            $file = $this->intake->registerFile($batch, ['original_filename' => basename($path), 'storage_path' => $path, 'sha256_checksum' => $checksum, 'source_granularity' => 'MONTHLY_SUMMARY', 'file_size_bytes' => filesize($path), 'received_at' => now()]);
            $batch = $this->intake->transitionBatch($batch, 'RECEIVED');
            $batch = $this->intake->transitionBatch($batch, 'PROFILED');
            $batch = $this->intake->transitionBatch($batch, 'STAGED');
            $batch->update(['source_total' => count($payload['class_attendance_summary'])]);
            $rows = [];
            foreach ($payload['class_attendance_summary'] as $number => $summary) {
                $row = $this->intake->recordRow($batch, $file, ['row_number' => $number + 1, 'source_key' => $summary['class_admin'], 'raw_payload' => $summary]);
                $this->intake->accountRowOutcome($row, 'IMPORTED');
                $class = $officialClasses[$summary['class_admin']];
                $report = MonthlyAttendanceSummary::create($summary + ['period' => '2026-07', 'class_id' => $class->id, 'matiq_report_class' => $payload['class_system']['mappings'][$number]['matiq_report_class'], 'non_eligible_reason' => $payload['rules']['non_eligible_reason_july'], 'import_batch_id' => $batch->id, 'source_checksum' => $checksum, 'status' => 'DRAFT']);
                ImportLineage::create(['import_batch_id' => $batch->id, 'import_file_id' => $file->id, 'import_row_id' => $row->id, 'target_type' => MonthlyAttendanceSummary::class, 'target_id' => $report->id, 'relationship_type' => 'IMPORTED_FROM']);
                $rows[] = $report;
            }
            $batch = $this->intake->transitionBatch($batch, 'MAPPED');
            $batch = $this->intake->transitionBatch($batch, 'VALIDATED');
            $batch = $this->intake->transitionBatch($batch, 'DRY_RUN');
            $batch = $this->intake->finalizeReconciliation($batch);

            return ['batch' => $batch, 'rows' => $rows];
        });
    }
}
