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
        Schema::table('feedback', function (Blueprint $table) {
            // Drop unnecessary columns
            $table->dropColumn(['email', 'name', 'url', 'title']);

            // Add SoftDeletes
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feedback', function (Blueprint $table) {
            // Add columns back
            $table->string('email', 255)->nullable();
            $table->string('name', 100)->nullable();
            $table->string('url', 500)->nullable();
            $table->string('title', 255)->nullable()->after('type'); // Add back as nullable because we can't recover data

            $table->dropSoftDeletes();
        });
    }
};
