<?php

namespace Database\Seeders;

use App\Models\Album;
use App\Models\Artist;
use App\Models\Genre;
use App\Models\Song;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SongSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener referencias a los modelos necesarios
        $artists = Artist::all();
        $albums = Album::all();
        $genres = Genre::all();

        // Datos de canciones para el álbum "Future Nostalgia" (Dua Lipa)
        $duaLipaAlbum = $albums->where('title', 'Future Nostalgia')->first();
        $duaLipa = $artists->where('name', 'Dua Lipa')->first();
        $popGenre = $genres->where('name', 'Pop')->first();
        $danceGenre = $genres->where('name', 'Electronic')->first();

        $duaLipaSongs = [
            [
                'title' => 'Don\'t Start Now',
                'duration' => '00:03:03',
                'genre_id' => $danceGenre->id
            ],
            [
                'title' => 'Physical',
                'duration' => '00:03:13',
                'genre_id' => $popGenre->id
            ],
            [
                'title' => 'Levitating',
                'duration' => '00:03:23',
                'genre_id' => $popGenre->id
            ],
            [
                'title' => 'Break My Heart',
                'duration' => '00:03:41',
                'genre_id' => $danceGenre->id
            ],
        ];

        foreach ($duaLipaSongs as $songData) {
            Song::create([
                'title' => $songData['title'],
                'artist_id' => $duaLipa->id,
                'album_id' => $duaLipaAlbum->id,
                'duration' => $songData['duration'],
                'genre_id' => $songData['genre_id'],
            ]);
        }

        // Datos de canciones para el álbum "After Hours" (The Weeknd)
        $weekndAlbum = $albums->where('title', 'After Hours')->first();
        $weeknd = $artists->where('name', 'The Weeknd')->first();
        $rnbGenre = $genres->where('name', 'R&B')->first();

        $weekndSongs = [
            [
                'title' => 'Blinding Lights',
                'duration' => '00:03:20',
                'genre_id' => $rnbGenre->id
            ],
            [
                'title' => 'In Your Eyes',
                'duration' => '00:03:57',
                'genre_id' => $rnbGenre->id
            ],
            [
                'title' => 'Save Your Tears',
                'duration' => '00:03:35',
                'genre_id' => $popGenre->id
            ],
            [
                'title' => 'After Hours',
                'duration' => '00:06:01',
                'genre_id' => $rnbGenre->id
            ],
        ];

        foreach ($weekndSongs as $songData) {
            Song::create([
                'title' => $songData['title'],
                'artist_id' => $weeknd->id,
                'album_id' => $weekndAlbum->id,
                'duration' => $songData['duration'],
                'genre_id' => $songData['genre_id'],
            ]);
        }

        // Datos de canciones para el álbum "Folklore" (Taylor Swift)
        $swiftAlbum = $albums->where('title', 'Folklore')->first();
        $swift = $artists->where('name', 'Taylor Swift')->first();
        $folkGenre = $genres->where('name', 'Folk')->first();
        $indieGenre = $genres->where('name', 'Indie')->first();

        $swiftSongs = [
            [
                'title' => 'Cardigan',
                'duration' => '00:03:59',
                'genre_id' => $folkGenre->id
            ],
            [
                'title' => 'The 1',
                'duration' => '00:03:30',
                'genre_id' => $indieGenre->id
            ],
            [
                'title' => 'Exile',
                'duration' => '00:04:45',
                'genre_id' => $folkGenre->id
            ],
            [
                'title' => 'August',
                'duration' => '00:04:21',
                'genre_id' => $indieGenre->id
            ],
        ];

        foreach ($swiftSongs as $songData) {
            Song::create([
                'title' => $songData['title'],
                'artist_id' => $swift->id,
                'album_id' => $swiftAlbum->id,
                'duration' => $songData['duration'],
                'genre_id' => $songData['genre_id'],
            ]);
        }

        // Datos de canciones para el álbum "A Night at the Opera" (Queen)
        $queenAlbum = $albums->where('title', 'A Night at the Opera')->first();
        $queen = $artists->where('name', 'Queen')->first();
        $rockGenre = $genres->where('name', 'Rock')->first();

        $queenSongs = [
            [
                'title' => 'Bohemian Rhapsody',
                'duration' => '00:05:55',
                'genre_id' => $rockGenre->id
            ],
            [
                'title' => 'Love of My Life',
                'duration' => '00:03:38',
                'genre_id' => $rockGenre->id
            ],
            [
                'title' => '\'39',
                'duration' => '00:03:30',
                'genre_id' => $rockGenre->id
            ],
            [
                'title' => 'You\'re My Best Friend',
                'duration' => '00:02:50',
                'genre_id' => $rockGenre->id
            ],
        ];

        foreach ($queenSongs as $songData) {
            Song::create([
                'title' => $songData['title'],
                'artist_id' => $queen->id,
                'album_id' => $queenAlbum->id,
                'duration' => $songData['duration'],
                'genre_id' => $songData['genre_id'],
            ]);
        }

        // Datos de canciones para el álbum "Motomami" (Rosalía)
        $rosaliaAlbum = $albums->where('title', 'Motomami')->first();
        $rosalia = $artists->where('name', 'Rosalía')->first();
        $latinGenre = $genres->where('name', 'Latin')->first();
        $urbanGenre = $genres->where('name', 'Urban')->first();

        $rosaliaSongs = [
            [
                'title' => 'Saoko',
                'duration' => '00:02:17',
                'genre_id' => $urbanGenre->id
            ],
            [
                'title' => 'Candy',
                'duration' => '00:01:52',
                'genre_id' => $latinGenre->id
            ],
            [
                'title' => 'La Fama',
                'duration' => '00:03:08',
                'genre_id' => $latinGenre->id
            ],
            [
                'title' => 'Chicken Teriyaki',
                'duration' => '00:02:09',
                'genre_id' => $urbanGenre->id
            ],
        ];

        foreach ($rosaliaSongs as $songData) {
            Song::create([
                'title' => $songData['title'],
                'artist_id' => $rosalia->id,
                'album_id' => $rosaliaAlbum->id,
                'duration' => $songData['duration'],
                'genre_id' => $songData['genre_id'],
            ]);
        }
    }
}
