<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alert_rules', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('rule_code');
            $table->unsignedInteger('version_no')->default(1);
            // Add the self-reference after the table exists; PostgreSQL rejects the
            // inline circular reference on some local installations.
            $table->uuid('supersedes_rule_id')->nullable();
            $table->string('name');
            $table->string('category');
            $table->boolean('is_active')->default(true);
            $table->json('configuration')->nullable();
            $table->timestampsTz();
            $table->unique(['rule_code', 'version_no']);
        });

        Schema::table('alert_rules', function (Blueprint $table): void {
            $table->foreign('supersedes_rule_id', 'alert_rules_supersedes_rule_fk')->references('id')->on('alert_rules')->restrictOnDelete();
        });

        Schema::create('alerts', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('alert_rule_id')->constrained('alert_rules')->restrictOnDelete();
            $table->string('fingerprint');
            $table->string('dedup_key')->nullable();
            $table->string('entity_type')->nullable();
            $table->uuid('entity_id')->nullable();
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('severity');
            $table->string('status')->default('OPEN');
            $table->timestampTz('due_at')->nullable();
            $table->timestampTz('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->json('evidence')->nullable();
            $table->timestampsTz();
            $table->index(['alert_rule_id', 'fingerprint', 'status']);
            $table->unique(['alert_rule_id', 'dedup_key']);
        });

        Schema::create('alert_actions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('alert_id')->constrained('alerts')->restrictOnDelete();
            $table->foreignId('actor_user_id')->constrained('users')->restrictOnDelete();
            $table->string('action_type');
            $table->string('from_status')->nullable();
            $table->string('to_status')->nullable();
            $table->text('notes')->nullable();
            $table->timestampsTz();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('alert_actions');
        Schema::dropIfExists('alerts');
        Schema::dropIfExists('alert_rules');
    }
};
