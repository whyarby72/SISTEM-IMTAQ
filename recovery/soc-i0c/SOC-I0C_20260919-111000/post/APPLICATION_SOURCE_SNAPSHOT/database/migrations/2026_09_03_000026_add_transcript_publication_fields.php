<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academic_transcript_versions', function (Blueprint $table): void {
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestampTz('reviewed_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestampTz('approved_at')->nullable();
            $table->foreignId('published_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestampTz('published_at')->nullable();
        });

        Schema::create('academic_transcript_signatories', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('academic_transcript_version_id')->constrained('academic_transcript_versions')->restrictOnDelete();
            $table->string('signatory_role');
            $table->foreignUuid('staff_id')->nullable()->constrained('staff')->restrictOnDelete();
            $table->string('name_snapshot');
            $table->string('title_snapshot');
            $table->timestampsTz();
            $table->unique(['academic_transcript_version_id', 'signatory_role'], 'transcript_signatories_version_role_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_transcript_signatories');
        Schema::table('academic_transcript_versions', function (Blueprint $table): void {
            $table->dropForeign(['reviewed_by']);
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['published_by']);
            $table->dropColumn(['reviewed_by', 'reviewed_at', 'approved_by', 'approved_at', 'published_by', 'published_at']);
        });
    }
};
