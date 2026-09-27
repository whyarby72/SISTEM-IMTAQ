<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->timestampTz('occurred_at');
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('actor_type');
            $table->string('action');
            $table->string('entity_type');
            $table->string('entity_id');
            $table->unsignedInteger('version_before')->nullable();
            $table->unsignedInteger('version_after')->nullable();
            $table->jsonb('old_values')->nullable();
            $table->jsonb('new_values')->nullable();
            $table->text('reason')->nullable();
            $table->string('source_channel');
            $table->string('correlation_id')->nullable();
            $table->string('request_id')->nullable();
            $table->uuid('correction_request_id')->nullable();
            $table->jsonb('technical_metadata')->nullable();

            $table->index(['entity_type', 'entity_id', 'occurred_at']);
            $table->index(['actor_user_id', 'occurred_at']);
            $table->index('correction_request_id');
        });

        Schema::create('correction_requests', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('requested_by_user_id')->constrained('users')->restrictOnDelete();
            $table->string('entity_type');
            $table->string('entity_id');
            $table->string('correction_type');
            $table->string('status')->default('PENDING');
            $table->jsonb('requested_changes');
            $table->text('reason');
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestampTz('reviewed_at')->nullable();
            $table->timestampTz('applied_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->unsignedInteger('version_no')->default(1);
            $table->timestampsTz();

            $table->index(['entity_type', 'entity_id', 'status']);
            $table->index(['requested_by_user_id', 'status']);
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("CREATE OR REPLACE FUNCTION prevent_audit_log_mutation() RETURNS trigger AS $$
                BEGIN
                    RAISE EXCEPTION 'audit_logs is append-only';
                END;
            $$ LANGUAGE plpgsql");
            DB::statement('CREATE TRIGGER audit_logs_append_only
                BEFORE UPDATE OR DELETE ON audit_logs
                FOR EACH ROW EXECUTE FUNCTION prevent_audit_log_mutation()');
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('DROP TRIGGER IF EXISTS audit_logs_append_only ON audit_logs');
            DB::statement('DROP FUNCTION IF EXISTS prevent_audit_log_mutation()');
        }

        Schema::dropIfExists('correction_requests');
        Schema::dropIfExists('audit_logs');
    }
};
