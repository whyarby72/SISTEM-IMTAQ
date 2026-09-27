<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedule_changes', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('change_code')->unique();
            $table->string('change_type');
            $table->foreignUuid('source_session_id')->nullable()->constrained('class_sessions')->restrictOnDelete();
            $table->foreignUuid('related_session_id')->nullable()->constrained('class_sessions')->restrictOnDelete();
            $table->foreignUuid('original_teacher_id')->nullable()->constrained('staff')->restrictOnDelete();
            $table->foreignUuid('replacement_teacher_id')->nullable()->constrained('staff')->restrictOnDelete();
            $table->timestampTz('new_start_at')->nullable();
            $table->timestampTz('new_end_at')->nullable();
            $table->text('reason');
            $table->foreignId('requested_by_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestampTz('requested_at')->nullable();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestampTz('approved_at')->nullable();
            $table->foreignId('applied_by_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestampTz('applied_at')->nullable();
            $table->string('status');
            $table->timestampsTz();

            $table->index(['source_session_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_changes');
    }
};
