<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Remove unused provider and provider_id columns from users table.
     * OAuth information is now stored in the user_oauth_providers table.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // First, try to drop the index if it exists
            // Use try-catch because the index might not exist in all environments
            try {
                $table->dropIndex('users_provider_provider_id_index');
            } catch (\Exception $e) {
                // Index doesn't exist, continue
            }
            
            // Then drop the columns if they exist
            if (Schema::hasColumn('users', 'provider')) {
                $table->dropColumn('provider');
            }
            
            if (Schema::hasColumn('users', 'provider_id')) {
                $table->dropColumn('provider_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     * 
     * Re-add the provider columns if needed for rollback.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Re-add columns only if they don't exist
            if (!Schema::hasColumn('users', 'provider')) {
                $table->string('provider')->nullable()->after('email_verified_at');
            }
            
            if (!Schema::hasColumn('users', 'provider_id')) {
                $table->string('provider_id')->nullable()->after('provider');
            }
        });
    }
};
