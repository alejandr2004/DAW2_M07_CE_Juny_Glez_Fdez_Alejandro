<?php

namespace Database\Seeders;

use App\Models\Genre;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GenreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lista de géneros musicales comunes
        $genres = [
            'Rock',
            'Pop',
            'Hip Hop',
            'R&B',
            'Electronic',
            'Jazz',
            'Blues',
            'Classical',
            'Country',
            'Latin',
            'Reggae',
            'Folk',
            'Metal',
            'Punk',
            'Indie',
            'Alternative',
            'Soul',
            'Funk',
            'Disco',
            'Urban'
        ];

        // Insertar cada género en la base de datos
        foreach ($genres as $genreName) {
            Genre::create([
                'name' => $genreName
            ]);
        }
    }
}
