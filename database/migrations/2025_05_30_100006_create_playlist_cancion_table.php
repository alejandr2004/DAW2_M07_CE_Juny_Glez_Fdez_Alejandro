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
        Schema::create('playlist_cancion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('playlist_id')->constrained()->onDelete('cascade');
            $table->foreignId('song_id')->constrained('canciones')->onDelete('cascade');
            $table->integer('order')->default(0);
            $table->timestamps();
            
            $table->unique(['playlist_id', 'song_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('playlist_cancion');
    }
};
