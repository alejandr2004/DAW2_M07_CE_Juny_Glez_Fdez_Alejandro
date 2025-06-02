<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    protected $fillable = ['title', 'release_date', 'artist_id', 'genre_id'];

    public function artist()
    {
        return $this->belongsTo(Artist::class);
    }

    public function songs()
    {
        return $this->hasMany(Song::class);
    }

    // Relación con género (añadida por consistencia)
    public function genre()
    {
        return $this->belongsTo(Genre::class);
    }
}