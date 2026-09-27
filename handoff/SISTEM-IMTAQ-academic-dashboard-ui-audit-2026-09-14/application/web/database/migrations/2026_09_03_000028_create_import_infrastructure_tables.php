<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_batches', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('batch_code')->unique();
            $table->string('source_system');
            $table->string('source_period')->nullable();
            $table->string('status')->default('DRAFT');
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestampTz('received_at')->nullable();
            $table->unsignedInteger('source_total')->default(0);
            $table->unsignedInteger('imported_count')->default(0);
            $table->unsignedInteger('rejected_count')->default(0);
            $table->unsignedInteger('quarantined_count')->default(0);
            $table->unsignedInteger('duplicate_count')->default(0);
            $table->unsignedInteger('excluded_count')->default(0);
            $table->timestampsTz();
        });

        Schema::create('import_files', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('import_batch_id')->constrained('import_batches')->restrictOnDelete();
            $table->string('original_filename');
            $table->string('storage_path');
            $table->string('sha256_checksum', 64);
            $table->string('source_granularity')->default('UNKNOWN');
            $table->unsignedBigInteger('file_size_bytes')->nullable();
            $table->timestampTz('received_at')->nullable();
            $table->timestampsTz();
            $table->unique(['import_batch_id', 'sha256_checksum']);
            $table->unique(['id', 'import_batch_id']);
        });

        Schema::create('import_rows', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('import_batch_id')->constrained('import_batches')->restrictOnDelete();
            $table->foreignUuid('import_file_id')->constrained('import_files')->restrictOnDelete();
            $table->unsignedInteger('row_number');
            $table->string('source_key')->nullable();
            $table->jsonb('raw_payload');
            $table->string('row_status')->default('PENDING');
            $table->string('canonical_entity_type')->nullable();
            $table->string('canonical_entity_id')->nullable();
            $table->timestampTz('accounted_at')->nullable();
            $table->timestampsTz();
            $table->unique(['import_file_id', 'row_number']);
            $table->unique(['id', 'import_batch_id', 'import_file_id']);
            $table->foreign(['import_file_id', 'import_batch_id'])->references(['id', 'import_batch_id'])->on('import_files')->restrictOnDelete();
            $table->index(['import_batch_id', 'row_status']);
        });

        Schema::create('import_row_errors', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('import_row_id')->constrained('import_rows')->restrictOnDelete();
            $table->string('severity')->default('BLOCKING');
            $table->string('error_code');
            $table->string('field_name')->nullable();
            $table->text('message');
            $table->jsonb('details')->nullable();
            $table->foreignId('resolved_by_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestampTz('resolved_at')->nullable();
            $table->timestampsTz();
            $table->index(['import_row_id', 'severity']);
        });

        Schema::create('import_mappings', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('import_batch_id')->constrained('import_batches')->restrictOnDelete();
            $table->string('source_type');
            $table->string('source_key');
            $table->string('target_type')->nullable();
            $table->string('target_id')->nullable();
            $table->string('mapping_status')->default('PENDING');
            $table->decimal('confidence', 5, 4)->nullable();
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestampTz('reviewed_at')->nullable();
            $table->jsonb('evidence')->nullable();
            $table->timestampsTz();
            $table->unique(['import_batch_id', 'source_type', 'source_key']);
        });

        Schema::create('import_lineages', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('import_batch_id')->constrained('import_batches')->restrictOnDelete();
            $table->foreignUuid('import_file_id')->constrained('import_files')->restrictOnDelete();
            $table->foreignUuid('import_row_id')->constrained('import_rows')->restrictOnDelete();
            $table->string('target_type');
            $table->string('target_id');
            $table->string('relationship_type')->default('IMPORTED_FROM');
            $table->timestampsTz();
            $table->unique(['import_row_id', 'target_type', 'target_id']);
            $table->foreign(['import_file_id', 'import_batch_id'])->references(['id', 'import_batch_id'])->on('import_files')->restrictOnDelete();
            $table->foreign(['import_row_id', 'import_file_id', 'import_batch_id'])->references(['id', 'import_file_id', 'import_batch_id'])->on('import_rows')->restrictOnDelete();
            $table->index(['target_type', 'target_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_lineages');
        Schema::dropIfExists('import_mappings');
        Schema::dropIfExists('import_row_errors');
        Schema::dropIfExists('import_rows');
        Schema::dropIfExists('import_files');
        Schema::dropIfExists('import_batches');
    }
};
