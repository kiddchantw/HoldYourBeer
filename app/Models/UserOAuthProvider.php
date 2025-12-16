<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserOAuthProvider extends Model
{
    protected $table = 'user_oauth_providers';

    protected $fillable = [
        'user_id',
        'provider',
        'provider_id',
        'provider_email',
        'linked_at',
        'last_used_at',
    ];

    protected $casts = [
        'linked_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    /**
     * Get the user that owns this OAuth provider link
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
