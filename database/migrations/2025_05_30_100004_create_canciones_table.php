<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('canciones', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('duration');
            $table->unsignedBigInteger('album_id');
            $table->unsignedBigInteger('artist_id');
            $table->unsignedBigInteger('genre_id');
            $table->integer('play_count')->default(0);
            $table->string('cover_image')->nullable();
            $table->timestamps();

            $table->foreign('album_id')->references('id')->on('albums');
            $table->foreign('artist_id')->references('id')->on('artistas');
            $table->foreign('genre_id')->references('id')->on('generos');
        });
    }

    public function down()
    {
        Schema::dropIfExists('canciones');
    }
};