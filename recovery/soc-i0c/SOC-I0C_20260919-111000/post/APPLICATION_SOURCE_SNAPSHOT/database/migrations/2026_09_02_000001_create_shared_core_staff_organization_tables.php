<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizational_units', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('unit_code')->unique();
            $table->string('unit_name');
            $table->string('unit_type');
            $table->uuid('parent_unit_id')->nullable();
            $table->date('effective_from')->nullable();
            $table->date('effective_until')->nullable();
            $table->string('record_status')->default('ACTIVE');
            $table->unsignedInteger('version_no')->default(1);
            $table->timestampsTz();

            $table->index(['record_status', 'effective_from']);
        });

        Schema::table('organizational_units', function (Blueprint $table): void {
            $table->foreign('parent_unit_id')
                ->references('id')
                ->on('organizational_units')
                ->restrictOnDelete();
        });

        Schema::create('locations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('location_code')->unique();
            $table->string('location_name');
            $table->string('location_type');
            $table->foreignUuid('organizational_unit_id')
                ->nullable()
                ->constrained('organizational_units')
                ->restrictOnDelete();
            $table->text('address_text')->nullable();
            $table->date('effective_from')->nullable();
            $table->date('effective_until')->nullable();
            $table->string('record_status')->default('ACTIVE');
            $table->unsignedInteger('version_no')->default(1);
            $table->timestampsTz();

            $table->index(['record_status', 'effective_from']);
        });

        Schema::create('staff', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('staff_code')->unique();
            $table->string('full_name');
            $table->string('record_status')->default('ACTIVE');
            $table->date('active_from')->nullable();
            $table->date('active_until')->nullable();
            $table->unsignedInteger('version_no')->default(1);
            $table->timestampsTz();

            $table->index(['record_status', 'active_from']);
        });

        Schema::create('staff_organizational_assignments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('staff_id')->constrained('staff')->restrictOnDelete();
            $table->foreignUuid('organizational_unit_id')
                ->constrained('organizational_units')
                ->restrictOnDelete();
            $table->string('assignment_type');
            $table->boolean('is_primary')->default(false);
            $table->date('effective_from');
            $table->date('effective_until')->nullable();
            $table->string('source_reference')->nullable();
            $table->unsignedInteger('version_no')->default(1);
            $table->timestampsTz();

            $table->index(['staff_id', 'effective_from']);
            $table->index(['organizational_unit_id', 'effective_from']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_organizational_assignments');
        Schema::dropIfExists('staff');
        Schema::dropIfExists('locations');
        Schema::dropIfExists('organizational_units');
    }
};
