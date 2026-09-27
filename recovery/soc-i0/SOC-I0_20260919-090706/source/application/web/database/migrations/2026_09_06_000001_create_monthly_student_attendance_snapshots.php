<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_student_attendance_snapshots', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('period', 7);
            $table->foreignUuid('student_id')->constrained('students')->restrictOnDelete();
            $table->foreignUuid('class_id')->constrained('classes')->restrictOnDelete();
            $table->string('class_admin');
            $table->string('attendance_group');
            $table->string('matiq_report_class');
            $table->string('source_record_id');
            $table->string('source_checksum', 64);
            $table->foreignUuid('import_batch_id')->constrained('import_batches')->restrictOnDelete();
            $table->foreignUuid('import_file_id')->constrained('import_files')->restrictOnDelete();
            $table->foreignUuid('import_row_id')->constrained('import_rows')->restrictOnDelete();
            $table->unsignedInteger('scheduled_attendance_units_working');
            $table->unsignedInteger('present');
            $table->unsignedInteger('permission');
            $table->unsignedInteger('sick');
            $table->unsignedInteger('absent');
            $table->unsignedInteger('eligible');
            $table->unsignedInteger('non_eligible');
            $table->string('non_eligible_reason')->nullable();
            $table->decimal('attendance_rate', 8, 6);
            $table->string('source_absent_term')->nullable();
            $table->jsonb('raw_payload')->nullable();
            $table->timestampsTz();
            $table->unique(['period', 'student_id', 'source_checksum']);
            $table->unique(['import_row_id']);
            $table->index(['period', 'class_id']);
            $table->index(['source_record_id', 'source_checksum']);
        });

        DB::statement('ALTER TABLE monthly_student_attendance_snapshots ADD CONSTRAINT monthly_student_snapshots_formula_check CHECK (eligible = present + permission + sick + absent)');
        DB::statement('ALTER TABLE monthly_student_attendance_snapshots ADD CONSTRAINT monthly_student_snapshots_rate_check CHECK (attendance_rate >= 0 AND attendance_rate <= 1)');
        DB::statement('ALTER TABLE monthly_student_attendance_snapshots ADD CONSTRAINT monthly_student_snapshots_scheduled_check CHECK (scheduled_attendance_units_working >= eligible + non_eligible)');
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_student_attendance_snapshots');
    }
};
