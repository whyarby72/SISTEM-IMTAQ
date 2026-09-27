<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_status_history', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('students')->restrictOnDelete();
            $table->string('status');
            $table->date('effective_from');
            $table->date('effective_until')->nullable();
            $table->string('decision_reference')->nullable();
            $table->text('reason')->nullable();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->unsignedInteger('version_no')->default(1);
            $table->timestampsTz();

            $table->index(['student_id', 'effective_from']);
            $table->index(['student_id', 'status']);
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('CREATE EXTENSION IF NOT EXISTS btree_gist');
            DB::statement("ALTER TABLE student_status_history
                ADD CONSTRAINT student_status_history_no_overlap
                EXCLUDE USING gist (
                    student_id WITH =,
                    daterange(effective_from, effective_until, '[)') WITH &&
                )");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('student_status_history');
    }
};
