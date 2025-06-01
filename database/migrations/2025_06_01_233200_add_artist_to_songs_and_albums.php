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
        // Primero, obtenemos todos los datos de artistas para migrarlos
        $artists = DB::table('artistas')->get();
        $artistMap = [];
        
        foreach ($artists as $artist) {
            if (isset($artist->nombre)) {
                $artistMap[$artist->id] = $artist->nombre;
            } else if (isset($artist->name)) {
                $artistMap[$artist->id] = $artist->name;
            }
        }
        
        // Añadir campo artista a canciones
        Schema::table('canciones', function (Blueprint $table) {
            $table->string('artist')->after('title')->nullable();
        });
        
        // Añadir campo artista a albums
        Schema::table('albums', function (Blueprint $table) {
            $table->string('artist')->after('title')->nullable();
        });
        
        // Migrar datos de artistas a canciones
        foreach (DB::table('canciones')->get() as $song) {
            if (isset($song->artist_id) && isset($artistMap[$song->artist_id])) {
                DB::table('canciones')
                    ->where('id', $song->id)
                    ->update(['artist' => $artistMap[$song->artist_id]]);
            }
        }
        
        // Migrar datos de artistas a albums
        foreach (DB::table('albums')->get() as $album) {
            if (isset($album->artist_id) && isset($artistMap[$album->artist_id])) {
                DB::table('albums')
                    ->where('id', $album->id)
                    ->update(['artist' => $artistMap[$album->artist_id]]);
            }
        }
        
        // Eliminar clave foránea de artist_id en canciones
        Schema::table('canciones', function (Blueprint $table) {
            // Comprobar si existe la restricción de clave foránea
            if (Schema::hasColumn('canciones', 'artist_id')) {
                // El nombre de la clave foránea puede variar dependiendo de cómo se creó
                $foreignKeys = Schema::getConnection()->getDoctrineSchemaManager()->listTableForeignKeys('canciones');
                $artistForeignKey = null;
                
                foreach ($foreignKeys as $foreignKey) {
                    if (in_array('artist_id', $foreignKey->getLocalColumns())) {
                        $artistForeignKey = $foreignKey->getName();
                        break;
                    }
                }
                
                if ($artistForeignKey) {
                    $table->dropForeign($artistForeignKey);
                }
                
                $table->dropColumn('artist_id');
            }
        });
        
        // Eliminar clave foránea de artist_id en albums
        Schema::table('albums', function (Blueprint $table) {
            // Comprobar si existe la restricción de clave foránea
            if (Schema::hasColumn('albums', 'artist_id')) {
                // El nombre de la clave foránea puede variar dependiendo de cómo se creó
                $foreignKeys = Schema::getConnection()->getDoctrineSchemaManager()->listTableForeignKeys('albums');
                $artistForeignKey = null;
                
                foreach ($foreignKeys as $foreignKey) {
                    if (in_array('artist_id', $foreignKey->getLocalColumns())) {
                        $artistForeignKey = $foreignKey->getName();
                        break;
                    }
                }
                
                if ($artistForeignKey) {
                    $table->dropForeign($artistForeignKey);
                }
                
                $table->dropColumn('artist_id');
            }
        });
        
        // Eliminar columnas innecesarias en canciones
        Schema::table('canciones', function (Blueprint $table) {
            if (Schema::hasColumn('canciones', 'audio_url')) {
                $table->dropColumn('audio_url');
            }
            
            if (Schema::hasColumn('canciones', 'play_count')) {
                $table->dropColumn('play_count');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar columna artista de canciones
        Schema::table('canciones', function (Blueprint $table) {
            $table->dropColumn('artist');
        });
        
        // Eliminar columna artista de albums
        Schema::table('albums', function (Blueprint $table) {
            $table->dropColumn('artist');
        });
        
        // Añadir clave foránea de artist_id en canciones
        Schema::table('canciones', function (Blueprint $table) {
            $table->unsignedBigInteger('artist_id')->after('title');
            $table->foreign('artist_id')->references('id')->on('artistas');
        });
        
        // Añadir clave foránea de artist_id en albums
        Schema::table('albums', function (Blueprint $table) {
            $table->unsignedBigInteger('artist_id')->after('title');
            $table->foreign('artist_id')->references('id')->on('artistas');
        });
        
        // Añadir columnas eliminadas a canciones
        Schema::table('canciones', function (Blueprint $table) {
            $table->string('audio_url')->nullable();
            $table->integer('play_count')->default(0);
        });
    }
};
