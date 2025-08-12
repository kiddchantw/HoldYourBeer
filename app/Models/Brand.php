<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    /**
     * Get the beers for the brand.
     */
    public function beers(): HasMany
    {
        return $this->hasMany(Beer::class);
    }
}