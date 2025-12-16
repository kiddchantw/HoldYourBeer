<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tasting_logs', function (Blueprint $table) {
            // Compound index for user-specific time-range queries
            // Optimizes queries filtering by user_beer_count_id + tasted_at
            $table->index(['user_beer_count_id', 'tasted_at'], 'idx_tasting_logs_user_time');

            // Single index for global time-range queries
            // Optimizes queries filtering by tasted_at only
            $table->index('tasted_at', 'idx_tasting_logs_tasted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasting_logs', function (Blueprint $table) {
            $table->dropIndex('idx_tasting_logs_user_time');
            $table->dropIndex('idx_tasting_logs_tasted_at');
        });
    }
};
