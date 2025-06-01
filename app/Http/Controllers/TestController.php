<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TestController extends Controller
{
    public function checkStructure()
    {
        $structure = [];
        
        // Verificar estructura de canciones
        $structure['canciones'] = [
            'exists' => Schema::hasTable('canciones'),
            'columns' => Schema::hasTable('canciones') ? Schema::getColumnListing('canciones') : [],
            'has_artist' => Schema::hasTable('canciones') && Schema::hasColumn('canciones', 'artist'),
            'has_artist_id' => Schema::hasTable('canciones') && Schema::hasColumn('canciones', 'artist_id'),
        ];
        
        // Verificar estructura de albums
        $structure['albums'] = [
            'exists' => Schema::hasTable('albums'),
            'columns' => Schema::hasTable('albums') ? Schema::getColumnListing('albums') : [],
            'has_artist' => Schema::hasTable('albums') && Schema::hasColumn('albums', 'artist'),
            'has_artist_id' => Schema::hasTable('albums') && Schema::hasColumn('albums', 'artist_id'),
        ];
        
        // Verificar si existe la tabla artistas
        $structure['artistas'] = [
            'exists' => Schema::hasTable('artistas'),
            'columns' => Schema::hasTable('artistas') ? Schema::getColumnListing('artistas') : [],
        ];
        
        return response()->json($structure);
    }
}
