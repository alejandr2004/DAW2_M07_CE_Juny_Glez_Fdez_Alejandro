<?php

namespace Database\Seeders;

use App\Models\Album;
use App\Models\Artist;
use Illuminate\Database\Seeder;

class AlbumSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener IDs de artistas para referenciar en los álbumes
        $artists = Artist::all();
        
        // Datos de álbumes de ejemplo
        $albums = [
            // Dua Lipa
            [
                'title' => 'Future Nostalgia',
                'artist_id' => $artists->where('name', 'Dua Lipa')->first()->id,
                'release_year' => 2020,
                'cover_image' => 'albums/future_nostalgia.jpg',
            ],
            // The Weeknd
            [
                'title' => 'After Hours',
                'artist_id' => $artists->where('name', 'The Weeknd')->first()->id,
                'release_year' => 2020,
                'cover_image' => 'albums/after_hours.jpg',
            ],
            // Bad Bunny
            [
                'title' => 'YHLQMDLG',
                'artist_id' => $artists->where('name', 'Bad Bunny')->first()->id,
                'release_year' => 2020,
                'cover_image' => 'albums/yhlqmdlg.jpg',
            ],
            // Billie Eilish
            [
                'title' => 'Happier Than Ever',
                'artist_id' => $artists->where('name', 'Billie Eilish')->first()->id,
                'release_year' => 2021,
                'cover_image' => 'albums/happier_than_ever.jpg',
            ],
            // Taylor Swift
            [
                'title' => 'Folklore',
                'artist_id' => $artists->where('name', 'Taylor Swift')->first()->id,
                'release_year' => 2020,
                'cover_image' => 'albums/folklore.jpg',
            ],
            // Ed Sheeran
            [
                'title' => '=',
                'artist_id' => $artists->where('name', 'Ed Sheeran')->first()->id,
                'release_year' => 2021,
                'cover_image' => 'albums/equals.jpg',
            ],
            // Rosalía
            [
                'title' => 'Motomami',
                'artist_id' => $artists->where('name', 'Rosalía')->first()->id,
                'release_year' => 2022,
                'cover_image' => 'albums/motomami.jpg',
            ],
            // Coldplay
            [
                'title' => 'Music of the Spheres',
                'artist_id' => $artists->where('name', 'Coldplay')->first()->id,
                'release_year' => 2021,
                'cover_image' => 'albums/music_of_the_spheres.jpg',
            ],
            // Queen
            [
                'title' => 'A Night at the Opera',
                'artist_id' => $artists->where('name', 'Queen')->first()->id,
                'release_year' => 1975,
                'cover_image' => 'albums/a_night_at_the_opera.jpg',
            ],
            // Arctic Monkeys
            [
                'title' => 'AM',
                'artist_id' => $artists->where('name', 'Arctic Monkeys')->first()->id,
                'release_year' => 2013,
                'cover_image' => 'albums/am.jpg',
            ],
        ];

        // Insertar cada álbum en la base de datos
        foreach ($albums as $albumData) {
            Album::create($albumData);
        }
    }
}
