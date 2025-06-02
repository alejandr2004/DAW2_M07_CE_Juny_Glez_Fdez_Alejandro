<?php

namespace Database\Seeders;

use App\Models\Artist;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AlbumSeeder extends Seeder
{
    public function run()
    {
        // Obtener artistas por su nombre correcto
        $duaLipa = Artist::where('nombre', 'Dua Lipa')->first();
        $badBunny = Artist::where('nombre', 'Bad Bunny')->first();

        if ($duaLipa && $badBunny) {
            $albums = [
                [
                    'title' => 'Future Nostalgia',
                    'artist_id' => $duaLipa->id,
                    'release_date' => '2020-03-27',
                    'genre_id' => 2, // Pop
                    'cover_image' => 'albums/future_nostalgia.jpg',
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'title' => 'Un Verano Sin Ti',
                    'artist_id' => $badBunny->id,
                    'release_date' => '2022-05-06',
                    'genre_id' => 3, // Hip Hop
                    'cover_image' => 'albums/un_verano_sin_ti.jpg',
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ];

            DB::table('albums')->insert($albums);
        }
    }
}