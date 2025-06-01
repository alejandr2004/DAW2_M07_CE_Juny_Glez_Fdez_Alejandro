<?php

namespace Database\Seeders;

use App\Models\Artist;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ArtistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lista de artistas de ejemplo
        $artists = [
            'Dua Lipa',
            'The Weeknd',
            'Bad Bunny',
            'Billie Eilish',
            'Taylor Swift',
            'Ed Sheeran',
            'Rosalía',
            'Coldplay',
            'Queen',
            'Arctic Monkeys',
            'Kendrick Lamar',
            'Adele',
            'C. Tangana',
            'The Beatles',
            'David Bowie'
        ];

        // Insertar cada artista en la base de datos
        foreach ($artists as $artistName) {
            Artist::create([
                'name' => $artistName
            ]);
        }
    }
}
