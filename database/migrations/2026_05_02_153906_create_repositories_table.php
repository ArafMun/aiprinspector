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
        Schema::create('repositories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g., "username/repo-name"
            $table->string('full_name')->unique(); // e.g., "username/repo-name"
            $table->string('url')->nullable(); // Repository URL
            $table->string('description')->nullable();
            $table->string('default_branch')->default('main');
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable(); // Additional repository data
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repositories');
    }
};
