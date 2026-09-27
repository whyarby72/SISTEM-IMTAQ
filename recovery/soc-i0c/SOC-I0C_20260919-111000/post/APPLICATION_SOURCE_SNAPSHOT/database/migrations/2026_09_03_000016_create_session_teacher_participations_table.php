<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_teacher_participations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('class_session_id')->constrained('class_sessions')->restrictOnDelete();
            $table->foreignUuid('teacher_staff_id')->constrained('staff')->restrictOnDelete();
            $table->string('role');
            $table->string('obligation_type');
            $table->string('participation_status')->default('EXPECTED');
            $table->string('attendance_status')->nullable();
            $table->string('reason')->nullable();
            $table->uuid('schedule_change_id')->nullable();
            $table->timestampTz('checkin_at')->nullable();
            $table->timestampTz('checkout_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestampsTz();

            $table->unique(['class_session_id', 'teacher_staff_id']);
            $table->index(['class_session_id', 'participation_status']);
        });

        DB::statement("CREATE UNIQUE INDEX session_teacher_expected_primary_unique
            ON session_teacher_participations (class_session_id)
            WHERE role = 'PRIMARY' AND participation_status = 'EXPECTED'");
    }

    public function down(): void
    {
        Schema::dropIfExists('session_teacher_participations');
    }
};
