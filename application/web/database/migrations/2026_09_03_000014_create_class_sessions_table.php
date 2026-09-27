<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_sessions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('session_code')->unique();
            $table->foreignUuid('teaching_assignment_id')->constrained('teaching_assignments')->restrictOnDelete();
            $table->foreignUuid('schedule_rule_id')->nullable()->constrained('schedule_rules')->restrictOnDelete();
            $table->foreignUuid('class_id')->constrained('classes')->restrictOnDelete();
            $table->foreignUuid('subject_id')->constrained('subjects')->restrictOnDelete();
            $table->foreignUuid('location_id')->nullable()->constrained('locations')->restrictOnDelete();
            $table->timestampTz('planned_start_at');
            $table->timestampTz('planned_end_at');
            $table->timestampTz('actual_start_at')->nullable();
            $table->timestampTz('actual_end_at')->nullable();
            $table->string('session_source');
            $table->string('participant_scope');
            $table->string('session_status');
            $table->uuid('rescheduled_from_session_id')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedInteger('version_no')->default(1);
            $table->timestampsTz();

            $table->index(['class_id', 'planned_start_at']);
            $table->index(['teaching_assignment_id', 'planned_start_at']);
        });

        Schema::table('class_sessions', function (Blueprint $table): void {
            $table->foreign('rescheduled_from_session_id')
                ->references('id')
                ->on('class_sessions')
                ->restrictOnDelete();
        });

        DB::statement('CREATE UNIQUE INDEX class_sessions_schedule_rule_start_unique
            ON class_sessions (schedule_rule_id, planned_start_at)
            WHERE schedule_rule_id IS NOT NULL');

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('CREATE EXTENSION IF NOT EXISTS btree_gist');
            DB::statement("ALTER TABLE class_sessions
                ADD CONSTRAINT class_sessions_active_no_overlap
                EXCLUDE USING gist (
                    class_id WITH =,
                    tstzrange(planned_start_at, planned_end_at, '[)') WITH &&
                ) WHERE (session_status IN ('PLANNED','CONFIRMED','COMPLETED'))");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('class_sessions');
    }
};
