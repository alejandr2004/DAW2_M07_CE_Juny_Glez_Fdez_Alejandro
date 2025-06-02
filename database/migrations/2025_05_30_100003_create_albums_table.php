<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('albums', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->date('release_date');
            $table->unsignedBigInteger('artist_id');
            $table->unsignedBigInteger('genre_id');
            $table->string('cover_image')->nullable(); // Columna añadida
            $table->timestamps();

            $table->foreign('artist_id')->references('id')->on('artistas');
            $table->foreign('genre_id')->references('id')->on('generos');
        });
    }

    public function down()
    {
        Schema::dropIfExists('albums');
    }
};