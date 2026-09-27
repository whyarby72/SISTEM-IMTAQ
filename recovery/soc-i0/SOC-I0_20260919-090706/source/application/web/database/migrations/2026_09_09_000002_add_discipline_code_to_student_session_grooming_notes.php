<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_session_grooming_notes', function (Blueprint $table): void {
            $table->string('discipline_code')->nullable()->after('session_student_participant_id');
        });
    }

    public function down(): void
    {
        Schema::table('student_session_grooming_notes', function (Blueprint $table): void {
            $table->dropColumn('discipline_code');
        });
    }
};
