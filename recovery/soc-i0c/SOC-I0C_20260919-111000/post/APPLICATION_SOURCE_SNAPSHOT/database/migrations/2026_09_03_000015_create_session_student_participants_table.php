<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_student_participants', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('class_session_id')->constrained('class_sessions')->restrictOnDelete();
            $table->foreignUuid('student_id')->constrained('students')->restrictOnDelete();
            $table->string('participant_basis');
            $table->string('participant_status')->default('EXPECTED');
            $table->boolean('is_required')->default(true);
            $table->string('removal_reason')->nullable();
            $table->foreignId('removed_by_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestampTz('removed_at')->nullable();
            $table->timestampsTz();

            $table->unique(['class_session_id', 'student_id']);
            $table->index(['class_session_id', 'participant_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_student_participants');
    }
};
