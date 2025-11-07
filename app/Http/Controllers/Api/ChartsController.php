<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserBeerCount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChartsController extends Controller
{
    /**
     * Get brand analytics data for charts
     */
    public function brandAnalytics(Request $request): JsonResponse
    {
        $user = $request->user();

        // Eager load relationships and group by brand name
        $userBeerCounts = UserBeerCount::where('user_id', $user->id)
            ->with('beer.brand')
            ->get();

        $brandData = $userBeerCounts
            ->mapToGroups(function ($userBeerCount) {
                // Check if beer and brand relationships are loaded
                if ($userBeerCount->beer && $userBeerCount->beer->brand) {
                    return [$userBeerCount->beer->brand->name => $userBeerCount->count];
                }
                return ['Unknown' => 0]; // Handle cases where relationships are missing
            })
            ->map(function ($counts) {
                return $counts->sum();
            });

        // Convert to the format expected by Flutter app
        $chartData = $brandData->map(function ($totalConsumption, $brandName) {
            return [
                'brandName' => $brandName,
                'totalConsumption' => $totalConsumption
            ];
        })->values()->all();

        // Calculate statistics
        $totalCount = $userBeerCounts->sum('count');
        $uniqueBrands = $brandData->count();
        $uniqueBeers = $userBeerCounts->count();

        return response()->json([
            'data' => $chartData,
            'statistics' => [
                'total_count' => $totalCount,
                'unique_brands' => $uniqueBrands,
                'unique_beers' => $uniqueBeers,
            ],
            'success' => true,
            'message' => 'Brand analytics data retrieved successfully'
        ]);
    }
}