<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_years', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('year_code')->unique();
            $table->string('display_name');
            $table->date('starts_on');
            $table->date('ends_on');
            $table->string('status')->default('ACTIVE');
            $table->unsignedInteger('version_no')->default(1);
            $table->timestampsTz();

            $table->index(['status', 'starts_on']);
        });

        Schema::create('grade_levels', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('organizational_unit_id')->nullable()->constrained('organizational_units')->restrictOnDelete();
            $table->string('level_code');
            $table->string('display_name');
            $table->unsignedSmallInteger('sequence_no');
            $table->string('status')->default('ACTIVE');
            $table->unsignedInteger('version_no')->default(1);
            $table->timestampsTz();

            $table->unique(['organizational_unit_id', 'level_code']);
        });

        Schema::create('classes', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('class_code')->unique();
            $table->foreignUuid('academic_year_id')->constrained('academic_years')->restrictOnDelete();
            $table->foreignUuid('organizational_unit_id')->constrained('organizational_units')->restrictOnDelete();
            $table->foreignUuid('grade_level_id')->constrained('grade_levels')->restrictOnDelete();
            $table->string('section_code');
            $table->string('display_name');
            $table->string('status')->default('ACTIVE');
            $table->unsignedInteger('version_no')->default(1);
            $table->timestampsTz();

            $table->unique(['academic_year_id', 'organizational_unit_id', 'grade_level_id', 'section_code'], 'classes_year_unit_grade_section_unique');
            $table->index(['academic_year_id', 'status']);
        });

        Schema::create('subjects', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('subject_code')->unique();
            $table->string('subject_name');
            $table->string('status')->default('ACTIVE');
            $table->unsignedInteger('version_no')->default(1);
            $table->timestampsTz();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('classes');
        Schema::dropIfExists('grade_levels');
        Schema::dropIfExists('academic_years');
    }
};
