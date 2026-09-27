<?php

namespace App\Shared\Platform\Imports\Services;

use App\Domains\Academic\Models\MonthlyStudentAttendanceSnapshot;
use App\Models\User;
use App\Shared\Platform\Imports\Models\ImportBatch;
use App\Shared\Platform\Imports\Models\ImportLineage;
use App\Shared\Platform\Imports\Models\ImportMapping;
use Illuminate\Support\Facades\DB;

class MonthlyStudentAttendanceSnapshotImportService
{
    public function __construct(
        private readonly MonthlyStudentAttendanceSnapshotMappingService $mapping,
        private readonly ImportIntakeService $intake,
    ) {}

    public function import(User $actor, string $period = '2026-07'): array
    {
        $batchCode = 'IMTAQ-'.str_replace('-', '', $period).'-STUDENT-SNAPSHOT';
        $existing = ImportBatch::query()->where('batch_code', $batchCode)->first();
        if ($existing !== null) {
            return ['batch' => $existing->fresh(), 'snapshot_count' => MonthlyStudentAttendanceSnapshot::query()->where('import_batch_id', $existing->id)->count(), 'already_imported' => true];
        }

        $dryRun = $this->mapping->dryRun($period);
        if (! $dryRun['valid'] || $dryRun['row_count'] !== 84 || $dryRun['mapped_count'] !== 84) {
            throw new \InvalidArgumentException('Snapshot import requires 84/84 valid canonical mappings.');
        }

        $sourceFile = DB::table('import_files as file')
            ->join('import_batches as batch', 'batch.id', '=', 'file.import_batch_id')
            ->where('batch.source_period', $period)
            ->where('file.source_granularity', 'MONTHLY_SUMMARY')
            ->orderByDesc('file.created_at')
            ->select('file.*')
            ->first();
        if ($sourceFile === null) {
            throw new \InvalidArgumentException('A registered monthly source file is required for snapshot lineage.');
        }

        $sourceRows = DB::table('staging_imtaq_attendance_july_2026 as attendance')
            ->join('staging_imtaq_students_july_2026 as roster', 'roster.source_record_id', '=', 'attendance.source_record_id')
            ->where('attendance.period', $period)
            ->orderBy('attendance.source_record_id')
            ->get()
            ->keyBy('source_record_id');
        $mappedRows = collect($dryRun['rows'])->keyBy('source_record_id');

        return DB::transaction(function () use ($actor, $batchCode, $period, $sourceFile, $sourceRows, $mappedRows): array {
            $batch = $this->intake->createBatch($actor, [
                'batch_code' => $batchCode,
                'source_system' => 'IMTAQ_LEGACY',
                'source_period' => $period,
                'received_at' => now(),
            ]);
            $file = $this->intake->registerFile($batch, [
                'original_filename' => $sourceFile->original_filename,
                'storage_path' => $sourceFile->storage_path,
                'sha256_checksum' => $sourceFile->sha256_checksum,
                'source_granularity' => 'MONTHLY_SUMMARY',
                'file_size_bytes' => $sourceFile->file_size_bytes,
                'received_at' => now(),
            ]);
            $batch = $this->intake->transitionBatch($batch, 'RECEIVED');
            $batch = $this->intake->transitionBatch($batch, 'PROFILED');
            $batch = $this->intake->transitionBatch($batch, 'STAGED');
            $batch->update(['source_total' => $sourceRows->count()]);

            foreach ($sourceRows as $number => $sourceRow) {
                $row = $this->intake->recordRow($batch, $file, [
                    'row_number' => $sourceRows->keys()->search($number) + 1,
                    'source_key' => $sourceRow->source_record_id,
                    'raw_payload' => (array) $sourceRow,
                ]);
                $this->intake->accountRowOutcome($row, 'IMPORTED');
                $mapped = $mappedRows->get($sourceRow->source_record_id);
                ImportMapping::create([
                    'import_batch_id' => $batch->id,
                    'source_type' => 'STUDENT_SOURCE_RECORD',
                    'source_key' => $sourceRow->source_record_id,
                    'target_type' => 'Student',
                    'target_id' => $mapped['student_id'],
                    'mapping_status' => 'APPROVED',
                    'confidence' => 1,
                    'reviewed_by_user_id' => $actor->id,
                    'reviewed_at' => now(),
                    'evidence' => ['method' => 'EXACT_NAME_AND_CLASS_ENROLLMENT', 'period' => $period],
                ]);
            }

            $batch = $this->intake->transitionBatch($batch, 'MAPPED');
            $batch = $this->intake->transitionBatch($batch, 'VALIDATED');
            $batch = $this->intake->transitionBatch($batch, 'DRY_RUN');
            $batch = $this->intake->finalizeReconciliation($batch);
            $batch = $this->intake->transitionBatch($batch, 'APPROVED');
            $batch = $this->intake->transitionBatch($batch, 'IMPORTING');

            foreach ($sourceRows as $sourceRow) {
                $mapped = $mappedRows->get($sourceRow->source_record_id);
                $snapshot = MonthlyStudentAttendanceSnapshot::create([
                    'period' => $period,
                    'student_id' => $mapped['student_id'],
                    'class_id' => $mapped['class_id'],
                    'class_admin' => $sourceRow->class_admin,
                    'attendance_group' => $sourceRow->attendance_group,
                    'matiq_report_class' => $sourceRow->matiq_report_class,
                    'source_record_id' => $sourceRow->source_record_id,
                    'source_checksum' => $file->sha256_checksum,
                    'import_batch_id' => $batch->id,
                    'import_file_id' => $file->id,
                    'import_row_id' => $batch->rows()->where('source_key', $sourceRow->source_record_id)->value('id'),
                    'scheduled_attendance_units_working' => $sourceRow->scheduled_attendance_units_working,
                    'present' => $sourceRow->present,
                    'permission' => $sourceRow->permission,
                    'sick' => $sourceRow->sick,
                    'absent' => $sourceRow->absent,
                    'eligible' => $sourceRow->eligible,
                    'non_eligible' => $sourceRow->non_eligible,
                    'non_eligible_reason' => $sourceRow->non_eligible_reason,
                    'attendance_rate' => $sourceRow->attendance_rate,
                    'source_absent_term' => $sourceRow->source_absent_term,
                    'raw_payload' => (array) $sourceRow,
                ]);
                $batch->rows()->where('source_key', $sourceRow->source_record_id)->update(['canonical_entity_type' => MonthlyStudentAttendanceSnapshot::class, 'canonical_entity_id' => $snapshot->id]);
                ImportLineage::create(['import_batch_id' => $batch->id, 'import_file_id' => $file->id, 'import_row_id' => $snapshot->import_row_id, 'target_type' => MonthlyStudentAttendanceSnapshot::class, 'target_id' => $snapshot->id, 'relationship_type' => 'IMPORTED_FROM']);
            }

            $batch = $this->intake->transitionBatch($batch, 'IMPORTED');

            return ['batch' => $batch, 'snapshot_count' => MonthlyStudentAttendanceSnapshot::query()->where('import_batch_id', $batch->id)->count(), 'already_imported' => false];
        });
    }
}
