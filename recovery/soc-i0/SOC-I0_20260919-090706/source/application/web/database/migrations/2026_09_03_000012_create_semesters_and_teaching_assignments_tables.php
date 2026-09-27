<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('semesters', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('academic_year_id')->constrained('academic_years')->restrictOnDelete();
            $table->string('semester_code');
            $table->string('display_name');
            $table->unsignedSmallInteger('sequence_no');
            $table->date('starts_on');
            $table->date('ends_on');
            $table->string('status')->default('ACTIVE');
            $table->unsignedInteger('version_no')->default(1);
            $table->timestampsTz();

            $table->unique(['academic_year_id', 'semester_code']);
        });

        Schema::create('teaching_assignments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('assignment_code')->unique();
            $table->foreignUuid('semester_id')->constrained('semesters')->restrictOnDelete();
            $table->foreignUuid('class_id')->constrained('classes')->restrictOnDelete();
            $table->foreignUuid('subject_id')->constrained('subjects')->restrictOnDelete();
            $table->foreignUuid('teacher_staff_id')->constrained('staff')->restrictOnDelete();
            $table->date('effective_from');
            $table->date('effective_until')->nullable();
            $table->string('workflow_status');
            $table->string('source_reference')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->unsignedInteger('version_no')->default(1);
            $table->timestampsTz();

            $table->index(['semester_id', 'class_id']);
            $table->index(['teacher_staff_id', 'effective_from']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teaching_assignments');
        Schema::dropIfExists('semesters');
    }
};
