<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Beer;
use App\Models\UserBeerCount;
use App\Models\TastingLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BeerController extends Controller
{
    /**
     * Get my list of tracked beers.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = UserBeerCount::with(['beer.brand'])
            ->where('user_id', Auth::id());

        // Apply sorting
        $sort = $request->get('sort', '-tasted_at');
        if ($sort === '-tasted_at') {
            $query->orderBy('last_tasted_at', 'desc');
        } elseif ($sort === 'tasted_at') {
            $query->orderBy('last_tasted_at', 'asc');
        }

        // Apply brand filter
        if ($request->has('brand_id')) {
            $query->whereHas('beer', function ($q) use ($request) {
                $q->where('brand_id', $request->get('brand_id'));
            });
        }

        $userBeerCounts = $query->get();

        // Transform the data to match API specification
        $beers = $userBeerCounts->map(function ($userBeerCount) {
            return [
                'id' => $userBeerCount->beer->id,
                'name' => $userBeerCount->beer->name,
                'style' => $userBeerCount->beer->style,
                'brand' => [
                    'id' => $userBeerCount->beer->brand->id,
                    'name' => $userBeerCount->beer->brand->name,
                ],
                'tasting_count' => $userBeerCount->count,
                'last_tasted_at' => $userBeerCount->last_tasted_at,
            ];
        });

        return response()->json($beers);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'brand_id' => ['required', 'integer', 'exists:brands,id'],
            'style' => ['nullable', 'string', 'max:255'],
        ]);

        $beer = Beer::create($validatedData);

        UserBeerCount::create([
            'user_id' => Auth::id(),
            'beer_id' => $beer->id,
            'count' => 1,
            'last_tasted_at' => now(),
        ]);

        return response()->json($beer, 201);
    }

    /**
     * Increment or decrement the tasting count for a beer.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function countAction(Request $request, int $id)
    {
        $validatedData = $request->validate([
            'action' => ['required', 'string', 'in:increment,decrement'],
        ]);

        $action = $validatedData['action'];

        try {
            return DB::transaction(function () use ($id, $action) {
                // Find the user beer count with lock
                $userBeerCount = UserBeerCount::where('user_id', Auth::id())
                    ->where('beer_id', $id)
                    ->lockForUpdate()
                    ->first();

                if (!$userBeerCount) {
                    return response()->json([
                        'error' => 'Beer not found in your tracked list.'
                    ], 404);
                }

                // Perform the action
                if ($action === 'increment') {
                    $userBeerCount->count += 1;
                } elseif ($action === 'decrement') {
                    // Don't decrement below zero
                    if ($userBeerCount->count <= 0) {
                        return response()->json([
                            'error' => 'Cannot decrement count below zero.'
                        ], 400);
                    }
                    $userBeerCount->count -= 1;
                }

                $userBeerCount->last_tasted_at = now();
                $userBeerCount->save();

                // Create tasting log entry
                TastingLog::create([
                    'user_beer_count_id' => $userBeerCount->id,
                    'action' => $action,
                    'tasted_at' => now(),
                ]);

                // Load the beer with brand for response
                $userBeerCount->load(['beer.brand']);

                // Return the updated beer object
                return response()->json([
                    'id' => $userBeerCount->beer->id,
                    'name' => $userBeerCount->beer->name,
                    'style' => $userBeerCount->beer->style,
                    'brand' => [
                        'id' => $userBeerCount->beer->brand->id,
                        'name' => $userBeerCount->beer->brand->name,
                    ],
                    'tasting_count' => $userBeerCount->count,
                    'last_tasted_at' => $userBeerCount->last_tasted_at,
                ]);
            });
        } catch (\Exception) {
            return response()->json([
                'error' => 'Failed to update tasting count.'
            ], 500);
        }
    }

    /**
     * Get the tasting log for a specific beer.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function tastingLogs(int $id)
    {
        // Find the user beer count
        $userBeerCount = UserBeerCount::where('user_id', Auth::id())
            ->where('beer_id', $id)
            ->first();

        if (!$userBeerCount) {
            return response()->json([
                'error' => 'Beer not found in your tracked list.'
            ], 404);
        }

        // Get tasting logs
        $tastingLogs = TastingLog::where('user_beer_count_id', $userBeerCount->id)
            ->orderBy('tasted_at', 'desc')
            ->get();

        // Transform the data
        $logs = $tastingLogs->map(function ($log) {
            return [
                'id' => $log->id,
                'action' => $log->action,
                'tasted_at' => $log->tasted_at,
                'note' => $log->note,
            ];
        });

        return response()->json($logs);
    }
}
