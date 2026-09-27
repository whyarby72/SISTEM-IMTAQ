<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_period_locks', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('class_id')->constrained('classes')->restrictOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->string('status')->default('OPEN');
            $table->foreignId('locked_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestampTz('locked_at')->nullable();
            $table->unsignedInteger('version_no')->default(1);
            $table->timestampsTz();

            $table->unique(['class_id', 'period_start', 'period_end']);
            $table->index(['class_id', 'status', 'period_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_period_locks');
    }
};
