<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TastingLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_beer_count_id',
        'action',
        'tasted_at',
        'note',
        'shop_id',
    ];

    protected $casts = [
        'tasted_at' => 'datetime',
    ];

    /**
     * Get the shop where this beer was purchased.
     */
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
