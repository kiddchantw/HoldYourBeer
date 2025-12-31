<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'email_verified_at',
        'provider',
        'provider_id',
        'onboarding_completed_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'onboarding_completed_at' => 'datetime',
        ];
    }

    /**
     * Set the email attribute to lowercase
     */
    public function setEmailAttribute(string $value): void
    {
        $this->attributes['email'] = strtolower($value);
    }

    /**
     * Send the email verification notification.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new \App\Notifications\VerifyEmailNotification);
    }

    /**
     * Send the password reset notification.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new \App\Notifications\ResetPasswordNotification($token));
    }

    /**
     * Check if user is an OAuth user (Google, Apple, Facebook, etc.)
     */
    public function isOAuthUser(): bool
    {
        return in_array($this->provider, ['google', 'apple', 'facebook']);
    }

    /**
     * Check if user is a local (email/password) user
     */
    public function isLocalUser(): bool
    {
        return $this->provider === 'local' || $this->provider === null;
    }

    /**
     * Check if user has a password set
     * OAuth users initially have null password
     */
    public function hasPassword(): bool
    {
        return !is_null($this->password);
    }

    /**
     * Check if user can set password without providing current password
     * Only OAuth users who haven't set a password yet can do this
     */
    public function canSetPasswordWithoutCurrent(): bool
    {
        return $this->isOAuthUser() && !$this->hasPassword();
    }
    /**
     * Check if user is an admin.
     */
    public function getIsAdminAttribute(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Get all OAuth providers linked to this user
     */
    public function oauthProviders(): HasMany
    {
        return $this->hasMany(UserOAuthProvider::class);
    }

    /**
     * Check if user has linked a specific OAuth provider
     */
    public function hasOAuthProvider(string $provider): bool
    {
        return $this->oauthProviders()
            ->where('provider', $provider)
            ->exists();
    }

    /**
     * Get all linked OAuth providers as a collection
     */
    public function getLinkedProviders(): Collection
    {
        return $this->oauthProviders()
            ->orderBy('linked_at', 'desc')
            ->get();
    }

    /**
     * Get the number of authentication methods available
     * (password + OAuth providers)
     */
    public function getAuthMethodsCount(): int
    {
        $count = 0;

        // Check if user has password
        if ($this->password) {
            $count++;
        }

        // Add OAuth providers count
        $count += $this->oauthProviders()->count();

        return $count;
    }

    /**
     * Check if user can safely unlink an OAuth provider
     * (must have at least one other auth method)
     */
    public function canUnlinkOAuthProvider(): bool
    {
        return $this->getAuthMethodsCount() > 1;
    }
}
