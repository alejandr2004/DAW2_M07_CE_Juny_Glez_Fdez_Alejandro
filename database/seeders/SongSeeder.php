<?php

namespace Database\Seeders;

use App\Models\Song;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SongSeeder extends Seeder
{
    public function run()
    {
        $songs = [];
        $albums = \App\Models\Album::all();
        
        // Títulos de canciones genéricos para usar en el seeder
        $songTitles = [
            'Amanecer',
            'Sueño Eterno',
            'Horizontes',
            'Luna Llena',
            'Corazón de Cristal',
            'Tiempo de Vivir', 
            'Camino Infinito',
            'Viento Libre',
            'Mar de Emociones',
            'Melodia Nocturna',
            'Esperanza',
            'Fuego Interior',
            'Destino Final',
            'Nueva Era',
            'Luz Eterna',
            'Recuerdos',
            'En Silencio',
            'Alma Perdida',
            'Libertad',
            'Eclipse'            
        ];
        
        foreach ($albums as $album) {
            // Mezclar los títulos para cada álbum
            shuffle($songTitles);
            
            for ($i = 0; $i < 5; $i++) {
                $songs[] = [
                    'title' => $songTitles[$i],
                    'duration' => rand(2, 5).':'.str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT),
                    'album_id' => $album->id,
                    'artist_id' => $album->artist_id,
                    'genre_id' => $album->genre_id,
                    'play_count' => rand(0, 10000),
                    'cover_image' => 'songs/default.jpg',
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
        }

        DB::table('canciones')->insert($songs);
    }
}