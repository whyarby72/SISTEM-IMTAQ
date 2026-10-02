<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('features', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('module');
            $table->string('required_permission')->nullable();
            $table->boolean('default_enabled')->default(true);
            $table->boolean('system_enabled')->default(true);
            $table->boolean('is_toggleable')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestampsTz();
            $table->index(['module', 'sort_order']);
        });

        Schema::create('user_feature_overrides', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignUuid('feature_id')->constrained('features')->restrictOnDelete();
            $table->string('state', 20)->default('INHERIT');
            $table->text('reason')->nullable();
            $table->foreignId('changed_by_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->unsignedInteger('version_no')->default(1);
            $table->timestampsTz();
            $table->unique(['user_id', 'feature_id']);
            $table->index(['user_id', 'state']);
        });

        Schema::create('user_preferences', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('preference_key');
            $table->jsonb('preference_value');
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestampsTz();
            $table->unique(['user_id', 'preference_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
        Schema::dropIfExists('user_feature_overrides');
        Schema::dropIfExists('features');
    }
};
