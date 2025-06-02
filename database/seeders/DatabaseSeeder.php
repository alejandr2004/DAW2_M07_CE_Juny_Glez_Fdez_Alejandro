<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            UserSeeder::class, // Primero creamos los usuarios
            GenreSeeder::class,
            ArtistSeeder::class, // Asegúrate que está después de crear las tablas
            AlbumSeeder::class,
            SongSeeder::class,
            PlaylistSeeder::class
        ]);
    }
}