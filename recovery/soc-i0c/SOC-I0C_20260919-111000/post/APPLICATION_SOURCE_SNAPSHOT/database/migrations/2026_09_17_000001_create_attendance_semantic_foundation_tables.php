<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_source_certifications', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('source_type');
            $table->string('period', 7);
            $table->string('scope_type');
            $table->string('scope_key');
            $table->string('metric')->default('ATTENDANCE');
            $table->string('source_reference');
            $table->string('certification_status')->default('PENDING');
            $table->foreignId('certified_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestampTz('certified_at')->nullable();
            $table->text('certification_reason')->nullable();
            $table->string('evidence_reference')->nullable();
            $table->foreignId('revoked_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestampTz('revoked_at')->nullable();
            $table->text('revocation_reason')->nullable();
            $table->timestampsTz();

            $table->index(['period', 'scope_type', 'scope_key', 'metric']);
            $table->index(['source_type', 'certification_status']);
        });

        Schema::create('class_lineage_mappings', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('source_class_reference');
            $table->foreignUuid('target_class_id')->constrained('classes')->restrictOnDelete();
            $table->string('mapping_context');
            $table->date('effective_from');
            $table->date('effective_until')->nullable();
            $table->string('status')->default('PENDING');
            $table->foreignId('approved_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestampTz('approved_at')->nullable();
            $table->text('approval_reason')->nullable();
            $table->timestampsTz();

            $table->index(['source_class_reference', 'mapping_context', 'effective_from']);
            $table->index(['target_class_id', 'mapping_context']);
        });

        Schema::table('session_student_participants', function (Blueprint $table): void {
            $table->string('eligibility_status')->nullable()->after('is_required');
            $table->string('non_eligible_reason')->nullable()->after('eligibility_status');
            $table->index(['eligibility_status', 'class_session_id']);
        });
    }

    public function down(): void
    {
        Schema::table('session_student_participants', function (Blueprint $table): void {
            $table->dropIndex(['eligibility_status', 'class_session_id']);
            $table->dropColumn(['eligibility_status', 'non_eligible_reason']);
        });
        Schema::dropIfExists('class_lineage_mappings');
        Schema::dropIfExists('attendance_source_certifications');
    }
};
