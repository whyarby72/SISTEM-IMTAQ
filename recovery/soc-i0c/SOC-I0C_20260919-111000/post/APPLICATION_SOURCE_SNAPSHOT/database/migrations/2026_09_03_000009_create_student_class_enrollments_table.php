<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_class_enrollments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('students')->restrictOnDelete();
            $table->foreignUuid('class_id')->constrained('classes')->restrictOnDelete();
            $table->date('effective_from');
            $table->date('effective_until')->nullable();
            $table->string('status')->default('ACTIVE');
            $table->string('reason')->nullable();
            $table->string('source_reference')->nullable();
            $table->unsignedInteger('version_no')->default(1);
            $table->timestampsTz();

            $table->index(['student_id', 'effective_from']);
            $table->index(['class_id', 'effective_from']);
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('CREATE EXTENSION IF NOT EXISTS btree_gist');
            DB::statement("ALTER TABLE student_class_enrollments
                ADD CONSTRAINT student_class_enrollments_no_overlap
                EXCLUDE USING gist (
                    student_id WITH =,
                    daterange(effective_from, effective_until, '[)') WITH &&
                )");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('student_class_enrollments');
    }
};
