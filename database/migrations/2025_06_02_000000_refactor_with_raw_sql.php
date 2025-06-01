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
        // 1. Añadir campos artist a las tablas
        if (!Schema::hasColumn('canciones', 'artist')) {
            DB::statement('ALTER TABLE canciones ADD COLUMN artist VARCHAR(255) AFTER title');
        }
        
        if (!Schema::hasColumn('albums', 'artist')) {
            DB::statement('ALTER TABLE albums ADD COLUMN artist VARCHAR(255) AFTER title');
        }
        
        // 2. Copiar datos de artistas
        $artists = DB::table('artistas')->get();
        $artistMap = [];
        
        foreach ($artists as $artist) {
            $artistMap[$artist->id] = $artist->nombre ?? '';
        }
        
        // Transferir datos a canciones
        $songs = DB::table('canciones')->get();
        foreach ($songs as $song) {
            if (isset($song->artist_id) && isset($artistMap[$song->artist_id])) {
                DB::table('canciones')
                    ->where('id', $song->id)
                    ->update(['artist' => $artistMap[$song->artist_id]]);
            }
        }
        
        // Transferir datos a albums
        $albums = DB::table('albums')->get();
        foreach ($albums as $album) {
            if (isset($album->artist_id) && isset($artistMap[$album->artist_id])) {
                DB::table('albums')
                    ->where('id', $album->id)
                    ->update(['artist' => $artistMap[$album->artist_id]]);
            }
        }
        
        // 3. Eliminar campos innecesarios
        if (Schema::hasColumn('canciones', 'artist_id')) {
            DB::statement('ALTER TABLE canciones DROP COLUMN artist_id');
        }
        
        if (Schema::hasColumn('albums', 'artist_id')) {
            DB::statement('ALTER TABLE albums DROP COLUMN artist_id');
        }
        
        if (Schema::hasColumn('canciones', 'audio_url')) {
            DB::statement('ALTER TABLE canciones DROP COLUMN audio_url');
        }
        
        if (Schema::hasColumn('canciones', 'play_count')) {
            DB::statement('ALTER TABLE canciones DROP COLUMN play_count');
        }
        
        // 4. Eliminar tabla artistas
        if (Schema::hasTable('artistas')) {
            Schema::dropIfExists('artistas');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Recrear tabla artistas
        if (!Schema::hasTable('artistas')) {
            Schema::create('artistas', function (Blueprint $table) {
                $table->id();
                $table->string('nombre');
                $table->text('biografia')->nullable();
                $table->string('imagen')->nullable();
                $table->string('pais')->nullable();
                $table->timestamps();
            });
        }
        
        // 2. Añadir columnas eliminadas a canciones
        if (!Schema::hasColumn('canciones', 'audio_url')) {
            DB::statement('ALTER TABLE canciones ADD COLUMN audio_url VARCHAR(255) NULL');
        }
        
        if (!Schema::hasColumn('canciones', 'play_count')) {
            DB::statement('ALTER TABLE canciones ADD COLUMN play_count INT DEFAULT 0');
        }
        
        // 3. Restaurar claves foráneas
        if (!Schema::hasColumn('canciones', 'artist_id')) {
            DB::statement('ALTER TABLE canciones ADD COLUMN artist_id BIGINT UNSIGNED AFTER title');
        }
        
        if (!Schema::hasColumn('albums', 'artist_id')) {
            DB::statement('ALTER TABLE albums ADD COLUMN artist_id BIGINT UNSIGNED AFTER title');
        }
        
        // 4. Eliminar campos artist string
        if (Schema::hasColumn('canciones', 'artist')) {
            DB::statement('ALTER TABLE canciones DROP COLUMN artist');
        }
        
        if (Schema::hasColumn('albums', 'artist')) {
            DB::statement('ALTER TABLE albums DROP COLUMN artist');
        }
    }
};
