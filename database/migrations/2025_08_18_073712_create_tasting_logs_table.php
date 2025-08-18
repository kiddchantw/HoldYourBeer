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
        Schema::create('tasting_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_beer_count_id')->constrained()->onDelete('cascade');
            $table->string('action');
            $table->timestamp('tasted_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasting_logs');
    }
};
