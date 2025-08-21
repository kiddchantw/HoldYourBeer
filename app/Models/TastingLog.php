<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TastingLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_beer_count_id',
        'action',
        'tasted_at',
        'note',
    ];

    protected $casts = [
        'tasted_at' => 'datetime',
    ];
}
