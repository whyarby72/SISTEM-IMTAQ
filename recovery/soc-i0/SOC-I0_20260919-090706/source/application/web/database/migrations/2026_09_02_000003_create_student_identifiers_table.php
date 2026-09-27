<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_identifiers', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('students')->restrictOnDelete();
            $table->string('identifier_type');
            $table->string('identifier_value');
            $table->string('identifier_scope')->nullable();
            $table->string('verification_status')->default('UNVERIFIED');
            $table->string('record_status')->default('ACTIVE');
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->string('source_reference')->nullable();
            $table->unsignedInteger('version_no')->default(1);
            $table->timestampsTz();

            $table->index(['student_id', 'identifier_type', 'record_status']);
            $table->index(['identifier_type', 'identifier_value', 'record_status']);
        });

        DB::statement("CREATE UNIQUE INDEX student_identifiers_active_student_type_no_scope_unique
            ON student_identifiers (student_id, identifier_type)
            WHERE record_status = 'ACTIVE' AND identifier_scope IS NULL");
        DB::statement("CREATE UNIQUE INDEX student_identifiers_active_student_type_scope_unique
            ON student_identifiers (student_id, identifier_type, identifier_scope)
            WHERE record_status = 'ACTIVE' AND identifier_scope IS NOT NULL");
        DB::statement("CREATE UNIQUE INDEX student_identifiers_active_nisn_value_unique
            ON student_identifiers (identifier_value)
            WHERE record_status = 'ACTIVE' AND identifier_type = 'NISN'");
    }

    public function down(): void
    {
        Schema::dropIfExists('student_identifiers');
    }
};
