<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedule_rules', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('teaching_assignment_id')->constrained('teaching_assignments')->restrictOnDelete();
            $table->unsignedTinyInteger('weekday');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('recurrence_type');
            $table->foreignUuid('location_id')->nullable()->constrained('locations')->restrictOnDelete();
            $table->date('effective_from');
            $table->date('effective_until')->nullable();
            $table->string('workflow_status');
            $table->unsignedInteger('version_no')->default(1);
            $table->timestampsTz();

            $table->index(['teaching_assignment_id', 'effective_from']);
            $table->index(['weekday', 'workflow_status']);
        });

        Schema::create('schedule_rule_week_numbers', function (Blueprint $table): void {
            $table->foreignUuid('schedule_rule_id')->constrained('schedule_rules')->cascadeOnDelete();
            $table->unsignedTinyInteger('week_no');
            $table->primary(['schedule_rule_id', 'week_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_rule_week_numbers');
        Schema::dropIfExists('schedule_rules');
    }
};
