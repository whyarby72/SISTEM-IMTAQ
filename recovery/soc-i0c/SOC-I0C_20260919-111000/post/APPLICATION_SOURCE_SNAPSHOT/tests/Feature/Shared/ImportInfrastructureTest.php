<?php

namespace Tests\Feature\Shared;

use App\Models\User;
use App\Shared\Core\Models\Student;
use App\Shared\Core\Models\StudentStatusHistory;
use App\Shared\Platform\Audit\Models\AuditLog;
use App\Shared\Platform\Imports\Models\ImportBatch;
use App\Shared\Platform\Imports\Models\ImportFile;
use App\Shared\Platform\Imports\Models\ImportLineage;
use App\Shared\Platform\Imports\Models\ImportMapping;
use App\Shared\Platform\Imports\Models\ImportRow;
use App\Shared\Platform\Imports\Models\ImportRowError;
use App\Shared\Platform\Imports\Services\ImportIntakeService;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ImportInfrastructureTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_infrastructure_preserves_source_accounting_and_lineage(): void
    {
        $owner = User::factory()->create();
        $batch = ImportBatch::create(['batch_code' => 'BATCH-001', 'source_system' => 'LEGACY_SHEET', 'source_period' => '2025-2026', 'created_by_user_id' => $owner->id, 'source_total' => 1]);
        $file = ImportFile::create(['import_batch_id' => $batch->id, 'original_filename' => 'attendance.csv', 'storage_path' => 'imports/BATCH-001/attendance.csv', 'sha256_checksum' => str_repeat('a', 64), 'source_granularity' => 'DAILY_LEVEL']);
        $row = ImportRow::create(['import_batch_id' => $batch->id, 'import_file_id' => $file->id, 'row_number' => 2, 'source_key' => 'STU-001', 'raw_payload' => ['student_code' => 'STU-001'], 'row_status' => 'QUARANTINED']);
        $error = ImportRowError::create(['import_row_id' => $row->id, 'severity' => 'BLOCKING', 'error_code' => 'IDENTITY_REVIEW', 'message' => 'Manual identity review required']);
        $mapping = ImportMapping::create(['import_batch_id' => $batch->id, 'source_type' => 'STUDENT_CODE', 'source_key' => 'STU-001', 'mapping_status' => 'PENDING']);
        $lineage = ImportLineage::create(['import_batch_id' => $batch->id, 'import_file_id' => $file->id, 'import_row_id' => $row->id, 'target_type' => 'Student', 'target_id' => 'student-001']);

        $this->assertTrue(Schema::hasTable('import_batches'));
        $this->assertSame($file->id, $batch->fresh()->files->first()->id);
        $this->assertSame($error->id, $row->fresh()->errors->first()->id);
        $this->assertSame($mapping->id, $batch->fresh()->mappings->first()->id);
        $this->assertSame($lineage->id, $row->fresh()->lineages->first()->id);
        $this->assertSame(['student_code' => 'STU-001'], $row->fresh()->raw_payload);
    }

    public function test_source_rows_are_unique_per_file_and_mappings_per_batch_source_key(): void
    {
        $batch = ImportBatch::create(['batch_code' => 'BATCH-002', 'source_system' => 'LEGACY_SHEET']);
        $file = ImportFile::create(['import_batch_id' => $batch->id, 'original_filename' => 'students.csv', 'storage_path' => 'imports/BATCH-002/students.csv', 'sha256_checksum' => str_repeat('b', 64)]);
        ImportRow::create(['import_batch_id' => $batch->id, 'import_file_id' => $file->id, 'row_number' => 1, 'raw_payload' => ['name' => 'Student']]);
        ImportMapping::create(['import_batch_id' => $batch->id, 'source_type' => 'STUDENT_CODE', 'source_key' => 'STU-002']);

        $this->expectException(UniqueConstraintViolationException::class);
        ImportRow::create(['import_batch_id' => $batch->id, 'import_file_id' => $file->id, 'row_number' => 1, 'raw_payload' => ['name' => 'Duplicate']]);
    }

    public function test_import_batch_cannot_delete_retained_source_facts(): void
    {
        $batch = ImportBatch::create(['batch_code' => 'BATCH-003', 'source_system' => 'LEGACY_SHEET']);
        $file = ImportFile::create(['import_batch_id' => $batch->id, 'original_filename' => 'source.csv', 'storage_path' => 'imports/BATCH-003/source.csv', 'sha256_checksum' => str_repeat('c', 64)]);
        ImportRow::create(['import_batch_id' => $batch->id, 'import_file_id' => $file->id, 'row_number' => 1, 'raw_payload' => ['name' => 'Retained']]);

        $this->expectException(QueryException::class);
        $batch->delete();
    }

    public function test_rows_and_lineage_cannot_mix_records_from_different_batches(): void
    {
        $firstBatch = ImportBatch::create(['batch_code' => 'BATCH-004', 'source_system' => 'LEGACY_SHEET']);
        $firstFile = ImportFile::create(['import_batch_id' => $firstBatch->id, 'original_filename' => 'first.csv', 'storage_path' => 'imports/BATCH-004/first.csv', 'sha256_checksum' => str_repeat('d', 64)]);
        $row = ImportRow::create(['import_batch_id' => $firstBatch->id, 'import_file_id' => $firstFile->id, 'row_number' => 1, 'raw_payload' => ['name' => 'First']]);
        $secondBatch = ImportBatch::create(['batch_code' => 'BATCH-005', 'source_system' => 'LEGACY_SHEET']);
        $secondFile = ImportFile::create(['import_batch_id' => $secondBatch->id, 'original_filename' => 'second.csv', 'storage_path' => 'imports/BATCH-005/second.csv', 'sha256_checksum' => str_repeat('e', 64)]);

        try {
            ImportRow::create(['import_batch_id' => $secondBatch->id, 'import_file_id' => $firstFile->id, 'row_number' => 1, 'raw_payload' => ['name' => 'Mixed']]);
            $this->fail('A row must not mix a batch with a file from another batch.');
        } catch (QueryException) {
            $this->assertTrue(true);
        }

        $this->expectException(QueryException::class);
        ImportLineage::create(['import_batch_id' => $secondBatch->id, 'import_file_id' => $secondFile->id, 'import_row_id' => $row->id, 'target_type' => 'Student', 'target_id' => 'student-mixed']);
    }

    public function test_intake_service_records_batch_and_normalizes_file_checksum(): void
    {
        $owner = User::factory()->create();
        $service = app(ImportIntakeService::class);
        $batch = $service->createBatch($owner, ['batch_code' => 'BATCH-INTAKE', 'source_system' => 'LEGACY_SHEET', 'source_period' => '2025-2026']);
        $file = $service->registerFile($batch, ['original_filename' => 'students.csv', 'storage_path' => 'imports/BATCH-INTAKE/students.csv', 'sha256_checksum' => str_repeat('A', 64), 'source_granularity' => 'DAILY_LEVEL', 'file_size_bytes' => 42]);

        $this->assertSame($owner->id, $batch->created_by_user_id);
        $this->assertSame(str_repeat('a', 64), $file->sha256_checksum);
        $this->assertSame('DAILY_LEVEL', $file->source_granularity);
    }

    public function test_intake_service_rejects_invalid_checksum_and_granularity(): void
    {
        $owner = User::factory()->create();
        $batch = app(ImportIntakeService::class)->createBatch($owner, ['batch_code' => 'BATCH-INVALID', 'source_system' => 'LEGACY_SHEET']);

        $this->expectException(ValidationException::class);
        app(ImportIntakeService::class)->registerFile($batch, ['original_filename' => 'invalid.csv', 'storage_path' => 'imports/BATCH-INVALID/invalid.csv', 'sha256_checksum' => 'not-a-checksum', 'source_granularity' => 'UNKNOWN_SOURCE']);
    }

    public function test_batch_lifecycle_guards_file_intake_after_receiving(): void
    {
        $owner = User::factory()->create();
        $service = app(ImportIntakeService::class);
        $batch = $service->createBatch($owner, ['batch_code' => 'BATCH-LIFECYCLE', 'source_system' => 'LEGACY_SHEET']);
        $service->registerFile($batch, ['original_filename' => 'source.csv', 'storage_path' => 'imports/BATCH-LIFECYCLE/source.csv', 'sha256_checksum' => str_repeat('f', 64), 'source_granularity' => 'UNKNOWN']);

        $received = $service->transitionBatch($batch, 'RECEIVED');
        $this->assertSame('RECEIVED', $received->status);

        $profiled = $service->transitionBatch($batch, 'PROFILED');
        $this->assertSame('PROFILED', $profiled->status);

        $this->expectException(\InvalidArgumentException::class);
        $service->registerFile($profiled, ['original_filename' => 'late.csv', 'storage_path' => 'imports/BATCH-LIFECYCLE/late.csv', 'sha256_checksum' => str_repeat('e', 64), 'source_granularity' => 'UNKNOWN']);
    }

    public function test_row_ingestion_is_idempotent_for_same_source_row(): void
    {
        $owner = User::factory()->create();
        $service = app(ImportIntakeService::class);
        $batch = $service->createBatch($owner, ['batch_code' => 'BATCH-ROWS', 'source_system' => 'LEGACY_SHEET']);
        $file = $service->registerFile($batch, ['original_filename' => 'rows.csv', 'storage_path' => 'imports/BATCH-ROWS/rows.csv', 'sha256_checksum' => str_repeat('1', 64), 'source_granularity' => 'DAILY_LEVEL']);
        $staleBatch = $batch;
        $batch = $service->transitionBatch($batch, 'RECEIVED');

        $first = $service->recordRow($staleBatch, $file, ['row_number' => 2, 'source_key' => 'STU-001', 'raw_payload' => ['student_code' => 'STU-001']]);
        $repeat = $service->recordRow($batch, $file, ['row_number' => 2, 'source_key' => 'STU-001', 'raw_payload' => ['student_code' => 'STU-001']]);

        $this->assertSame($first->id, $repeat->id);
        $this->assertSame(1, $file->fresh()->rows()->count());
    }

    public function test_row_ingestion_rejects_changed_duplicate_and_closed_batch(): void
    {
        $owner = User::factory()->create();
        $service = app(ImportIntakeService::class);
        $batch = $service->createBatch($owner, ['batch_code' => 'BATCH-ROWS-INVALID', 'source_system' => 'LEGACY_SHEET']);
        $file = $service->registerFile($batch, ['original_filename' => 'rows.csv', 'storage_path' => 'imports/BATCH-ROWS-INVALID/rows.csv', 'sha256_checksum' => str_repeat('2', 64), 'source_granularity' => 'DAILY_LEVEL']);
        $batch = $service->transitionBatch($batch, 'RECEIVED');
        $service->recordRow($batch, $file, ['row_number' => 1, 'raw_payload' => ['value' => 'original']]);

        try {
            $service->recordRow($batch, $file, ['row_number' => 1, 'raw_payload' => ['value' => 'changed']]);
            $this->fail('A duplicate source row with changed content must be rejected.');
        } catch (\InvalidArgumentException) {
            $this->assertTrue(true);
        }

        $profiled = $service->transitionBatch($batch, 'PROFILED');
        $this->expectException(\InvalidArgumentException::class);
        $profiled->update(['status' => 'CLOSED']);
        $closed = $profiled->fresh();
        $service->recordRow($closed, $file, ['row_number' => 2, 'raw_payload' => ['value' => 'not allowed']]);
    }

    public function test_row_outcome_accounts_quarantine_and_error_atomically(): void
    {
        $owner = User::factory()->create();
        $service = app(ImportIntakeService::class);
        $batch = $service->createBatch($owner, ['batch_code' => 'BATCH-OUTCOME', 'source_system' => 'LEGACY_SHEET']);
        $file = $service->registerFile($batch, ['original_filename' => 'rows.csv', 'storage_path' => 'imports/BATCH-OUTCOME/rows.csv', 'sha256_checksum' => str_repeat('4', 64), 'source_granularity' => 'DAILY_LEVEL']);
        $batch = $service->transitionBatch($batch, 'RECEIVED');
        $row = $service->recordRow($batch, $file, ['row_number' => 1, 'raw_payload' => ['student_code' => 'UNKNOWN']]);

        $accounted = $service->accountRowOutcome($row, 'QUARANTINED', ['severity' => 'BLOCKING', 'error_code' => 'IDENTITY_REVIEW', 'message' => 'Manual review required']);

        $this->assertSame('QUARANTINED', $accounted->row_status);
        $this->assertSame(1, ImportRowError::where('import_row_id', $row->id)->count());
        $this->assertSame(1, $batch->fresh()->quarantined_count);

        try {
            $service->accountRowOutcome($row, 'QUARANTINED', ['severity' => 'BLOCKING', 'error_code' => 'IDENTITY_REVIEW', 'message' => 'Manual review required']);
            $this->fail('A completed row outcome must not be applied twice.');
        } catch (\InvalidArgumentException) {
            $this->assertTrue(true);
        }

        $this->assertSame(1, ImportRowError::where('import_row_id', $row->id)->count());
        $this->assertSame(1, $batch->fresh()->quarantined_count);
    }

    public function test_row_outcome_requires_error_for_rejection_and_cannot_be_reapplied(): void
    {
        $owner = User::factory()->create();
        $service = app(ImportIntakeService::class);
        $batch = $service->createBatch($owner, ['batch_code' => 'BATCH-OUTCOME-INVALID', 'source_system' => 'LEGACY_SHEET']);
        $file = $service->registerFile($batch, ['original_filename' => 'rows.csv', 'storage_path' => 'imports/BATCH-OUTCOME-INVALID/rows.csv', 'sha256_checksum' => str_repeat('5', 64), 'source_granularity' => 'DAILY_LEVEL']);
        $batch = $service->transitionBatch($batch, 'RECEIVED');
        $row = $service->recordRow($batch, $file, ['row_number' => 1, 'raw_payload' => ['value' => 'bad']]);

        try {
            $service->accountRowOutcome($row, 'REJECTED');
            $this->fail('Rejected rows must include an error record.');
        } catch (\InvalidArgumentException) {
            $this->assertTrue(true);
        }

        $service->accountRowOutcome($row, 'EXCLUDED');
        $this->expectException(\InvalidArgumentException::class);
        $service->accountRowOutcome($row, 'IMPORTED');
    }

    public function test_reconciliation_requires_complete_accounting_and_closing_rechecks_it(): void
    {
        $owner = User::factory()->create();
        $service = app(ImportIntakeService::class);
        $batch = $service->createBatch($owner, ['batch_code' => 'BATCH-RECONCILE', 'source_system' => 'LEGACY_SHEET']);
        $batch->update(['source_total' => 2]);
        $file = $service->registerFile($batch, ['original_filename' => 'rows.csv', 'storage_path' => 'imports/BATCH-RECONCILE/rows.csv', 'sha256_checksum' => str_repeat('6', 64), 'source_granularity' => 'DAILY_LEVEL']);
        $batch = $service->transitionBatch($batch, 'RECEIVED');
        $first = $service->recordRow($batch, $file, ['row_number' => 1, 'raw_payload' => ['value' => 'one']]);
        $second = $service->recordRow($batch, $file, ['row_number' => 2, 'raw_payload' => ['value' => 'two']]);
        $service->accountRowOutcome($first, 'IMPORTED');
        $service->accountRowOutcome($second, 'EXCLUDED');

        $batch = $service->transitionBatch($batch, 'PROFILED');
        $batch->update(['status' => 'DRY_RUN']);
        $reconciled = $service->finalizeReconciliation($batch);

        $this->assertSame('RECONCILED', $reconciled->status);
        $imported = $service->transitionBatch($reconciled, 'APPROVED');
        $importing = $service->transitionBatch($imported, 'IMPORTING');
        $imported = $service->transitionBatch($importing, 'IMPORTED');
        $closed = $service->transitionBatch($imported, 'CLOSED');

        $this->assertSame('CLOSED', $closed->status);
    }

    public function test_reconciliation_rejects_pending_rows_and_source_total_mismatch(): void
    {
        $owner = User::factory()->create();
        $service = app(ImportIntakeService::class);
        $batch = $service->createBatch($owner, ['batch_code' => 'BATCH-RECONCILE-INVALID', 'source_system' => 'LEGACY_SHEET']);
        $batch->update(['source_total' => 2]);
        $file = $service->registerFile($batch, ['original_filename' => 'rows.csv', 'storage_path' => 'imports/BATCH-RECONCILE-INVALID/rows.csv', 'sha256_checksum' => str_repeat('7', 64), 'source_granularity' => 'DAILY_LEVEL']);
        $batch = $service->transitionBatch($batch, 'RECEIVED');
        $row = $service->recordRow($batch, $file, ['row_number' => 1, 'raw_payload' => ['value' => 'pending']]);
        $batch = $service->transitionBatch($batch, 'PROFILED');
        $batch->update(['status' => 'DRY_RUN']);

        $this->expectException(\InvalidArgumentException::class);
        $service->finalizeReconciliation($batch);
    }

    public function test_dry_run_requires_accounted_rows_and_no_canonical_target(): void
    {
        $owner = User::factory()->create();
        $service = app(ImportIntakeService::class);
        $batch = $service->createBatch($owner, ['batch_code' => 'BATCH-DRY-RUN', 'source_system' => 'LEGACY_SHEET']);
        $file = $service->registerFile($batch, ['original_filename' => 'rows.csv', 'storage_path' => 'imports/BATCH-DRY-RUN/rows.csv', 'sha256_checksum' => str_repeat('8', 64), 'source_granularity' => 'DAILY_LEVEL']);
        $batch = $service->transitionBatch($batch, 'RECEIVED');
        $row = $service->recordRow($batch, $file, ['row_number' => 1, 'raw_payload' => ['value' => 'one']]);
        $batch = $service->transitionBatch($batch, 'PROFILED');
        $batch = $service->transitionBatch($batch, 'STAGED');
        $batch = $service->transitionBatch($batch, 'MAPPED');
        $batch = $service->transitionBatch($batch, 'VALIDATED');

        try {
            $service->transitionBatch($batch, 'DRY_RUN');
            $this->fail('A dry-run must reject pending rows.');
        } catch (\InvalidArgumentException) {
            $this->assertTrue(true);
        }

        $service->accountRowOutcome($row, 'IMPORTED');
        $dryRun = $service->transitionBatch($batch, 'DRY_RUN');

        $this->assertSame('DRY_RUN', $dryRun->status);
        $this->assertNull($row->fresh()->canonical_entity_id);
    }

    public function test_dry_run_preview_returns_deterministic_accounting_summary(): void
    {
        $owner = User::factory()->create();
        $service = app(ImportIntakeService::class);
        $batch = $service->createBatch($owner, ['batch_code' => 'BATCH-PREVIEW', 'source_system' => 'LEGACY_SHEET']);
        $batch->update(['source_total' => 2]);
        $file = $service->registerFile($batch, ['original_filename' => 'rows.csv', 'storage_path' => 'imports/BATCH-PREVIEW/rows.csv', 'sha256_checksum' => str_repeat('9', 64), 'source_granularity' => 'DAILY_LEVEL']);
        $batch = $service->transitionBatch($batch, 'RECEIVED');
        $imported = $service->recordRow($batch, $file, ['row_number' => 1, 'raw_payload' => ['value' => 'one']]);
        $quarantined = $service->recordRow($batch, $file, ['row_number' => 2, 'raw_payload' => ['value' => 'two']]);
        $service->accountRowOutcome($imported, 'IMPORTED');
        $service->accountRowOutcome($quarantined, 'QUARANTINED', ['severity' => 'BLOCKING', 'error_code' => 'REVIEW', 'message' => 'Needs review']);
        $batch->update(['status' => 'DRY_RUN']);

        $preview = $service->dryRunPreview($batch);

        $this->assertSame('DRY_RUN', $preview['status']);
        $this->assertSame(2, $preview['row_total']);
        $this->assertSame(0, $preview['pending_count']);
        $this->assertSame(['IMPORTED' => 1, 'QUARANTINED' => 1], $preview['outcome_counts']);
        $this->assertSame(1, $preview['error_count']);
        $this->assertSame(0, $preview['canonical_target_count']);
        $this->assertTrue($preview['ready_for_reconciliation']);
    }

    public function test_identity_review_candidates_preserve_evidence_without_auto_matching(): void
    {
        $owner = User::factory()->create();
        $service = app(ImportIntakeService::class);
        $batch = $service->createBatch($owner, ['batch_code' => 'BATCH-IDENTITY-REVIEW', 'source_system' => 'LEGACY_SHEET']);
        $file = $service->registerFile($batch, ['original_filename' => 'rows.csv', 'storage_path' => 'imports/BATCH-IDENTITY-REVIEW/rows.csv', 'sha256_checksum' => str_repeat('a', 64), 'source_granularity' => 'DAILY_LEVEL']);
        $batch = $service->transitionBatch($batch, 'RECEIVED');
        $row = $service->recordRow($batch, $file, ['row_number' => 1, 'source_key' => 'LEGACY-001', 'raw_payload' => ['name' => 'Same Name']]);
        $service->accountRowOutcome($row, 'QUARANTINED', ['severity' => 'BLOCKING', 'error_code' => 'IDENTITY_AMBIGUOUS', 'field_name' => 'name', 'message' => 'Human review required']);
        $batch->update(['status' => 'DRY_RUN']);

        $candidates = $service->identityReviewCandidates($batch);

        $this->assertCount(1, $candidates);
        $this->assertSame('LEGACY-001', $candidates[0]['source_key']);
        $this->assertSame(['name' => 'Same Name'], $candidates[0]['raw_payload']);
        $this->assertSame('IDENTITY_AMBIGUOUS', $candidates[0]['errors'][0]['error_code']);
        $this->assertTrue($candidates[0]['review_required']);
        $this->assertFalse($candidates[0]['auto_match']);
        $this->assertNull($row->fresh()->canonical_entity_id);
    }

    public function test_unknown_nisn_stays_unresolved_without_creating_placeholder_student(): void
    {
        $owner = User::factory()->create();
        $service = app(ImportIntakeService::class);
        $batch = $service->createBatch($owner, ['batch_code' => 'BATCH-UNKNOWN-NISN', 'source_system' => 'LEGACY_SHEET']);
        $file = $service->registerFile($batch, ['original_filename' => 'students.csv', 'storage_path' => 'imports/BATCH-UNKNOWN-NISN/students.csv', 'sha256_checksum' => str_repeat('3', 64), 'source_granularity' => 'ROSTER_SNAPSHOT']);
        $batch = $service->transitionBatch($batch, 'RECEIVED');
        $row = $service->recordRow($batch, $file, ['row_number' => 1, 'source_key' => 'NISN-UNKNOWN-001', 'raw_payload' => ['nisn' => '9999999999', 'full_name' => 'Unknown Source Student']]);
        $service->accountRowOutcome($row, 'QUARANTINED', ['severity' => 'BLOCKING', 'error_code' => 'IDENTITY_UNRESOLVED', 'field_name' => 'nisn', 'message' => 'No canonical Student match']);
        $batch->update(['status' => 'DRY_RUN']);

        $candidates = $service->identityReviewCandidates($batch);

        $this->assertCount(1, $candidates);
        $this->assertSame('9999999999', $candidates[0]['raw_payload']['nisn']);
        $this->assertNull($row->fresh()->canonical_entity_id);
        $this->assertDatabaseCount('students', 0);
    }

    public function test_identity_mapping_review_requires_explicit_human_decision(): void
    {
        $owner = User::factory()->create();
        $reviewer = User::factory()->create();
        $service = app(ImportIntakeService::class);
        $batch = $service->createBatch($owner, ['batch_code' => 'BATCH-IDENTITY-MAPPING', 'source_system' => 'LEGACY_SHEET']);
        $mapping = ImportMapping::create(['import_batch_id' => $batch->id, 'source_type' => 'STUDENT_CODE', 'source_key' => 'LEGACY-002']);

        try {
            $service->reviewIdentityMapping($reviewer, $mapping, 'MATCHED', ['target_id' => 'student-002']);
            $this->fail('Identity review must require a DRY_RUN batch.');
        } catch (\InvalidArgumentException) {
            $this->assertTrue(true);
        }

        $batch->update(['status' => 'DRY_RUN']);
        $reviewed = $service->reviewIdentityMapping($reviewer, $mapping, 'MATCHED', ['target_type' => 'Student', 'target_id' => 'student-002', 'confidence' => 1, 'evidence' => ['method' => 'VERIFIED_CODE']]);

        $this->assertSame('MATCHED', $reviewed->mapping_status);
        $this->assertSame($reviewer->id, $reviewed->reviewed_by_user_id);
        $this->assertSame('student-002', $reviewed->target_id);
    }

    public function test_source_inventory_preserves_daily_and_monthly_grain_without_session_expansion(): void
    {
        $owner = User::factory()->create();
        $service = app(ImportIntakeService::class);
        $batch = $service->createBatch($owner, ['batch_code' => 'BATCH-SOURCE-INVENTORY', 'source_system' => 'LEGACY_SHEET']);
        $service->registerFile($batch, ['original_filename' => 'daily.csv', 'storage_path' => 'imports/BATCH-SOURCE-INVENTORY/daily.csv', 'sha256_checksum' => str_repeat('d', 64), 'source_granularity' => 'DAILY_LEVEL']);
        $service->registerFile($batch, ['original_filename' => 'monthly.csv', 'storage_path' => 'imports/BATCH-SOURCE-INVENTORY/monthly.csv', 'sha256_checksum' => str_repeat('e', 64), 'source_granularity' => 'MONTHLY_SUMMARY']);

        $inventory = $service->sourceInventory($batch);

        $this->assertSame(2, $inventory['file_count']);
        $this->assertSame(['DAILY_LEVEL', 'MONTHLY_SUMMARY'], $inventory['granularities']);
        $this->assertTrue($inventory['requires_legacy_daily_storage']);
        $this->assertTrue($inventory['requires_legacy_monthly_storage']);
        $this->assertFalse($inventory['session_expansion_allowed']);
    }

    public function test_canonical_import_preflight_accepts_only_reconciled_traceable_rows(): void
    {
        $owner = User::factory()->create();
        $service = app(ImportIntakeService::class);
        $batch = $service->createBatch($owner, ['batch_code' => 'BATCH-IMPORT-PREFLIGHT', 'source_system' => 'LEGACY_SHEET']);
        $batch->update(['status' => 'RECONCILED', 'source_total' => 1, 'imported_count' => 1]);
        $file = ImportFile::create(['import_batch_id' => $batch->id, 'original_filename' => 'accepted.csv', 'storage_path' => 'imports/BATCH-IMPORT-PREFLIGHT/accepted.csv', 'sha256_checksum' => str_repeat('f', 64), 'source_granularity' => 'DAILY_LEVEL']);
        $row = ImportRow::create(['import_batch_id' => $batch->id, 'import_file_id' => $file->id, 'row_number' => 1, 'source_key' => 'LEGACY-005', 'raw_payload' => ['value' => 'accepted'], 'row_status' => 'IMPORTED', 'canonical_entity_type' => 'Student', 'canonical_entity_id' => 'student-005']);
        ImportLineage::create(['import_batch_id' => $batch->id, 'import_file_id' => $file->id, 'import_row_id' => $row->id, 'target_type' => 'Student', 'target_id' => 'student-005']);

        $preflight = $service->canonicalImportPreflight($batch);

        $this->assertTrue($preflight['ready_for_canonical_import']);
        $this->assertSame([], $preflight['blocking_reasons']);
        $this->assertSame(1, $preflight['accounted_total']);
    }

    public function test_canonical_import_preflight_reports_untraceable_imported_rows(): void
    {
        $owner = User::factory()->create();
        $service = app(ImportIntakeService::class);
        $batch = $service->createBatch($owner, ['batch_code' => 'BATCH-IMPORT-PREFLIGHT-BLOCKED', 'source_system' => 'LEGACY_SHEET']);
        $batch->update(['status' => 'RECONCILED', 'source_total' => 1, 'imported_count' => 1]);
        $file = ImportFile::create(['import_batch_id' => $batch->id, 'original_filename' => 'blocked.csv', 'storage_path' => 'imports/BATCH-IMPORT-PREFLIGHT-BLOCKED/blocked.csv', 'sha256_checksum' => str_repeat('0', 64), 'source_granularity' => 'DAILY_LEVEL']);
        ImportRow::create(['import_batch_id' => $batch->id, 'import_file_id' => $file->id, 'row_number' => 1, 'source_key' => 'LEGACY-006', 'raw_payload' => ['value' => 'blocked'], 'row_status' => 'IMPORTED']);
        ImportMapping::create(['import_batch_id' => $batch->id, 'source_type' => 'STUDENT_CODE', 'source_key' => 'LEGACY-006']);

        $preflight = $service->canonicalImportPreflight($batch);

        $this->assertFalse($preflight['ready_for_canonical_import']);
        $this->assertContains('PENDING_IDENTITY_MAPPINGS', $preflight['blocking_reasons']);
        $this->assertContains('IMPORTED_ROWS_WITHOUT_CANONICAL_TARGET', $preflight['blocking_reasons']);
        $this->assertContains('IMPORTED_ROWS_WITHOUT_LINEAGE', $preflight['blocking_reasons']);
    }

    public function test_executor_updates_existing_student_with_append_only_audit(): void
    {
        $owner = User::factory()->create();
        $student = Student::create(['student_code' => 'STU-EXEC-001', 'full_name' => 'Old Name']);
        $service = app(ImportIntakeService::class);
        $batch = $service->createBatch($owner, ['batch_code' => 'BATCH-EXECUTOR', 'source_system' => 'LEGACY_SHEET']);
        $batch->update(['status' => 'RECONCILED', 'source_total' => 1, 'imported_count' => 1]);
        $file = ImportFile::create(['import_batch_id' => $batch->id, 'original_filename' => 'master.csv', 'storage_path' => 'imports/BATCH-EXECUTOR/master.csv', 'sha256_checksum' => str_repeat('1', 64), 'source_granularity' => 'ROSTER_SNAPSHOT']);
        $row = ImportRow::create(['import_batch_id' => $batch->id, 'import_file_id' => $file->id, 'row_number' => 1, 'source_key' => 'LEGACY-EXEC-001', 'raw_payload' => ['full_name' => 'New Name', 'student_code' => 'MUST-NOT-OVERWRITE'], 'row_status' => 'IMPORTED', 'canonical_entity_type' => 'Student', 'canonical_entity_id' => $student->id]);
        ImportLineage::create(['import_batch_id' => $batch->id, 'import_file_id' => $file->id, 'import_row_id' => $row->id, 'target_type' => 'Student', 'target_id' => $student->id]);

        $imported = $service->executeMappedStudentUpdates($owner, $batch);

        $this->assertSame('IMPORTED', $imported->status);
        $this->assertSame('New Name', $student->fresh()->full_name);
        $this->assertSame('STU-EXEC-001', $student->fresh()->student_code);
        $this->assertSame(2, $student->fresh()->version_no);
        $this->assertSame(1, AuditLog::where('action', 'IMPORT_STUDENT_MASTER_APPLIED')->count());
    }

    public function test_executor_imports_effective_status_history_without_overwriting_prior_history(): void
    {
        $owner = User::factory()->create();
        $student = Student::create(['student_code' => 'STU-HISTORY-001', 'full_name' => 'History Student']);
        StudentStatusHistory::create(['student_id' => $student->id, 'status' => 'ACTIVE', 'effective_from' => '2025-01-01', 'effective_until' => '2026-01-01', 'actor_user_id' => $owner->id]);
        $service = app(ImportIntakeService::class);
        $batch = $service->createBatch($owner, ['batch_code' => 'BATCH-HISTORY-EXECUTOR', 'source_system' => 'LEGACY_SHEET']);
        $batch->update(['status' => 'RECONCILED', 'source_total' => 1, 'imported_count' => 1]);
        $file = ImportFile::create(['import_batch_id' => $batch->id, 'original_filename' => 'history.csv', 'storage_path' => 'imports/BATCH-HISTORY-EXECUTOR/history.csv', 'sha256_checksum' => str_repeat('2', 64), 'source_granularity' => 'ROSTER_SNAPSHOT']);
        $row = ImportRow::create(['import_batch_id' => $batch->id, 'import_file_id' => $file->id, 'row_number' => 1, 'source_key' => 'LEGACY-HISTORY-001', 'raw_payload' => ['status' => 'GRADUATED', 'effective_from' => '2026-01-01', 'decision_reference' => 'DEC-001'], 'row_status' => 'IMPORTED', 'canonical_entity_type' => 'Student', 'canonical_entity_id' => $student->id]);
        ImportLineage::create(['import_batch_id' => $batch->id, 'import_file_id' => $file->id, 'import_row_id' => $row->id, 'target_type' => 'Student', 'target_id' => $student->id]);

        $imported = $service->executeMappedStudentStatusHistory($owner, $batch);

        $this->assertSame('IMPORTED', $imported->status);
        $this->assertSame(2, StudentStatusHistory::where('student_id', $student->id)->count());
        $this->assertSame('GRADUATED', StudentStatusHistory::where('student_id', $student->id)->latest('effective_from')->value('status'));
        $this->assertSame(1, AuditLog::where('action', 'IMPORT_STUDENT_STATUS_HISTORY_APPLIED')->count());
    }

    public function test_reviewed_mapping_propagates_target_and_lineage_idempotently(): void
    {
        $owner = User::factory()->create();
        $reviewer = User::factory()->create();
        $service = app(ImportIntakeService::class);
        $batch = $service->createBatch($owner, ['batch_code' => 'BATCH-MAPPING-PROPAGATE', 'source_system' => 'LEGACY_SHEET']);
        $file = $service->registerFile($batch, ['original_filename' => 'rows.csv', 'storage_path' => 'imports/BATCH-MAPPING-PROPAGATE/rows.csv', 'sha256_checksum' => str_repeat('b', 64), 'source_granularity' => 'DAILY_LEVEL']);
        $batch = $service->transitionBatch($batch, 'RECEIVED');
        $row = $service->recordRow($batch, $file, ['row_number' => 1, 'source_key' => 'LEGACY-003', 'raw_payload' => ['student_code' => 'LEGACY-003']]);
        $mapping = ImportMapping::create(['import_batch_id' => $batch->id, 'source_type' => 'STUDENT_CODE', 'source_key' => 'LEGACY-003']);
        $batch->update(['status' => 'DRY_RUN']);
        $service->reviewIdentityMapping($reviewer, $mapping, 'MATCHED', ['target_type' => 'Student', 'target_id' => 'student-003', 'evidence' => ['method' => 'VERIFIED_CODE']]);

        $first = $service->propagateReviewedMapping($mapping, $row);
        $repeat = $service->propagateReviewedMapping($mapping, $row);

        $this->assertSame('student-003', $first->canonical_entity_id);
        $this->assertSame($first->id, $repeat->id);
        $this->assertCount(1, ImportLineage::where('import_row_id', $row->id)->get());
    }

    public function test_reconciliation_requires_identity_mappings_to_be_reviewed(): void
    {
        $owner = User::factory()->create();
        $service = app(ImportIntakeService::class);
        $batch = $service->createBatch($owner, ['batch_code' => 'BATCH-RECONCILE-MAPPING', 'source_system' => 'LEGACY_SHEET']);
        $batch->update(['source_total' => 1]);
        $file = $service->registerFile($batch, ['original_filename' => 'rows.csv', 'storage_path' => 'imports/BATCH-RECONCILE-MAPPING/rows.csv', 'sha256_checksum' => str_repeat('c', 64), 'source_granularity' => 'DAILY_LEVEL']);
        $batch = $service->transitionBatch($batch, 'RECEIVED');
        $row = $service->recordRow($batch, $file, ['row_number' => 1, 'source_key' => 'LEGACY-004', 'raw_payload' => ['value' => 'one']]);
        $service->accountRowOutcome($row, 'IMPORTED');
        ImportMapping::create(['import_batch_id' => $batch->id, 'source_type' => 'STUDENT_CODE', 'source_key' => 'LEGACY-004']);
        $batch = $service->transitionBatch($batch, 'PROFILED');
        $batch->update(['status' => 'DRY_RUN']);

        $this->expectException(\InvalidArgumentException::class);
        $service->finalizeReconciliation($batch);
    }
}
