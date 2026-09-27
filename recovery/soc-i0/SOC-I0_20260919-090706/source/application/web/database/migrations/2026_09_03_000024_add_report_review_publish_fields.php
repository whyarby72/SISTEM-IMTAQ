<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('report_card_versions', function (Blueprint $table): void {
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestampTz('reviewed_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestampTz('approved_at')->nullable();
            $table->foreignId('published_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestampTz('published_at')->nullable();
        });

        Schema::create('report_card_signatories', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('report_card_version_id')->constrained('report_card_versions')->restrictOnDelete();
            $table->string('signatory_role');
            $table->foreignUuid('staff_id')->nullable()->constrained('staff')->restrictOnDelete();
            $table->string('name_snapshot');
            $table->string('title_snapshot');
            $table->timestampsTz();
            $table->unique(['report_card_version_id', 'signatory_role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_card_signatories');
        Schema::table('report_card_versions', function (Blueprint $table): void {
            $table->dropForeign(['reviewed_by']);
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['published_by']);
            $table->dropColumn(['reviewed_by', 'reviewed_at', 'approved_by', 'approved_at', 'published_by', 'published_at']);
        });
    }
};
