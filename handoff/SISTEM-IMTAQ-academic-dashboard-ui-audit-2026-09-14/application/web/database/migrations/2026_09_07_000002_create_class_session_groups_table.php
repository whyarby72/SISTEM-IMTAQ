<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_session_groups', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('class_session_id')->constrained('class_sessions')->cascadeOnDelete();
            $table->foreignUuid('class_id')->constrained('classes')->restrictOnDelete();
            $table->string('scope_role')->default('TEACHING_SCOPE');
            $table->timestampsTz();

            $table->unique(['class_session_id', 'class_id']);
            $table->index(['class_id', 'scope_role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_session_groups');
    }
};
