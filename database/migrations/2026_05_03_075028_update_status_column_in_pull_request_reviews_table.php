<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, update existing data to use integer values
        DB::table('pull_request_reviews')->update([
            'status' => DB::raw('CASE status
                WHEN "pending" THEN 0
                WHEN "processing" THEN 1
                WHEN "completed" THEN 2
                WHEN "partial" THEN 3
                WHEN "failed" THEN 4
                ELSE 0
            END')
        ]);

        // Then change the column type from string to tinyint
        Schema::table('pull_request_reviews', function (Blueprint $table) {
            $table->tinyInteger('status')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Convert back to string values before changing column type
        DB::table('pull_request_reviews')->update([
            'status' => DB::raw('CASE status
                WHEN 0 THEN "pending"
                WHEN 1 THEN "processing"
                WHEN 2 THEN "completed"
                WHEN 3 THEN "partial"
                WHEN 4 THEN "failed"
                ELSE "pending"
            END')
        ]);

        Schema::table('pull_request_reviews', function (Blueprint $table) {
            // Change status column back to string
            $table->string('status')->default('pending')->change();
        });
    }
};
