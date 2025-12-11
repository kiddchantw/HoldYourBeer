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
        Schema::table('users', function (Blueprint $table) {
            // Add provider field to track authentication method
            // null = legacy users (before this migration)
            // 'local' = traditional email/password registration
            // 'google', 'apple', 'facebook' = OAuth providers
            $table->string('provider')->nullable()->after('password');

            // Add provider_id to store OAuth provider's user ID
            // This helps prevent account conflicts and enables OAuth account linking
            $table->string('provider_id')->nullable()->after('provider');

            // Add index for faster OAuth lookups
            $table->index(['provider', 'provider_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['provider', 'provider_id']);
            $table->dropColumn(['provider', 'provider_id']);
        });
    }
};
