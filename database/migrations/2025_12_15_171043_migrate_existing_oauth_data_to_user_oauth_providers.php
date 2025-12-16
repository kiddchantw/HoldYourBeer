<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrate existing OAuth user data from users table to user_oauth_providers table
        DB::table('users')
            ->whereNotNull('provider')
            ->whereIn('provider', ['google', 'apple', 'facebook'])
            ->whereNotNull('provider_id')
            ->orderBy('id')
            ->chunk(100, function ($users) {
                foreach ($users as $user) {
                    // Check if record already exists to avoid duplicates
                    $exists = DB::table('user_oauth_providers')
                        ->where('user_id', $user->id)
                        ->where('provider', $user->provider)
                        ->where('provider_id', $user->provider_id)
                        ->exists();

                    if (!$exists) {
                        DB::table('user_oauth_providers')->insert([
                            'user_id' => $user->id,
                            'provider' => $user->provider,
                            'provider_id' => $user->provider_id,
                            'provider_email' => $user->email,
                            'linked_at' => $user->created_at ?? now(),
                            'last_used_at' => $user->updated_at ?? now(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Clear migrated data (keep the structure, only remove data)
        DB::table('user_oauth_providers')->truncate();
    }
};
