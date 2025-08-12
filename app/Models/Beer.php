<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Beer extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand_id',
        'name',
        'style',
    ];

    /**
     * Get the brand that owns the beer.
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Get the user beer counts for the beer.
     */
    public function userBeerCounts(): HasMany
    {
        return $this->hasMany(UserBeerCount::class);
    }

    /**
     * Get the tasting count for a specific user.
     */
    public function getTastingCountForUser(int $userId): int
    {
        $count = $this->userBeerCounts()
            ->where('user_id', $userId)
            ->first();

        return $count ? $count->count : 0;
    }
}