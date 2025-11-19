<?php

namespace App\Policies;

use App\Models\Beer;
use App\Models\User;
use App\Models\UserBeerCount;

class BeerPolicy
{
    /**
     * Determine whether the user can view any beers.
     * All authenticated users can view their own beer list.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the beer.
     * All authenticated users can view beer details.
     */
    public function view(User $user, Beer $beer): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the beer's tasting count.
     * Only users who are tracking this beer can update its count.
     */
    public function update(User $user, Beer $beer): bool
    {
        return UserBeerCount::where('user_id', $user->id)
            ->where('beer_id', $beer->id)
            ->exists();
    }

    /**
     * Determine whether the user can view tasting logs for the beer.
     * Only users who are tracking this beer can view its tasting logs.
     */
    public function viewTastingLogs(User $user, Beer $beer): bool
    {
        return UserBeerCount::where('user_id', $user->id)
            ->where('beer_id', $beer->id)
            ->exists();
    }
}
