<?php

namespace Database\Seeders;

use App\Models\Artist;
use App\Models\Song;
use App\Models\Genre;
use App\Models\Album;
use Illuminate\Database\Seeder;

class SongArtistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener todos los artistas que no tienen canciones
        $artistsWithoutSongs = Artist::doesntHave('songs')->get();
        
        // Obtener todos los géneros disponibles
        $genres = Genre::all();
        
        if ($genres->isEmpty()) {
            // Si no hay géneros, crear algunos
            $genreNames = ['Rock', 'Pop', 'Jazz', 'Electrónica', 'Hip Hop', 'Clásica', 'Blues'];
            foreach ($genreNames as $name) {
                $genres->push(Genre::create(['name' => $name]));
            }
        }
        
        // Para cada artista sin canciones, crear al menos 3 canciones
        foreach ($artistsWithoutSongs as $artist) {
            $numSongs = rand(3, 5); // Entre 3 y 5 canciones por artista
            
            for ($i = 0; $i < $numSongs; $i++) {
                // Usar el accesor 'name' para mantener consistencia con la estructura de la base de datos
                $songTitle = "Canción " . ($i + 1) . " de " . $artist->name;
                
                // Crear o recuperar un álbum para el artista si no existe
                $album = Album::where('artist_id', $artist->id)->first();
                if (!$album) {
                    $album = Album::create([
                        'title' => "Álbum de " . $artist->name,
                        'artist_id' => $artist->id,
                        'release_year' => rand(2010, 2025),
                    ]);
                }
                
                Song::create([
                    'title' => $songTitle,
                    'artist_id' => $artist->id,
                    'genre_id' => $genres->random()->id,
                    'album_id' => $album->id, // Usar el álbum creado o existente
                    'duration' => sprintf("%d:%02d", rand(2, 4), rand(0, 59)), // Duración entre 2:00 y 4:59
                    'play_count' => rand(0, 1000), // Reproducciones aleatorias
                    'cover_image' => null, // No hay imagen de portada
                ]);
            }
        }
    }
}
