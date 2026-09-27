<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_cards', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('students')->restrictOnDelete();
            $table->foreignUuid('semester_id')->constrained('semesters')->restrictOnDelete();
            $table->string('report_type')->default('SEMESTER');
            $table->timestampsTz();
            $table->unique(['student_id', 'semester_id', 'report_type']);
        });

        Schema::create('report_card_versions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('report_card_id')->constrained('report_cards')->restrictOnDelete();
            $table->unsignedInteger('version_no');
            $table->string('status')->default('DRAFT');
            $table->timestampTz('source_cutoff_at');
            $table->string('student_name_snapshot');
            $table->foreignUuid('class_id')->constrained('classes')->restrictOnDelete();
            $table->string('class_name_snapshot');
            $table->string('semester_name_snapshot');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestampsTz();
            $table->unique(['report_card_id', 'version_no']);
        });

        Schema::create('report_card_subject_lines', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('report_card_version_id')->constrained('report_card_versions')->restrictOnDelete();
            $table->foreignUuid('subject_id')->constrained('subjects')->restrictOnDelete();
            $table->string('subject_name_snapshot');
            $table->foreignUuid('semester_subject_grade_id')->constrained('semester_subject_grades')->restrictOnDelete();
            $table->unsignedInteger('grade_version_no');
            $table->decimal('score', 5, 2);
            $table->timestampsTz();
            $table->unique(['report_card_version_id', 'subject_id']);
        });

        Schema::create('report_card_attendance_lines', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('report_card_version_id')->constrained('report_card_versions')->restrictOnDelete();
            $table->string('status_code');
            $table->unsignedInteger('status_count');
            $table->timestampsTz();
            $table->unique(['report_card_version_id', 'status_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_card_attendance_lines');
        Schema::dropIfExists('report_card_subject_lines');
        Schema::dropIfExists('report_card_versions');
        Schema::dropIfExists('report_cards');
    }
};
