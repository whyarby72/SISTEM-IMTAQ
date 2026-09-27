<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_attendance', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('session_student_participant_id')->unique()->constrained('session_student_participants')->restrictOnDelete();
            $table->string('attendance_status')->nullable();
            $table->string('reason_code')->nullable();
            $table->uuid('permission_event_id')->nullable();
            $table->timestampTz('arrival_at')->nullable();
            $table->timestampTz('departure_at')->nullable();
            $table->text('notes')->nullable();
            $table->string('workflow_status')->default('DRAFT');
            $table->unsignedInteger('version_no')->default(1);
            $table->foreignId('entered_by')->constrained('users')->restrictOnDelete();
            $table->timestampTz('entered_at');
            $table->foreignId('finalized_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestampTz('finalized_at')->nullable();
            $table->foreignId('updated_by')->constrained('users')->restrictOnDelete();
            $table->timestampTz('updated_at');

            $table->index(['workflow_status', 'updated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_attendance');
    }
};
