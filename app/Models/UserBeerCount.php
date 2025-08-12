<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBeerCount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'beer_id',
        'count',
        'last_tasted_at',
    ];

    protected $casts = [
        'last_tasted_at' => 'datetime',
    ];

    /**
     * Get the user that owns the count.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the beer that owns the count.
     */
    public function beer(): BelongsTo
    {
        return $this->belongsTo(Beer::class);
    }
}