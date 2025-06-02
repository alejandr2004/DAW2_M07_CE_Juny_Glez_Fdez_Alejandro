<?php

namespace Database\Seeders;

use App\Models\Artist;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class ArtistSeeder extends Seeder
{
    public function run()
    {
        if (Schema::hasTable('artistas')) {
            Artist::insert([
                [
                    'nombre' => 'Dua Lipa',
                    'biografia' => 'Cantante británica',
                    'pais' => 'Reino Unido',
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'nombre' => 'Bad Bunny',
                    'biografia' => 'Cantante puertorriqueño',
                    'pais' => 'Puerto Rico',
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
        }
    }
}