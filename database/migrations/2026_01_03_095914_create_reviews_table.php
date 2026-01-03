<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;  // Added "use" keyword here
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('place_id');
            $table->unsignedBigInteger('user_id');
            $table->integer('rating'); // 1-5 stars
            $table->text('comment');
            $table->timestamps();
            
            // Add indexes for better performance
            $table->index(['place_id', 'created_at']);
            $table->index('user_id');
            
            // Foreign keys (optional, if you have users table)
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};