<?php

namespace App\Policies;

use App\Models\Beer;
use App\Models\User;
use App\Models\UserBeerCount;

class TastingLogPolicy
{
    /**
     * Determine whether the user can create a tasting log for the beer.
     * Only users who are tracking this beer can create tasting logs.
     */
    public function create(User $user, Beer $beer): bool
    {
        return UserBeerCount::where('user_id', $user->id)
            ->where('beer_id', $beer->id)
            ->exists();
    }
}
