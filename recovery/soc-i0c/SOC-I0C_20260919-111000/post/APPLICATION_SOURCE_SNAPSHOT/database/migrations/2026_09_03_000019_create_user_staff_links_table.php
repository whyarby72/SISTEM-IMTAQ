<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_staff_links', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->unique()->constrained('users')->restrictOnDelete();
            $table->foreignUuid('staff_id')->unique()->constrained('staff')->restrictOnDelete();
            $table->date('effective_from')->nullable();
            $table->date('effective_until')->nullable();
            $table->foreignId('linked_by_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestampsTz();

            $table->index(['user_id', 'effective_from', 'effective_until']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_staff_links');
    }
};
