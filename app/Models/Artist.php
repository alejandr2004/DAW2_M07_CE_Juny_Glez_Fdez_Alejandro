<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Artist extends Model
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'artistas';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'biografia',
        'imagen',
        'pais'
    ];

    /**
     * Get songs by this artist
     */
    public function songs(): HasMany
    {
        return $this->hasMany(Song::class, 'artist_id');
    }
    
    /**
     * Accesor para obtener 'name' desde 'nombre'
     * 
     * @return string
     */
    public function getNameAttribute()
    {
        return $this->nombre;
    }
    
    /**
     * Mutador para establecer 'nombre' desde 'name'
     * 
     * @param string $value
     * @return void
     */
    public function setNameAttribute($value)
    {
        $this->attributes['nombre'] = $value;
    }

    /**
     * Get albums by this artist
     */
    public function albums(): HasMany
    {
        return $this->hasMany(Album::class);
    }
}
