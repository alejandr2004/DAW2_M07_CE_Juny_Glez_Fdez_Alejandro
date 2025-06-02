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
        
        foreach ($albums as $album) {
            for ($i = 1; $i <= 5; $i++) {
                $songs[] = [
                    'title' => 'Canción '.$i.' del álbum '.$album->title,
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