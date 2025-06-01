<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'name',
    ];

    /**
     * Get songs by this artist
     */
    public function songs(): HasMany
    {
        return $this->hasMany(Song::class, 'artist_id');
    }

    /**
     * Get albums by this artist
     */
    public function albums(): HasMany
    {
        return $this->hasMany(Album::class);
    }
}
