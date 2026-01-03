<?php

namespace Tests\Helpers;

use App\Models\User;
use App\Models\UserOAuthProvider;

trait CreatesOAuthUsers
{
    /**
     * Create an OAuth user with linked provider
     *
     * @param string $provider Provider name (google, apple, facebook)
     * @param array $userAttributes Additional user attributes
     * @param array $providerAttributes Additional provider attributes
     * @return User
     */
    protected function createOAuthUser(
        string $provider = 'google',
        array $userAttributes = [],
        array $providerAttributes = []
    ): User {
        // Create user without provider fields
        $user = User::factory()->create(array_merge([
            'password' => null, // OAuth users don't have password initially
            'email_verified_at' => now(),
        ], $userAttributes));

        // Create OAuth provider link
        UserOAuthProvider::create(array_merge([
            'user_id' => $user->id,
            'provider' => $provider,
            'provider_id' => $provider . '_' . $user->id,
            'provider_email' => $user->email,
            'linked_at' => now(),
            'last_used_at' => now(),
        ], $providerAttributes));

        return $user->fresh();
    }

    /**
     * Create a local (email/password) user without OAuth provider
     *
     * @param array $attributes Additional user attributes
     * @return User
     */
    protected function createLocalUser(array $attributes = []): User
    {
        return User::factory()->create($attributes);
    }
}
