<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_transcripts', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('students')->restrictOnDelete();
            $table->string('transcript_type')->default('ACADEMIC');
            $table->timestampsTz();
            $table->unique(['student_id', 'transcript_type']);
        });

        Schema::create('academic_transcript_versions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('academic_transcript_id')->constrained('academic_transcripts')->restrictOnDelete();
            $table->unsignedInteger('version_no');
            $table->string('status')->default('DRAFT');
            $table->timestampTz('source_cutoff_at');
            $table->string('student_name_snapshot');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestampsTz();
            $table->unique(['academic_transcript_id', 'version_no']);
        });

        Schema::create('academic_transcript_lines', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('academic_transcript_version_id')->constrained('academic_transcript_versions')->restrictOnDelete();
            $table->foreignUuid('semester_id')->constrained('semesters')->restrictOnDelete();
            $table->foreignUuid('subject_id')->constrained('subjects')->restrictOnDelete();
            $table->string('semester_name_snapshot');
            $table->string('subject_name_snapshot');
            $table->foreignUuid('semester_subject_grade_id')->constrained('semester_subject_grades')->restrictOnDelete();
            $table->unsignedInteger('grade_version_no');
            $table->decimal('score', 5, 2);
            $table->timestampsTz();
            $table->unique(['academic_transcript_version_id', 'semester_id', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_transcript_lines');
        Schema::dropIfExists('academic_transcript_versions');
        Schema::dropIfExists('academic_transcripts');
    }
};
