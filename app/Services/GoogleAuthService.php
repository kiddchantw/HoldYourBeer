<?php

namespace App\Services;

use Google\Client as GoogleClient;

class GoogleAuthService
{
    protected GoogleClient $client;

    public function __construct(?GoogleClient $client = null)
    {
        if ($client) {
            $this->client = $client;
        } else {
            $this->client = new GoogleClient([
                'client_id' => config('services.google.client_id'),
            ]);
        }
    }

    /**
     * Verify Google ID Token and return payload
     *
     * @param string $idToken
     * @return array|false
     */
    public function verifyIdToken(string $idToken): array|false
    {
        return $this->client->verifyIdToken($idToken);
    }
}
