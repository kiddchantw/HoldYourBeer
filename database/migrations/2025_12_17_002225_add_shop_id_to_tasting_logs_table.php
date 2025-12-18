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
        Schema::table('tasting_logs', function (Blueprint $table) {
            $table->foreignId('shop_id')
                ->nullable()
                ->constrained('shops')
                ->onDelete('set null')
                ->after('note');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasting_logs', function (Blueprint $table) {
            $table->dropForeignIdFor('shops', 'shop_id');
            $table->dropColumn('shop_id');
        });
    }
};
