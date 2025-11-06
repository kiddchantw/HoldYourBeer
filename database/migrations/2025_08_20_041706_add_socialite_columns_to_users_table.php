<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Note: These columns are no longer used as of 2025-11-06.
     * This migration is kept for historical purposes but does nothing.
     */
    public function up(): void
    {
        // No longer needed - using email as unique identifier
        // See: 2025_11_06_174229_remove_socialite_provider_columns_from_users_table.php
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No longer needed
    }
};
