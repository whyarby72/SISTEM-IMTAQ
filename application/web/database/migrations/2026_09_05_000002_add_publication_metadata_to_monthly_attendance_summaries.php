<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monthly_attendance_summaries', function (Blueprint $table): void {
            $table->foreignId('published_by_user_id')->nullable()->after('status')->constrained('users')->restrictOnDelete();
            $table->timestampTz('published_at')->nullable()->after('published_by_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('monthly_attendance_summaries', function (Blueprint $table): void {
            $table->dropForeign(['published_by_user_id']);
            $table->dropColumn(['published_by_user_id', 'published_at']);
        });
    }
};
