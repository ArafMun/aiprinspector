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
        Schema::create('pull_request_reviews', function (Blueprint $table) {
            $table->id();
            $table->string('repo_name');
            $table->integer('pr_number');
            $table->string('commit_sha')->nullable();
            $table->string('action');
            $table->json('payload');
            $table->string('status')->default('pending'); // pending, processing, completed, failed
            $table->integer('files_count')->default(0);
            $table->integer('chunks_count')->default(0);
            $table->integer('processed_chunks')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['repo_name', 'pr_number']);
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pull_request_reviews');
    }
};
