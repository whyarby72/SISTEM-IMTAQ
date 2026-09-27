<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_card_notes', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('report_card_version_id')->constrained('report_card_versions')->restrictOnDelete();
            $table->text('note_text');
            $table->boolean('approved_for_parent_report')->default(false);
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->constrained('users')->restrictOnDelete();
            $table->timestampsTz();
            $table->unique('report_card_version_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_card_notes');
    }
};
