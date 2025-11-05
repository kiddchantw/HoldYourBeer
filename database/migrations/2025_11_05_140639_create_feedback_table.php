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
        Schema::create('feedback', function (Blueprint $table) {
            $table->id();

            // User relationship
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');

            // Feedback type: feedback, bug_report, feature_request
            $table->enum('type', ['feedback', 'bug_report', 'feature_request'])->default('feedback');

            // Content
            $table->string('title', 255);
            $table->text('description');

            // Priority: low, medium, high, critical
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');

            // Status: new, in_review, in_progress, resolved, closed, rejected
            $table->enum('status', ['new', 'in_review', 'in_progress', 'resolved', 'closed', 'rejected'])->default('new');

            // Contact information (for anonymous feedback)
            $table->string('email', 255)->nullable();
            $table->string('name', 100)->nullable();

            // Admin response
            $table->text('admin_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();

            // Technical information
            $table->string('url', 500)->nullable(); // Page URL where feedback was submitted
            $table->string('browser', 100)->nullable();
            $table->string('device', 100)->nullable();
            $table->string('os', 100)->nullable();
            $table->ipAddress('ip_address')->nullable();

            // Metadata
            $table->json('metadata')->nullable(); // Additional data (screenshots, logs, etc.)

            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('type');
            $table->index('status');
            $table->index('priority');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedback');
    }
};
