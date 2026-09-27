<?php

namespace App\Shared\Platform\Imports\Services;

use App\Models\User;
use App\Shared\Core\Models\Student;
use App\Shared\Core\Models\StudentStatusHistory;
use App\Shared\Platform\Audit\Services\AuditLogger;
use App\Shared\Platform\Imports\Models\ImportBatch;
use App\Shared\Platform\Imports\Models\ImportFile;
use App\Shared\Platform\Imports\Models\ImportLineage;
use App\Shared\Platform\Imports\Models\ImportMapping;
use App\Shared\Platform\Imports\Models\ImportRow;
use App\Shared\Platform\Imports\Models\ImportRowError;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ImportIntakeService
{
    private const IMPORTABLE_STUDENT_FIELDS = ['full_name', 'arabic_name', 'nickname', 'gender_code', 'birth_place', 'birth_date', 'entry_year'];

    private const GRANULARITIES = ['SESSION_LEVEL', 'DAILY_LEVEL', 'MONTHLY_SUMMARY', 'SEMESTER_FINAL', 'SCHEDULE_RULE', 'ROSTER_SNAPSHOT', 'DOCUMENT_ONLY', 'UNKNOWN'];

    private const TRANSITIONS = [
        'DRAFT' => ['RECEIVED', 'REJECTED'],
        'RECEIVED' => ['PROFILED', 'QUARANTINED', 'REJECTED'],
        'PROFILED' => ['STAGED', 'QUARANTINED', 'REJECTED'],
        'STAGED' => ['MAPPED', 'QUARANTINED', 'REJECTED'],
        'MAPPED' => ['VALIDATED', 'QUARANTINED', 'REJECTED'],
        'VALIDATED' => ['DRY_RUN', 'QUARANTINED', 'REJECTED'],
        'DRY_RUN' => ['RECONCILED', 'QUARANTINED', 'REJECTED'],
        'RECONCILED' => ['APPROVED', 'QUARANTINED', 'REJECTED'],
        'APPROVED' => ['IMPORTING'],
        'IMPORTING' => ['IMPORTED', 'FAILED'],
        'IMPORTED' => ['CLOSED'],
        'QUARANTINED' => ['PROFILED', 'REJECTED'],
    ];

    private const ROW_INTAKE_STATUSES = ['RECEIVED', 'PROFILED', 'STAGED'];

    private const ROW_OUTCOMES = ['IMPORTED', 'REJECTED', 'QUARANTINED', 'DUPLICATE', 'EXCLUDED'];

    public function createBatch(User $actor, array $attributes): ImportBatch
    {
        $validated = Validator::make($attributes, [
            'batch_code' => ['required', 'string', 'max:255'],
            'source_system' => ['required', 'string', 'max:255'],
            'source_period' => ['nullable', 'string', 'max:255'],
            'received_at' => ['nullable', 'date'],
        ])->validate();

        return DB::transaction(fn (): ImportBatch => ImportBatch::create($validated + ['created_by_user_id' => $actor->id])->fresh());
    }

    public function registerFile(ImportBatch $batch, array $attributes): ImportFile
    {
        if ($batch->status !== 'DRAFT') {
            throw new \InvalidArgumentException('Files can only be registered on DRAFT import batches.');
        }

        $validated = Validator::make($attributes, [
            'original_filename' => ['required', 'string', 'max:255'],
            'storage_path' => ['required', 'string', 'max:1024'],
            'sha256_checksum' => ['required', 'string', 'size:64', 'regex:/^[a-f0-9]{64}$/i'],
            'source_granularity' => ['required', 'in:'.implode(',', self::GRANULARITIES)],
            'file_size_bytes' => ['nullable', 'integer', 'min:0'],
            'received_at' => ['nullable', 'date'],
        ])->validate();

        $validated['sha256_checksum'] = strtolower($validated['sha256_checksum']);

        return DB::transaction(fn (): ImportFile => $batch->files()->create($validated));
    }

    public function transitionBatch(ImportBatch $batch, string $toStatus): ImportBatch
    {
        $batch = ImportBatch::query()->findOrFail($batch->id);
        if (! in_array($toStatus, self::TRANSITIONS[$batch->status] ?? [], true)) {
            throw new \InvalidArgumentException("Import batch cannot transition from {$batch->status} to {$toStatus}.");
        }

        return DB::transaction(function () use ($batch, $toStatus): ImportBatch {
            $locked = ImportBatch::query()->whereKey($batch->id)->lockForUpdate()->firstOrFail();
            if (! in_array($toStatus, self::TRANSITIONS[$locked->status] ?? [], true)) {
                throw new \InvalidArgumentException("Import batch cannot transition from {$locked->status} to {$toStatus}.");
            }
            if ($toStatus === 'CLOSED') {
                $this->assertAccountingReconciles($locked);
            }
            if ($toStatus === 'DRY_RUN') {
                $this->assertDryRunReady($locked);
            }
            $locked->update(['status' => $toStatus]);

            return $locked->fresh();
        });
    }

    public function finalizeReconciliation(ImportBatch $batch): ImportBatch
    {
        return DB::transaction(function () use ($batch): ImportBatch {
            $locked = ImportBatch::query()->whereKey($batch->id)->lockForUpdate()->firstOrFail();
            if ($locked->status !== 'DRY_RUN') {
                throw new \InvalidArgumentException('Only DRY_RUN import batches can be reconciled.');
            }

            $this->assertAccountingReconciles($locked);
            $this->assertIdentityReviewComplete($locked);
            $locked->update(['status' => 'RECONCILED']);

            return $locked->fresh();
        });
    }

    public function dryRunPreview(ImportBatch $batch): array
    {
        $batch = ImportBatch::query()->findOrFail($batch->id);
        if ($batch->status !== 'DRY_RUN') {
            throw new \InvalidArgumentException('Dry-run preview is only available for DRY_RUN import batches.');
        }

        $outcomeCounts = ImportRow::query()
            ->where('import_batch_id', $batch->id)
            ->selectRaw('row_status, count(*) as aggregate')
            ->groupBy('row_status')
            ->pluck('aggregate', 'row_status')
            ->map(fn ($count): int => (int) $count)
            ->all();
        $rowTotal = array_sum($outcomeCounts);
        $pendingCount = $outcomeCounts['PENDING'] ?? 0;
        $accountedTotal = $batch->imported_count
            + $batch->rejected_count
            + $batch->quarantined_count
            + $batch->duplicate_count
            + $batch->excluded_count;

        return [
            'batch_id' => $batch->id,
            'batch_code' => $batch->batch_code,
            'status' => $batch->status,
            'source_total' => $batch->source_total,
            'row_total' => $rowTotal,
            'pending_count' => $pendingCount,
            'outcome_counts' => $outcomeCounts,
            'error_count' => ImportRowError::query()->whereHas('row', fn ($query) => $query->where('import_batch_id', $batch->id))->count(),
            'canonical_target_count' => ImportRow::query()->where('import_batch_id', $batch->id)->whereNotNull('canonical_entity_id')->count(),
            'ready_for_reconciliation' => $pendingCount === 0 && $accountedTotal === $batch->source_total,
        ];
    }

    public function sourceInventory(ImportBatch $batch): array
    {
        $batch = ImportBatch::query()->with('files')->findOrFail($batch->id);
        $files = $batch->files->map(fn (ImportFile $file): array => [
            'file_id' => $file->id,
            'original_filename' => $file->original_filename,
            'sha256_checksum' => $file->sha256_checksum,
            'source_granularity' => $file->source_granularity,
        ])->values()->all();
        $granularities = array_values(array_unique(array_column($files, 'source_granularity')));

        return [
            'batch_id' => $batch->id,
            'batch_code' => $batch->batch_code,
            'file_count' => count($files),
            'granularities' => $granularities,
            'files' => $files,
            'requires_legacy_daily_storage' => in_array('DAILY_LEVEL', $granularities, true),
            'requires_legacy_monthly_storage' => in_array('MONTHLY_SUMMARY', $granularities, true),
            'session_expansion_allowed' => false,
        ];
    }

    public function canonicalImportPreflight(ImportBatch $batch): array
    {
        $batch = ImportBatch::query()->findOrFail($batch->id);
        $blockingReasons = [];
        if ($batch->status !== 'RECONCILED') {
            $blockingReasons[] = 'BATCH_NOT_RECONCILED';
        }

        $pendingRows = ImportRow::query()->where('import_batch_id', $batch->id)->where('row_status', 'PENDING')->count();
        $pendingMappings = ImportMapping::query()->where('import_batch_id', $batch->id)->where('mapping_status', 'PENDING')->count();
        $importedRowsWithoutTarget = ImportRow::query()
            ->where('import_batch_id', $batch->id)
            ->where('row_status', 'IMPORTED')
            ->whereNull('canonical_entity_id')
            ->count();
        $importedRowsWithoutLineage = ImportRow::query()
            ->where('import_batch_id', $batch->id)
            ->where('row_status', 'IMPORTED')
            ->whereDoesntHave('lineages')
            ->count();
        $accountedTotal = $batch->imported_count
            + $batch->rejected_count
            + $batch->quarantined_count
            + $batch->duplicate_count
            + $batch->excluded_count;

        if ($pendingRows > 0) {
            $blockingReasons[] = 'PENDING_ROWS';
        }
        if ($pendingMappings > 0) {
            $blockingReasons[] = 'PENDING_IDENTITY_MAPPINGS';
        }
        if ($importedRowsWithoutTarget > 0) {
            $blockingReasons[] = 'IMPORTED_ROWS_WITHOUT_CANONICAL_TARGET';
        }
        if ($importedRowsWithoutLineage > 0) {
            $blockingReasons[] = 'IMPORTED_ROWS_WITHOUT_LINEAGE';
        }
        if ($accountedTotal !== $batch->source_total) {
            $blockingReasons[] = 'ACCOUNTING_MISMATCH';
        }

        return [
            'batch_id' => $batch->id,
            'batch_code' => $batch->batch_code,
            'status' => $batch->status,
            'source_total' => $batch->source_total,
            'accounted_total' => $accountedTotal,
            'pending_rows' => $pendingRows,
            'pending_mappings' => $pendingMappings,
            'imported_rows_without_target' => $importedRowsWithoutTarget,
            'imported_rows_without_lineage' => $importedRowsWithoutLineage,
            'blocking_reasons' => $blockingReasons,
            'ready_for_canonical_import' => $blockingReasons === [],
        ];
    }

    public function executeMappedStudentUpdates(User $actor, ImportBatch $batch): ImportBatch
    {
        $preflight = $this->canonicalImportPreflight($batch);
        if (! $preflight['ready_for_canonical_import']) {
            throw new \InvalidArgumentException('Canonical import preflight has blocking reasons.');
        }

        return DB::transaction(function () use ($actor, $batch): ImportBatch {
            $lockedBatch = ImportBatch::query()->whereKey($batch->id)->lockForUpdate()->firstOrFail();
            if ($lockedBatch->status !== 'RECONCILED') {
                throw new \InvalidArgumentException('Only RECONCILED batches can start canonical import.');
            }

            $auditLogger = app(AuditLogger::class);
            $lockedBatch->update(['status' => 'IMPORTING']);
            $rows = ImportRow::query()
                ->where('import_batch_id', $lockedBatch->id)
                ->where('row_status', 'IMPORTED')
                ->where('canonical_entity_type', 'Student')
                ->lockForUpdate()
                ->get();
            foreach ($rows as $row) {
                $student = Student::query()->whereKey($row->canonical_entity_id)->lockForUpdate()->first();
                if ($student === null) {
                    throw new \InvalidArgumentException("Canonical Student target {$row->canonical_entity_id} was not found.");
                }

                $updates = array_intersect_key($row->raw_payload, array_flip(self::IMPORTABLE_STUDENT_FIELDS));
                $oldValues = $student->only(array_keys($updates));
                if ($updates !== []) {
                    $student->fill($updates);
                    $student->version_no = $student->version_no + 1;
                    $student->save();
                }
                $auditLogger->record([
                    'actor_user_id' => $actor->id,
                    'action' => 'IMPORT_STUDENT_MASTER_APPLIED',
                    'entity_type' => Student::class,
                    'entity_id' => (string) $student->id,
                    'version_before' => $updates === [] ? $student->version_no : $student->version_no - 1,
                    'version_after' => $student->version_no,
                    'old_values' => $oldValues,
                    'new_values' => $updates,
                    'reason' => 'Controlled initial master migration from reconciled import batch',
                    'source_channel' => 'IMPORT',
                    'technical_metadata' => ['import_batch_id' => $lockedBatch->id, 'import_row_id' => $row->id],
                ]);
            }
            $lockedBatch->update(['status' => 'IMPORTED']);

            return $lockedBatch->fresh();
        });
    }

    public function executeMappedStudentStatusHistory(User $actor, ImportBatch $batch): ImportBatch
    {
        $preflight = $this->canonicalImportPreflight($batch);
        if (! $preflight['ready_for_canonical_import']) {
            throw new \InvalidArgumentException('Canonical import preflight has blocking reasons.');
        }

        return DB::transaction(function () use ($actor, $batch): ImportBatch {
            $lockedBatch = ImportBatch::query()->whereKey($batch->id)->lockForUpdate()->firstOrFail();
            if ($lockedBatch->status !== 'RECONCILED') {
                throw new \InvalidArgumentException('Only RECONCILED batches can import status history.');
            }

            $auditLogger = app(AuditLogger::class);
            $lockedBatch->update(['status' => 'IMPORTING']);
            $rows = ImportRow::query()
                ->where('import_batch_id', $lockedBatch->id)
                ->where('row_status', 'IMPORTED')
                ->where('canonical_entity_type', 'Student')
                ->whereNotNull('canonical_entity_id')
                ->get();
            foreach ($rows as $row) {
                $payload = Validator::make($row->raw_payload, [
                    'status' => ['required', 'in:ACTIVE,GRADUATED,WITHDRAWN,TRANSFERRED_OUT,DISMISSED,DECEASED'],
                    'effective_from' => ['required', 'date'],
                    'effective_until' => ['nullable', 'date', 'after:effective_from'],
                    'decision_reference' => ['nullable', 'string', 'max:255'],
                    'reason' => ['nullable', 'string'],
                ])->validate();
                $student = Student::query()->whereKey($row->canonical_entity_id)->first();
                if ($student === null) {
                    throw new \InvalidArgumentException("Canonical Student target {$row->canonical_entity_id} was not found.");
                }

                $history = StudentStatusHistory::create([
                    'student_id' => $student->id,
                    'status' => $payload['status'],
                    'effective_from' => $payload['effective_from'],
                    'effective_until' => $payload['effective_until'] ?? null,
                    'decision_reference' => $payload['decision_reference'] ?? null,
                    'reason' => $payload['reason'] ?? null,
                    'actor_user_id' => $actor->id,
                ]);
                $auditLogger->record([
                    'actor_user_id' => $actor->id,
                    'action' => 'IMPORT_STUDENT_STATUS_HISTORY_APPLIED',
                    'entity_type' => StudentStatusHistory::class,
                    'entity_id' => (string) $history->id,
                    'new_values' => $payload,
                    'reason' => 'Controlled initial history migration from reconciled import batch',
                    'source_channel' => 'IMPORT',
                    'technical_metadata' => ['import_batch_id' => $lockedBatch->id, 'import_row_id' => $row->id, 'student_id' => $student->id],
                ]);
            }
            $lockedBatch->update(['status' => 'IMPORTED']);

            return $lockedBatch->fresh();
        });
    }

    public function identityReviewCandidates(ImportBatch $batch): array
    {
        $batch = ImportBatch::query()->findOrFail($batch->id);
        if ($batch->status !== 'DRY_RUN') {
            throw new \InvalidArgumentException('Identity review candidates are only available during DRY_RUN.');
        }

        return ImportRow::query()
            ->with('errors')
            ->where('import_batch_id', $batch->id)
            ->whereIn('row_status', ['PENDING', 'QUARANTINED'])
            ->whereNull('canonical_entity_id')
            ->orderBy('id')
            ->get()
            ->map(fn (ImportRow $row): array => [
                'row_id' => $row->id,
                'source_key' => $row->source_key,
                'row_status' => $row->row_status,
                'raw_payload' => $row->raw_payload,
                'errors' => $row->errors->map(fn (ImportRowError $error): array => [
                    'severity' => $error->severity,
                    'error_code' => $error->error_code,
                    'field_name' => $error->field_name,
                    'message' => $error->message,
                ])->values()->all(),
                'review_required' => true,
                'auto_match' => false,
            ])->values()->all();
    }

    public function reviewIdentityMapping(User $reviewer, ImportMapping $mapping, string $decision, array $attributes = []): ImportMapping
    {
        if (! in_array($decision, ['MATCHED', 'REJECTED', 'QUARANTINED'], true)) {
            throw new \InvalidArgumentException("Unsupported identity mapping decision: {$decision}.");
        }

        $validated = Validator::make($attributes, [
            'target_type' => ['nullable', 'string', 'max:255'],
            'target_id' => ['nullable', 'string', 'max:255'],
            'confidence' => ['nullable', 'numeric', 'between:0,1'],
            'evidence' => ['nullable', 'array'],
        ])->validate();

        return DB::transaction(function () use ($reviewer, $mapping, $decision, $validated): ImportMapping {
            $locked = ImportMapping::query()->whereKey($mapping->id)->lockForUpdate()->firstOrFail();
            $batch = ImportBatch::query()->whereKey($locked->import_batch_id)->firstOrFail();
            if ($batch->status !== 'DRY_RUN') {
                throw new \InvalidArgumentException('Identity mappings can only be reviewed during DRY_RUN.');
            }
            if ($locked->mapping_status !== 'PENDING') {
                throw new \InvalidArgumentException('Only pending identity mappings can be reviewed.');
            }

            $hasTarget = isset($validated['target_type'], $validated['target_id']);
            if ($decision === 'MATCHED' && ! $hasTarget) {
                throw new \InvalidArgumentException('Matched identity mappings require a target type and target id.');
            }
            if ($decision !== 'MATCHED' && $hasTarget) {
                throw new \InvalidArgumentException('Rejected or quarantined mappings cannot carry a canonical target.');
            }

            $locked->update([
                'target_type' => $validated['target_type'] ?? null,
                'target_id' => $validated['target_id'] ?? null,
                'mapping_status' => $decision,
                'confidence' => $validated['confidence'] ?? null,
                'reviewed_by_user_id' => $reviewer->id,
                'reviewed_at' => now(),
                'evidence' => $validated['evidence'] ?? null,
            ]);

            return $locked->fresh();
        });
    }

    public function propagateReviewedMapping(ImportMapping $mapping, ImportRow $row): ImportRow
    {
        return DB::transaction(function () use ($mapping, $row): ImportRow {
            $lockedMapping = ImportMapping::query()->whereKey($mapping->id)->lockForUpdate()->firstOrFail();
            $lockedRow = ImportRow::query()->whereKey($row->id)->lockForUpdate()->firstOrFail();
            $batch = ImportBatch::query()->whereKey($lockedMapping->import_batch_id)->firstOrFail();
            if ($batch->status !== 'DRY_RUN') {
                throw new \InvalidArgumentException('Reviewed mappings can only propagate during DRY_RUN.');
            }
            if ($lockedMapping->mapping_status !== 'MATCHED' || $lockedMapping->target_type === null || $lockedMapping->target_id === null) {
                throw new \InvalidArgumentException('Only reviewed MATCHED mappings can propagate to a row.');
            }
            if ($lockedRow->import_batch_id !== $lockedMapping->import_batch_id || $lockedRow->source_key !== $lockedMapping->source_key) {
                throw new \InvalidArgumentException('Mapping and row must belong to the same batch and source key.');
            }

            $lockedRow->update([
                'canonical_entity_type' => $lockedMapping->target_type,
                'canonical_entity_id' => $lockedMapping->target_id,
            ]);
            ImportLineage::firstOrCreate([
                'import_batch_id' => $lockedRow->import_batch_id,
                'import_file_id' => $lockedRow->import_file_id,
                'import_row_id' => $lockedRow->id,
                'target_type' => $lockedMapping->target_type,
                'target_id' => $lockedMapping->target_id,
            ], ['relationship_type' => 'IMPORTED_FROM']);

            return $lockedRow->fresh();
        });
    }

    public function recordRow(ImportBatch $batch, ImportFile $file, array $attributes): ImportRow
    {
        $batch = ImportBatch::query()->findOrFail($batch->id);
        $file = ImportFile::query()->whereKey($file->id)->where('import_batch_id', $batch->id)->first();
        if ($file === null) {
            throw new \InvalidArgumentException('Import file does not belong to the supplied batch.');
        }
        if (! in_array($batch->status, self::ROW_INTAKE_STATUSES, true)) {
            throw new \InvalidArgumentException("Rows cannot be ingested while batch is {$batch->status}.");
        }

        $validated = Validator::make($attributes, [
            'row_number' => ['required', 'integer', 'min:1'],
            'source_key' => ['nullable', 'string', 'max:255'],
            'raw_payload' => ['required', 'array'],
            'row_status' => ['nullable', 'in:PENDING,QUARANTINED,REJECTED,DUPLICATE,EXCLUDED'],
        ])->validate();
        $validated['row_status'] ??= 'PENDING';

        $existing = $file->rows()->where('row_number', $validated['row_number'])->first();
        if ($existing !== null) {
            return $this->reuseOrRejectDuplicate($existing, $validated);
        }

        try {
            return DB::transaction(fn (): ImportRow => $file->rows()->create($validated + ['import_batch_id' => $batch->id]));
        } catch (UniqueConstraintViolationException) {
            $existing = $file->rows()->where('row_number', $validated['row_number'])->firstOrFail();

            return $this->reuseOrRejectDuplicate($existing, $validated);
        }
    }

    private function reuseOrRejectDuplicate(ImportRow $existing, array $validated): ImportRow
    {
        if ($existing->raw_payload !== $validated['raw_payload'] || $existing->source_key !== ($validated['source_key'] ?? null)) {
            throw new \InvalidArgumentException('Source row already exists with different content.');
        }

        return $existing;
    }

    private function assertAccountingReconciles(ImportBatch $batch): void
    {
        $pendingRows = ImportRow::query()
            ->where('import_batch_id', $batch->id)
            ->where('row_status', 'PENDING')
            ->count();
        if ($pendingRows > 0) {
            throw new \InvalidArgumentException('Import batch still contains pending rows.');
        }

        $accountedTotal = $batch->imported_count
            + $batch->rejected_count
            + $batch->quarantined_count
            + $batch->duplicate_count
            + $batch->excluded_count;
        if ($accountedTotal !== $batch->source_total) {
            throw new \InvalidArgumentException('Import batch accounting does not reconcile to source_total.');
        }
    }

    private function assertDryRunReady(ImportBatch $batch): void
    {
        $pendingRows = ImportRow::query()
            ->where('import_batch_id', $batch->id)
            ->where('row_status', 'PENDING')
            ->count();
        if ($pendingRows > 0) {
            throw new \InvalidArgumentException('Import batch still contains pending rows before dry-run.');
        }

        $canonicalRows = ImportRow::query()
            ->where('import_batch_id', $batch->id)
            ->whereNotNull('canonical_entity_id')
            ->count();
        if ($canonicalRows > 0) {
            throw new \InvalidArgumentException('Dry-run cannot start after canonical targets were assigned.');
        }
    }

    private function assertIdentityReviewComplete(ImportBatch $batch): void
    {
        $pendingMappings = ImportMapping::query()
            ->where('import_batch_id', $batch->id)
            ->where('mapping_status', 'PENDING')
            ->count();
        if ($pendingMappings > 0) {
            throw new \InvalidArgumentException('Import batch still contains pending identity mappings.');
        }
    }

    public function accountRowOutcome(ImportRow $row, string $outcome, ?array $error = null): ImportRow
    {
        if (! in_array($outcome, self::ROW_OUTCOMES, true)) {
            throw new \InvalidArgumentException("Unsupported import row outcome: {$outcome}.");
        }
        if ($row->row_status !== 'PENDING') {
            throw new \InvalidArgumentException('Only pending import rows can receive an outcome.');
        }
        if (in_array($outcome, ['REJECTED', 'QUARANTINED'], true) && $error === null) {
            throw new \InvalidArgumentException('Rejected or quarantined rows require an error record.');
        }

        $validatedError = $error === null ? null : Validator::make($error, [
            'severity' => ['required', 'in:BLOCKING,WARNING'],
            'error_code' => ['required', 'string', 'max:255'],
            'field_name' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'details' => ['nullable', 'array'],
        ])->validate();

        return DB::transaction(function () use ($row, $outcome, $validatedError): ImportRow {
            $locked = ImportRow::query()->whereKey($row->id)->lockForUpdate()->firstOrFail();
            if ($locked->row_status !== 'PENDING') {
                throw new \InvalidArgumentException('Only pending import rows can receive an outcome.');
            }
            $locked->update(['row_status' => $outcome, 'accounted_at' => now()]);
            if ($validatedError !== null) {
                ImportRowError::create($validatedError + ['import_row_id' => $locked->id]);
            }
            $counter = strtolower($outcome).'_count';
            ImportBatch::query()->whereKey($locked->import_batch_id)->lockForUpdate()->increment($counter);

            return $locked->fresh();
        });
    }
}
