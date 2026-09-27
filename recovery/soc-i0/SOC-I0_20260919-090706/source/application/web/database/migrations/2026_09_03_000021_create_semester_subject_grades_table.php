<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('semester_subject_grades', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('students')->restrictOnDelete();
            $table->foreignUuid('semester_id')->constrained('semesters')->restrictOnDelete();
            $table->foreignUuid('subject_id')->constrained('subjects')->restrictOnDelete();
            $table->decimal('score', 5, 2)->nullable();
            $table->string('grade_source')->default('DIRECT_ENTRY');
            $table->foreignUuid('source_teaching_assignment_id')->nullable()->constrained('teaching_assignments')->restrictOnDelete();
            $table->foreignUuid('responsible_staff_id')->nullable()->constrained('staff')->restrictOnDelete();
            $table->string('workflow_status')->default('DRAFT');
            $table->unsignedInteger('version_no')->default(1);
            $table->foreignId('entered_by')->constrained('users')->restrictOnDelete();
            $table->timestampTz('entered_at');
            $table->foreignId('finalized_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestampTz('finalized_at')->nullable();
            $table->foreignId('updated_by')->constrained('users')->restrictOnDelete();
            $table->timestampTz('updated_at');
            $table->timestampTz('created_at')->nullable();

            $table->unique(['student_id', 'semester_id', 'subject_id']);
            $table->index(['semester_id', 'subject_id', 'workflow_status']);
        });

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE semester_subject_grades ADD CONSTRAINT semester_subject_grades_score_check CHECK (score IS NULL OR (score >= 0 AND score <= 100))');
            DB::statement("ALTER TABLE semester_subject_grades ADD CONSTRAINT semester_subject_grades_source_check CHECK (grade_source IN ('DIRECT_ENTRY', 'IMPORTED'))");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('semester_subject_grades');
    }
};
