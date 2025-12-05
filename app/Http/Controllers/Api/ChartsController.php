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
     *
     * @queryParam month string Filter by month in YYYY-MM format (e.g., 2025-12). Example: 2025-12
     */
    public function brandAnalytics(Request $request): JsonResponse
    {
        $user = $request->user();
        $month = $request->query('month');

        // Build base query
        $query = UserBeerCount::where('user_id', $user->id);

        // Apply date filter if month parameter is provided
        if ($month && preg_match('/^\d{4}-\d{2}$/', $month)) {
            $startOfMonth = $month . '-01 00:00:00';
            $endOfMonth = date('Y-m-t 23:59:59', strtotime($startOfMonth));

            $query->whereBetween('updated_at', [$startOfMonth, $endOfMonth]);
        }

        // Eager load relationships and group by brand name
        $userBeerCounts = $query->with('beer.brand')->get();

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

        // Calculate new brands tried (for the filtered period if month is set)
        $newBrandsQuery = UserBeerCount::where('user_id', $user->id);
        if ($month && preg_match('/^\d{4}-\d{2}$/', $month)) {
            $startOfMonth = $month . '-01 00:00:00';
            $endOfMonth = date('Y-m-t 23:59:59', strtotime($startOfMonth));
            $newBrandsQuery->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
        }
        $newBrandsTried = $newBrandsQuery->distinct('beer_id')->count('beer_id');

        return response()->json([
            'data' => $chartData,
            'statistics' => [
                'total_count' => $totalCount,
                'unique_brands' => $uniqueBrands,
                'unique_beers' => $uniqueBeers,
                'new_brands_tried' => $newBrandsTried,
            ],
            'filter' => [
                'month' => $month,
                'period' => $month ? $month : 'all_time',
            ],
            'success' => true,
            'message' => 'Brand analytics data retrieved successfully'
        ]);
    }
}