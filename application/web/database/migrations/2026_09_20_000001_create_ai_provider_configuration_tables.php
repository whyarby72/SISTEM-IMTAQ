<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_provider_credentials', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('provider', 50);
            $table->string('label', 120);
            $table->text('encrypted_secret');
            $table->string('secret_last4', 4);
            $table->string('status', 30)->default('PENDING');
            $table->timestampTz('verified_at')->nullable();
            $table->timestampTz('revoked_at')->nullable();
            $table->timestampsTz();
            $table->index(['provider', 'status']);
        });

        Schema::create('ai_provider_configurations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('provider', 50);
            $table->uuid('credential_id');
            $table->string('model', 160);
            $table->unsignedInteger('max_output_tokens')->default(800);
            $table->string('status', 30)->default('DRAFT');
            $table->json('verification_metadata')->nullable();
            $table->timestampTz('verified_at')->nullable();
            $table->timestampTz('activated_at')->nullable();
            $table->timestampsTz();
            $table->foreign('credential_id')->references('id')->on('ai_provider_credentials')->restrictOnDelete();
            $table->index(['provider', 'status']);
        });

        Schema::create('ai_provider_active_configurations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('provider', 50)->unique();
            $table->uuid('configuration_id')->nullable();
            $table->boolean('runtime_enabled')->default(false);
            $table->unsignedInteger('version_no')->default(1);
            $table->timestampsTz();
            $table->foreign('configuration_id')->references('id')->on('ai_provider_configurations')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_provider_active_configurations');
        Schema::dropIfExists('ai_provider_configurations');
        Schema::dropIfExists('ai_provider_credentials');
    }
};
