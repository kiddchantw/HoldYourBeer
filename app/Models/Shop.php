<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shop extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name'];

    /**
     * Get all beers sold at this shop (crowd-sourced data).
     */
    public function beers(): BelongsToMany
    {
        return $this->belongsToMany(Beer::class)
            ->withPivot(['first_reported_at', 'last_reported_at', 'report_count'])
            ->withTimestamps();
    }

    /**
     * Get all tasting logs for this shop (personal records).
     */
    public function tastingLogs(): HasMany
    {
        return $this->hasMany(TastingLog::class);
    }
}
