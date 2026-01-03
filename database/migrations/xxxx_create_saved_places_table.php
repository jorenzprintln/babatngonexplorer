<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_places', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('place_id');
            $table->timestamps();
            
            // Prevent duplicate saves
            $table->unique(['user_id', 'place_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_places');
    }
};