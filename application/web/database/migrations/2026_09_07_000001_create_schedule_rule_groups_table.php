<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedule_rule_groups', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('schedule_rule_id')->constrained('schedule_rules')->cascadeOnDelete();
            $table->foreignUuid('class_id')->constrained('classes')->restrictOnDelete();
            $table->string('scope_role')->default('TEACHING_SCOPE');
            $table->timestampsTz();

            $table->unique(['schedule_rule_id', 'class_id']);
            $table->index(['class_id', 'scope_role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_rule_groups');
    }
};
