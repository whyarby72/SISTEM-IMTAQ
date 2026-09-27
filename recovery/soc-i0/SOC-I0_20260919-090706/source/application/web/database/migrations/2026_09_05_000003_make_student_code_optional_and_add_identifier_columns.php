<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table): void {
            $table->string('nis')->nullable()->unique();
            $table->string('nisn')->nullable()->unique();
        });

        DB::statement('ALTER TABLE students ALTER COLUMN student_code DROP NOT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE students ALTER COLUMN student_code SET NOT NULL');
        Schema::table('students', function (Blueprint $table): void {
            $table->dropUnique(['nis']);
            $table->dropUnique(['nisn']);
            $table->dropColumn(['nis', 'nisn']);
        });
    }
};
