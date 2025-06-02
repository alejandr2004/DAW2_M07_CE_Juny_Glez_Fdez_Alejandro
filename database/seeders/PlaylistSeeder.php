<?php

namespace Database\Seeders;

use App\Models\Playlist;
use App\Models\Song;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlaylistSeeder extends Seeder
{
    public function run()
    {
        if (Song::exists() && User::exists()) {
            $playlists = [];
            $users = User::all();
            
            for ($i = 1; $i <= 15; $i++) {
                $playlists[] = [
                    'name' => 'Playlist ' . $i,
                    'user_id' => $users->random()->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            DB::table('playlists')->insert($playlists);

            // Asociar canciones
            foreach (Playlist::all() as $playlist) {
                $songs = Song::inRandomOrder()
                    ->limit(rand(5, 10))
                    ->pluck('id')
                    ->toArray();
                
                $playlist->songs()->sync($songs);
            }
        }
    }
}