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
        Schema::create('user_beer_counts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade')->onUpdate('cascade')->comment('用戶ID');
            $table->foreignId('beer_id')->constrained()->onDelete('cascade')->onUpdate('cascade')->comment('啤酒ID');
            $table->integer('count')->default(0)->comment('品飲次數');
            $table->timestamp('last_tasted_at')->nullable()->comment('最後品飲時間');
            $table->timestamps();
            
            $table->unique(['user_id', 'beer_id'], 'user_beer_counts_user_beer_unique');
            $table->index(['user_id']);
            $table->index(['last_tasted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_beer_counts');
    }
};