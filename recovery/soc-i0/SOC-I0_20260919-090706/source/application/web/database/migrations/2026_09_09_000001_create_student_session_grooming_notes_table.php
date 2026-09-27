<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_session_grooming_notes', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('session_student_participant_id')->unique()->constrained('session_student_participants')->restrictOnDelete();
            $table->text('note_text')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestampTz('created_at');
            $table->foreignId('updated_by')->constrained('users')->restrictOnDelete();
            $table->timestampTz('updated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_session_grooming_notes');
    }
};
