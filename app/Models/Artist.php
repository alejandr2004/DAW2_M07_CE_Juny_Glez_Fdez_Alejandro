<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Artist extends Model
{
    protected $fillable = ['nombre', 'biografia', 'imagen', 'pais'];

    // Cambiado a español para coincidir con migración
    protected $table = 'artistas';
    
    // Accessor to allow using 'name' as an alias for 'nombre'
    public function getNameAttribute()
    {
        return $this->nombre;
    }
    
    // Accessor to allow using 'biography' as an alias for 'biografia'
    public function getBiographyAttribute()
    {
        return $this->biografia;
    }

    public function albums()
    {
        return $this->hasMany(Album::class);
    }

    public function songs()
    {
        return $this->hasMany(Song::class);
    }
}