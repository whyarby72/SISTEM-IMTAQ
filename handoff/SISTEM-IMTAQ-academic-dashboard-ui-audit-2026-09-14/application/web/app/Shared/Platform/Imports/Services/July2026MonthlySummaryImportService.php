<?php

namespace App\Shared\Platform\Imports\Services;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\MonthlyAttendanceSummary;
use App\Models\User;
use App\Shared\Platform\Imports\Models\ImportLineage;
use Illuminate\Support\Facades\DB;

class July2026MonthlySummaryImportService
{
    public function __construct(private readonly July2026HistoricalSummaryValidator $validator, private readonly ImportIntakeService $intake) {}

    public function import(User $actor, string $path, ?string $expectedChecksum = null): array
    {
        $validated = $this->validator->validateFile($path, $expectedChecksum);
        if (! $validated['valid']) {
            throw new \InvalidArgumentException('July 2026 source failed validation: '.implode(', ', $validated['blocking_errors']));
        }

        $payload = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        $checksum = $validated['source']['sha256_checksum'];

        return DB::transaction(function () use ($actor, $path, $payload, $checksum): array {
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
                $class = AcademicClass::query()->where('class_code', $summary['class_admin'])->where('status', 'ACTIVE')->firstOrFail();
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
