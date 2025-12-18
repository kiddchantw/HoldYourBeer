<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class RefreshToken extends Model
{
    protected $fillable = [
        'user_id',
        'token',
        'device_name',
        'user_agent',
        'expires_at',
        'last_used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    /**
     * Get the user that owns the refresh token.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate a new refresh token for a user.
     *
     * @param User $user
     * @param string|null $deviceName
     * @param string|null $userAgent
     * @return array ['plain_token' => string, 'model' => RefreshToken]
     */
    public static function generate(User $user, ?string $deviceName = null, ?string $userAgent = null): array
    {
        $plainToken = Str::random(64);
        $hashedToken = hash('sha256', $plainToken);

        $expirationMinutes = (int) config('sanctum.refresh_token_expiration', 43200);

        $refreshToken = self::create([
            'user_id' => $user->id,
            'token' => $hashedToken,
            'device_name' => $deviceName,
            'user_agent' => $userAgent,
            'expires_at' => now()->addMinutes($expirationMinutes),
        ]);

        return [
            'plain_token' => $plainToken,
            'model' => $refreshToken,
        ];
    }

    /**
     * Validate a plain text refresh token and return the model if valid.
     *
     * @param string $plainToken
     * @return RefreshToken|null
     */
    public static function validate(string $plainToken): ?RefreshToken
    {
        $hashedToken = hash('sha256', $plainToken);

        return self::where('token', $hashedToken)
            ->where('expires_at', '>', now())
            ->first();
    }

    /**
     * Mark this token as used by updating last_used_at.
     */
    public function markAsUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Revoke (delete) this refresh token.
     */
    public function revoke(): void
    {
        $this->delete();
    }

    /**
     * Prune all expired refresh tokens.
     *
     * @return int Number of tokens deleted
     */
    public static function pruneExpired(): int
    {
        return self::where('expires_at', '<=', now())->delete();
    }
}
