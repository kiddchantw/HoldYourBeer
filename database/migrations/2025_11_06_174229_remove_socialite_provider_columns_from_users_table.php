<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Removes socialite-related columns that are no longer needed.
     * We now use email as the unique identifier for OAuth users.
     */
    public function up(): void
    {
        // Check if columns exist before trying to drop them
        // (They may not exist in fresh installations after 2025-11-06)
        if (!Schema::hasColumn('users', 'google_id')) {
            return; // Columns already removed or never existed
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            // SQLite requires special handling for dropping columns with indexes
            DB::statement('PRAGMA foreign_keys = OFF');

            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn(['provider', 'provider_id', 'google_id', 'apple_id']);
            });

            DB::statement('PRAGMA foreign_keys = ON');
        } else {
            // For MySQL/PostgreSQL, drop indexes first
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique(['google_id']);
                $table->dropUnique(['apple_id']);
            });

            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn(['provider', 'provider_id', 'google_id', 'apple_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('provider')->nullable();
            $table->string('provider_id')->nullable();
            $table->string('google_id')->nullable()->unique();
            $table->string('apple_id')->nullable()->unique();
        });
    }
};
