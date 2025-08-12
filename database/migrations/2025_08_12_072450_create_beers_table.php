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
        Schema::create('beers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained()->onDelete('cascade')->onUpdate('cascade')->comment('品牌ID');
            $table->string('name')->comment('啤酒名稱/系列名');
            $table->string('style', 100)->nullable()->comment('啤酒類型 (如: Stout, IPA)');
            $table->timestamps();
            
            $table->index(['brand_id']);
            $table->unique(['brand_id', 'name'], 'beers_brand_name_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beers');
    }
};