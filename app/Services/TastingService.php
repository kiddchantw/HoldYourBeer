<?php

namespace App\Services;

use App\Models\Beer;
use App\Models\UserBeerCount;
use App\Models\TastingLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TastingService
{
    /**
     * Increment the tasting count for a beer.
     *
     * @param int $userId
     * @param int $beerId
     * @param string|null $note
     * @return UserBeerCount
     * @throws ModelNotFoundException
     */
    public function incrementCount(int $userId, int $beerId, ?string $note = null): UserBeerCount
    {
        return DB::transaction(function () use ($userId, $beerId, $note) {
            $userBeerCount = UserBeerCount::where('user_id', $userId)
                ->where('beer_id', $beerId)
                ->lockForUpdate()
                ->firstOrFail();

            $userBeerCount->count += 1;
            $userBeerCount->last_tasted_at = now();
            $userBeerCount->save();

            TastingLog::create([
                'user_beer_count_id' => $userBeerCount->id,
                'action' => 'increment',
                'tasted_at' => now(),
                'note' => $note,
            ]);

            return $userBeerCount->fresh(['beer.brand']);
        });
    }

    /**
     * Decrement the tasting count for a beer.
     *
     * @param int $userId
     * @param int $beerId
     * @param string|null $note
     * @return UserBeerCount
     * @throws ModelNotFoundException
     * @throws \Exception
     */
    public function decrementCount(int $userId, int $beerId, ?string $note = null): UserBeerCount
    {
        return DB::transaction(function () use ($userId, $beerId, $note) {
            $userBeerCount = UserBeerCount::where('user_id', $userId)
                ->where('beer_id', $beerId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($userBeerCount->count <= 0) {
                throw new \Exception('Cannot decrement count below zero.');
            }

            $userBeerCount->count -= 1;
            $userBeerCount->last_tasted_at = now();
            $userBeerCount->save();

            TastingLog::create([
                'user_beer_count_id' => $userBeerCount->id,
                'action' => 'decrement',
                'tasted_at' => now(),
                'note' => $note,
            ]);

            return $userBeerCount->fresh(['beer.brand']);
        });
    }

    /**
     * Add a new beer to user's tracking list.
     *
     * @param int $userId
     * @param array $beerData
     * @return Beer
     */
    public function addBeerToTracking(int $userId, array $beerData): Beer
    {
        return DB::transaction(function () use ($userId, $beerData) {
            $beer = Beer::create($beerData);

            UserBeerCount::create([
                'user_id' => $userId,
                'beer_id' => $beer->id,
                'count' => 1,
                'last_tasted_at' => now(),
            ]);

            TastingLog::create([
                'user_beer_count_id' => UserBeerCount::where('user_id', $userId)
                    ->where('beer_id', $beer->id)
                    ->first()->id,
                'action' => 'initial',
                'tasted_at' => now(),
            ]);

            return $beer->fresh('brand');
        });
    }

    /**
     * Get tasting logs for a specific beer.
     *
     * @param int $userId
     * @param int $beerId
     * @return \Illuminate\Database\Eloquent\Collection
     * @throws ModelNotFoundException
     */
    public function getTastingLogs(int $userId, int $beerId)
    {
        $userBeerCount = UserBeerCount::where('user_id', $userId)
            ->where('beer_id', $beerId)
            ->firstOrFail();

        return $userBeerCount->tastingLogs()
            ->orderBy('tasted_at', 'desc')
            ->get();
    }
}
