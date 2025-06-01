<?php

namespace Database\Seeders;

use App\Models\Playlist;
use App\Models\Song;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlaylistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener usuarios para asignar playlists
        $users = User::where('role', 'client')->get();
        
        // Obtener canciones para incluir en playlists
        $songs = Song::all();
        
        // Crear algunas playlists de ejemplo
        $playlists = [
            [
                'name' => 'Mis favoritas',
                'user_id' => $users[0]->id,
                'is_public' => true,
                'songs' => $songs->random(5)->pluck('id')->toArray()
            ],
            [
                'name' => 'Para estudiar',
                'user_id' => $users[0]->id,
                'is_public' => false,
                'songs' => $songs->random(4)->pluck('id')->toArray()
            ],
            [
                'name' => 'Rock Clásico',
                'user_id' => $users[1]->id,
                'is_public' => true,
                'songs' => $songs->random(3)->pluck('id')->toArray()
            ],
            [
                'name' => 'Pop Actual',
                'user_id' => $users[1]->id,
                'is_public' => true,
                'songs' => $songs->random(6)->pluck('id')->toArray()
            ],
            [
                'name' => 'Mi Playlist Privada',
                'user_id' => $users[2]->id,
                'is_public' => false,
                'songs' => $songs->random(4)->pluck('id')->toArray()
            ],
        ];

        // Insertar cada playlist y sus canciones
        foreach ($playlists as $playlistData) {
            $songIds = $playlistData['songs'];
            unset($playlistData['songs']);
            
            $playlist = Playlist::create($playlistData);
            
            // Agregar canciones a la playlist con un orden
            $order = 1;
            foreach ($songIds as $songId) {
                $playlist->songs()->attach($songId, ['order' => $order]);
                $order++;
            }
        }
    }
}
