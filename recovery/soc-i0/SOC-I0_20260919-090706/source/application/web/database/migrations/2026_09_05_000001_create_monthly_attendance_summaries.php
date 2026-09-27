<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_attendance_summaries', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('period', 7);
            $table->foreignUuid('class_id')->constrained('classes')->restrictOnDelete();
            $table->string('attendance_group');
            $table->string('matiq_report_class');
            $table->unsignedInteger('roster');
            $table->unsignedInteger('present');
            $table->unsignedInteger('permission');
            $table->unsignedInteger('sick');
            $table->unsignedInteger('absent');
            $table->unsignedInteger('eligible');
            $table->unsignedInteger('non_eligible');
            $table->decimal('attendance_rate', 8, 6);
            $table->string('non_eligible_reason')->nullable();
            $table->foreignUuid('import_batch_id')->constrained('import_batches')->restrictOnDelete();
            $table->string('source_checksum', 64);
            $table->string('status')->default('DRAFT');
            $table->timestampsTz();
            $table->unique(['period', 'class_id', 'source_checksum']);
            $table->index(['period', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_attendance_summaries');
    }
};
