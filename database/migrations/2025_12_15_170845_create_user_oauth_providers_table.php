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
        Schema::create('user_oauth_providers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('provider');           // 'google', 'apple', 'facebook'
            $table->string('provider_id');        // OAuth provider's user ID
            $table->string('provider_email')->nullable(); // OAuth 帳號的 email
            $table->timestamp('linked_at');
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            // 確保同一個 OAuth 帳號只能連結一次
            $table->unique(['provider', 'provider_id'], 'unique_provider_account');

            // 加速查詢
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_oauth_providers');
    }
};
