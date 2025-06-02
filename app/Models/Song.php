<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Song extends Model
{
    use HasFactory;

    // Especificar el nombre correcto de la tabla
    protected $table = 'canciones';

    protected $fillable = [
        'title',
        'duration',
        'album_id',
        'artist_id',
        'genre_id',
        'play_count',
        'cover_image'
    ];

    public function album()
    {
        return $this->belongsTo(Album::class);
    }

    public function artist()
    {
        return $this->belongsTo(Artist::class);
    }

    public function genre()
    {
        return $this->belongsTo(Genre::class);
    }

    public function playlists()
    {
        return $this->belongsToMany(Playlist::class, 'playlist_cancion');
    }
}