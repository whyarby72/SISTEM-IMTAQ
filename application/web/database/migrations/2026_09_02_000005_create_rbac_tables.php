<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestampsTz();
        });

        Schema::create('permissions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestampsTz();
        });

        Schema::create('role_permissions', function (Blueprint $table): void {
            $table->foreignUuid('role_id')->constrained('roles')->restrictOnDelete();
            $table->foreignUuid('permission_id')->constrained('permissions')->restrictOnDelete();
            $table->primary(['role_id', 'permission_id']);
        });

        Schema::create('user_role_assignments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignUuid('role_id')->constrained('roles')->restrictOnDelete();
            $table->string('scope_type')->default('INSTITUTION');
            $table->string('scope_key')->nullable();
            $table->date('effective_from')->nullable();
            $table->date('effective_until')->nullable();
            $table->string('assignment_reason')->nullable();
            $table->unsignedInteger('version_no')->default(1);
            $table->timestampsTz();

            $table->index(['user_id', 'effective_from', 'effective_until']);
            $table->index(['scope_type', 'scope_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_role_assignments');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
