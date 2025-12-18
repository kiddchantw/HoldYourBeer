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
        Schema::create('beer_shop', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beer_id')->constrained('beers')->onDelete('cascade');
            $table->foreignId('shop_id')->constrained('shops')->onDelete('cascade');
            $table->timestamp('first_reported_at')->nullable();
            $table->timestamp('last_reported_at')->nullable();
            $table->unsignedInteger('report_count')->default(1);
            $table->timestamps();

            // Unique constraint to prevent duplicate entries
            $table->unique(['beer_id', 'shop_id']);

            // Indexes for performance
            $table->index('beer_id');
            $table->index('shop_id');
            $table->index('report_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beer_shop');
    }
};
