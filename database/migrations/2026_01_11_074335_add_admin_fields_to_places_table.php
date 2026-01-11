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
        Schema::table('places', function (Blueprint $table) {
            // Add status column if it doesn't exist
            if (!Schema::hasColumn('places', 'status')) {
                $table->enum('status', ['active', 'inactive'])->default('active')->after('longitude');
            }
            
            // Add category column if it doesn't exist (optional - use 'type' if you prefer)
            if (!Schema::hasColumn('places', 'category')) {
                $table->string('category', 100)->nullable()->after('type');
            }
            
            // Add image_url column if it doesn't exist (can use existing 'image' field instead)
            if (!Schema::hasColumn('places', 'image_url')) {
                $table->string('image_url')->nullable()->after('image');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('places', function (Blueprint $table) {
            if (Schema::hasColumn('places', 'status')) {
                $table->dropColumn('status');
            }
            
            if (Schema::hasColumn('places', 'category')) {
                $table->dropColumn('category');
            }
            
            if (Schema::hasColumn('places', 'image_url')) {
                $table->dropColumn('image_url');
            }
        });
    }
};