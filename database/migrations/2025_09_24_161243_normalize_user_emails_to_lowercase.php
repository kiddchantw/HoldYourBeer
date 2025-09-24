<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 將所有現有的 email 轉為小寫
        DB::statement('UPDATE users SET email = LOWER(email)');

        // 檢查是否有重複的 email (大小寫不同但實際相同)
        $duplicates = DB::select('
            SELECT LOWER(email) as email, COUNT(*) as count
            FROM users
            GROUP BY LOWER(email)
            HAVING COUNT(*) > 1
        ');

        if (!empty($duplicates)) {
            // 記錄重複的 emails
            foreach ($duplicates as $duplicate) {
                \Log::warning("Found duplicate email after normalization: {$duplicate->email} (count: {$duplicate->count})");
            }

            // 對於重複的 emails，保留最新的一個，刪除其他的
            foreach ($duplicates as $duplicate) {
                $keepUserId = DB::table('users')
                    ->where('email', $duplicate->email)
                    ->orderBy('created_at', 'desc')
                    ->first()->id;

                DB::table('users')
                    ->where('email', $duplicate->email)
                    ->where('id', '!=', $keepUserId)
                    ->delete();

                \Log::info("Kept user ID {$keepUserId} for email: {$duplicate->email}");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 無法逆轉這個操作，因為我們無法復原原始的大小寫
        // 但這通常不是問題，因為 email 地址應該不區分大小寫
    }
};
