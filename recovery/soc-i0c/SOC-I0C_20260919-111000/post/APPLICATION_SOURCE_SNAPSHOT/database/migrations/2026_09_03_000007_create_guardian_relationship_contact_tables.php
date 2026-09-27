<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guardians', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('guardian_code')->unique();
            $table->string('full_name');
            $table->string('record_status')->default('ACTIVE');
            $table->unsignedInteger('version_no')->default(1);
            $table->timestampsTz();
        });

        Schema::create('student_guardian_relationships', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('students')->restrictOnDelete();
            $table->foreignUuid('guardian_id')->constrained('guardians')->restrictOnDelete();
            $table->string('relationship_type');
            $table->date('effective_from')->nullable();
            $table->date('effective_until')->nullable();
            $table->string('relationship_status')->default('ACTIVE');
            $table->unsignedSmallInteger('communication_priority')->nullable();
            $table->boolean('authorized_for_parent_reports')->default(false);
            $table->string('source_reference')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedInteger('version_no')->default(1);
            $table->timestampsTz();

            $table->index(['student_id', 'relationship_status']);
            $table->index(['guardian_id', 'relationship_status']);
        });

        Schema::create('guardian_contact_channels', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('guardian_id')->constrained('guardians')->restrictOnDelete();
            $table->string('channel_type');
            $table->string('normalized_value');
            $table->string('display_value')->nullable();
            $table->string('verification_status')->default('UNVERIFIED');
            $table->boolean('is_primary')->default(false);
            $table->date('active_from')->nullable();
            $table->date('active_until')->nullable();
            $table->foreignId('verified_by_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestampTz('verified_at')->nullable();
            $table->string('source_reference')->nullable();
            $table->unsignedInteger('version_no')->default(1);
            $table->timestampsTz();

            $table->index(['guardian_id', 'channel_type', 'active_until']);
            $table->index(['channel_type', 'normalized_value']);
        });

        DB::statement("CREATE UNIQUE INDEX student_guardian_active_relationship_unique
            ON student_guardian_relationships (student_id, guardian_id, relationship_type)
            WHERE relationship_status = 'ACTIVE'");
        DB::statement('CREATE UNIQUE INDEX guardian_active_primary_channel_unique
            ON guardian_contact_channels (guardian_id, channel_type)
            WHERE is_primary = TRUE AND active_until IS NULL');
    }

    public function down(): void
    {
        Schema::dropIfExists('guardian_contact_channels');
        Schema::dropIfExists('student_guardian_relationships');
        Schema::dropIfExists('guardians');
    }
};
