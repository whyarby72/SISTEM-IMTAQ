<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_homeroom_assignments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('class_id')->constrained('classes')->restrictOnDelete();
            $table->foreignUuid('staff_id')->constrained('staff')->restrictOnDelete();
            $table->date('effective_from');
            $table->date('effective_until')->nullable();
            $table->string('status')->default('ACTIVE');
            $table->foreignId('assigned_by_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestampTz('assigned_at')->nullable();
            $table->string('reason')->nullable();
            $table->unsignedInteger('version_no')->default(1);
            $table->timestampsTz();

            $table->index(['class_id', 'effective_from']);
            $table->index(['staff_id', 'effective_from']);
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('CREATE EXTENSION IF NOT EXISTS btree_gist');
            DB::statement("ALTER TABLE class_homeroom_assignments
                ADD CONSTRAINT class_homeroom_assignments_no_overlap
                EXCLUDE USING gist (
                    class_id WITH =,
                    daterange(effective_from, effective_until, '[)') WITH &&
                )");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('class_homeroom_assignments');
    }
};
