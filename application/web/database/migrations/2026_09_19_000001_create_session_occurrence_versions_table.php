<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_occurrence_versions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('class_session_id')->constrained('class_sessions')->restrictOnDelete();
            $table->unsignedInteger('version_no');
            $table->string('occurrence_status');
            $table->foreignId('recorded_by_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->text('reason')->nullable();
            $table->boolean('is_partial')->default(false);
            $table->text('partial_reason')->nullable();
            // The superseded version must be from the same ClassSession. The
            // composite FK is added after the table exists so its referenced
            // candidate key is available to PostgreSQL.
            $table->uuid('supersedes_version_id')->nullable();
            $table->foreignUuid('replacement_class_session_id')->nullable()->constrained('class_sessions')->restrictOnDelete();
            $table->timestampTz('recorded_at')->useCurrent();
            $table->timestampsTz();

            $table->unique(['class_session_id', 'version_no']);
            $table->unique(['class_session_id', 'id']);
            $table->index(['class_session_id', 'occurrence_status']);
            $table->index('supersedes_version_id');
        });

        Schema::table('session_occurrence_versions', function (Blueprint $table): void {
            $table->foreign(['class_session_id', 'supersedes_version_id'], 'session_occurrence_versions_same_session_supersedes_fk')
                ->references(['class_session_id', 'id'])
                ->on('session_occurrence_versions')
                ->restrictOnDelete();
        });

        Schema::table('class_sessions', function (Blueprint $table): void {
            $table->uuid('effective_occurrence_version_id')->nullable();
            $table->foreign(['id', 'effective_occurrence_version_id'])
                ->references(['class_session_id', 'id'])
                ->on('session_occurrence_versions')
                ->restrictOnDelete();
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE session_occurrence_versions ADD CONSTRAINT session_occurrence_versions_status_check CHECK (occurrence_status IN ('SCHEDULED','HELD','CANCELLED','RESCHEDULED'))");
        }
    }

    public function down(): void
    {
        Schema::table('class_sessions', function (Blueprint $table): void {
            $table->dropForeign(['id', 'effective_occurrence_version_id']);
            $table->dropColumn('effective_occurrence_version_id');
        });

        Schema::table('session_occurrence_versions', function (Blueprint $table): void {
            $table->dropForeign('session_occurrence_versions_same_session_supersedes_fk');
        });

        Schema::dropIfExists('session_occurrence_versions');
    }
};
