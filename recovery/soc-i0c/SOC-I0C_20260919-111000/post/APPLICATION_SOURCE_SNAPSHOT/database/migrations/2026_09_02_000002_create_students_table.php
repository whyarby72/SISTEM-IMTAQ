<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('student_code')->unique();
            $table->string('full_name');
            $table->string('arabic_name')->nullable();
            $table->string('nickname')->nullable();
            $table->string('gender_code')->nullable();
            $table->string('birth_place')->nullable();
            $table->date('birth_date')->nullable();
            $table->unsignedSmallInteger('entry_year')->nullable();
            $table->unsignedInteger('version_no')->default(1);
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
