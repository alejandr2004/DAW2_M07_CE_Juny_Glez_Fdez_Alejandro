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
        // 1. Añadir columnas de artista a las tablas canciones y albums
        if (!Schema::hasColumn('canciones', 'artist')) {
            Schema::table('canciones', function (Blueprint $table) {
                $table->string('artist')->nullable()->after('title');
            });
        }
        
        if (!Schema::hasColumn('albums', 'artist')) {
            Schema::table('albums', function (Blueprint $table) {
                $table->string('artist')->nullable()->after('title');
            });
        }
        
        // 2. Quitar columnas no necesarias de canciones
        if (Schema::hasColumn('canciones', 'audio_url')) {
            Schema::table('canciones', function (Blueprint $table) {
                $table->dropColumn('audio_url');
            });
        }
        
        if (Schema::hasColumn('canciones', 'play_count')) {
            Schema::table('canciones', function (Blueprint $table) {
                $table->dropColumn('play_count');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Restaurar columnas eliminadas de canciones
        Schema::table('canciones', function (Blueprint $table) {
            $table->string('audio_url')->nullable();
            $table->integer('play_count')->default(0);
        });
        
        // 2. Eliminar columnas de artista
        Schema::table('canciones', function (Blueprint $table) {
            $table->dropColumn('artist');
        });
        
        Schema::table('albums', function (Blueprint $table) {
            $table->dropColumn('artist');
        });
    }
};
