<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_calendar_events', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('academic_year_id')->constrained('academic_years')->restrictOnDelete();
            $table->string('event_type');
            $table->string('title');
            $table->timestampTz('start_at');
            $table->timestampTz('end_at');
            $table->foreignUuid('organizational_unit_id')->nullable()->constrained('organizational_units')->restrictOnDelete();
            $table->foreignUuid('class_id')->nullable()->constrained('classes')->restrictOnDelete();
            $table->string('regular_session_policy');
            $table->text('notes')->nullable();
            $table->string('workflow_status');
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestampsTz();

            $table->index(['academic_year_id', 'start_at']);
            $table->index(['organizational_unit_id', 'start_at']);
            $table->index(['class_id', 'start_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_calendar_events');
    }
};
