<?php

namespace App\Services;

use App\Exceptions\BusinessLogicException;
use App\Models\Beer;
use App\Models\Shop;
use App\Models\UserBeerCount;
use App\Models\TastingLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TastingService
{
    /**
     * Add the tasting count for a beer.
     *
     * @param int $userId
     * @param int $beerId
     * @param string|null $note
     * @return UserBeerCount
     * @throws ModelNotFoundException
     */
    public function addCount(int $userId, int $beerId, ?string $note = null): UserBeerCount
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
                'action' => 'add',
                'tasted_at' => now(),
                'note' => $note,
            ]);

            return $userBeerCount->fresh(['beer.brand']);
        });
    }

    /**
     * Delete the tasting count for a beer.
     *
     * @param int $userId
     * @param int $beerId
     * @param string|null $note
     * @return UserBeerCount
     * @throws ModelNotFoundException
     * @throws BusinessLogicException
     */
    public function deleteCount(int $userId, int $beerId, ?string $note = null): UserBeerCount
    {
        return DB::transaction(function () use ($userId, $beerId, $note) {
            $userBeerCount = UserBeerCount::where('user_id', $userId)
                ->where('beer_id', $beerId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($userBeerCount->count <= 0) {
                throw new BusinessLogicException(
                    'Cannot delete count below zero.',
                    'BIZ_001',
                    400
                );
            }

            $userBeerCount->count -= 1;
            $userBeerCount->last_tasted_at = now();
            $userBeerCount->save();

            TastingLog::create([
                'user_beer_count_id' => $userBeerCount->id,
                'action' => 'delete',
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
            // Extract shop_name, quantity, and note from beerData
            $shopName = $beerData['shop_name'] ?? null;
            $quantity = $beerData['quantity'] ?? 1;
            $note = $beerData['note'] ?? null;
 
            unset($beerData['shop_name']);
            unset($beerData['quantity']);
            unset($beerData['note']);
 
            // Create or get shop
            $shop = null;
            if (!empty($shopName)) {
                $shop = Shop::firstOrCreate(['name' => trim($shopName)]);
            }
 
            // 1. Create or get beer (Handle global beer uniqueness)
            // Use firstOrCreate to avoid Unique Constraint Violation
            $beer = Beer::firstOrCreate(
                [
                    'brand_id' => $beerData['brand_id'],
                    'name' => $beerData['name']
                ],
                $beerData // Other fields like style if creating new
            );
 
            // Sync beer_shop (crowd-sourced data)
            if ($shop) {
                $this->syncBeerShop($beer, $shop);
            }
 
            // 2. Check if user is already tracking this beer
            $userBeerCount = UserBeerCount::where('user_id', $userId)
                ->where('beer_id', $beer->id)
                ->lockForUpdate()
                ->first();

            if ($userBeerCount) {
                // Scenario A: User is already tracking -> Increment count
                $userBeerCount->count += $quantity;
                $userBeerCount->last_tasted_at = now();
                $userBeerCount->save();

                // Create tasting log (increment action)
                TastingLog::create([
                    'user_beer_count_id' => $userBeerCount->id,
                    'action' => 'add', // Treat as additional tasting
                    'tasted_at' => now(),
                    'shop_id' => $shop ? $shop->id : null,
                    'note' => $note,
                ]);
            } else {
                // Scenario B: First time tracking -> Create new record
                $userBeerCount = UserBeerCount::create([
                    'user_id' => $userId,
                    'beer_id' => $beer->id,
                    'count' => $quantity,
                    'last_tasted_at' => now(),
                ]);

                // Create tasting log (initial action)
                TastingLog::create([
                    'user_beer_count_id' => $userBeerCount->id,
                    'action' => 'initial',
                    'tasted_at' => now(),
                    'shop_id' => $shop ? $shop->id : null,
                    'note' => $note,
                ]);
            }

            // Attach tasting count to beer for API response
            $beer = $beer->fresh('brand'); // Refresh to get latest data
            $beer->tasting_count = $userBeerCount->count;
            $beer->last_tasted_at = $userBeerCount->last_tasted_at;
            
            return $beer;
        });
    }

    /**
     * Sync beer-shop relationship (crowd-sourced data).
     *
     * If the (beer, shop) combination already exists, increment report_count.
     * Otherwise, create a new record with report_count = 1.
     *
     * @param Beer $beer
     * @param Shop $shop
     * @return void
     */
    protected function syncBeerShop(Beer $beer, Shop $shop): void
    {
        $exists = $beer->shops()
            ->where('shop_id', $shop->id)
            ->exists();

        if (!$exists) {
            // First time this beer-shop combination is reported
            $beer->shops()->attach($shop->id, [
                'first_reported_at' => now(),
                'last_reported_at' => now(),
                'report_count' => 1,
            ]);
        } else {
            // Already exists, update report count
            $beer->shops()->updateExistingPivot($shop->id, [
                'last_reported_at' => now(),
                'report_count' => DB::raw('report_count + 1'),
            ]);
        }
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
