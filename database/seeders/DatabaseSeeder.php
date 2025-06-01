<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Ejecutamos los seeders en orden para mantener la integridad referencial
        $this->call([
            UserSeeder::class,
            GenreSeeder::class,
            ArtistSeeder::class,
            AlbumSeeder::class,
            SongSeeder::class,
            PlaylistSeeder::class,
            // Añadimos el nuevo seeder para asociar canciones a artistas sin canciones
            SongArtistSeeder::class,
        ]);
    }
}
