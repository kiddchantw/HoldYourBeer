<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBeerRequest;
use App\Http\Requests\CountActionRequest;
use App\Http\Resources\BeerResource;
use App\Http\Resources\TastingLogResource;
use App\Services\TastingService;
use App\Models\UserBeerCount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BeerController extends Controller
{
    /**
     * The tasting service instance.
     */
    public function __construct(
        private TastingService $tastingService
    ) {}

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

        // Transform the data using BeerResource
        $beers = $userBeerCounts->map(function ($userBeerCount) {
            $beer = $userBeerCount->beer;
            $beer->tasting_count = $userBeerCount->count;
            $beer->last_tasted_at = $userBeerCount->last_tasted_at;
            return $beer;
        });

        return BeerResource::collection($beers);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreBeerRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreBeerRequest $request)
    {
        $beer = $this->tastingService->addBeerToTracking(
            Auth::id(),
            $request->validated()
        );

        return (new BeerResource($beer))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Increment or decrement the tasting count for a beer.
     *
     * @param  \App\Http\Requests\CountActionRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function countAction(CountActionRequest $request, int $id)
    {
        $action = $request->validated()['action'];
        $note = $request->validated()['note'] ?? null;

        try {
            $userBeerCount = match($action) {
                'increment' => $this->tastingService->incrementCount(Auth::id(), $id, $note),
                'decrement' => $this->tastingService->decrementCount(Auth::id(), $id, $note),
            };

            $beer = $userBeerCount->beer;
            $beer->tasting_count = $userBeerCount->count;
            $beer->last_tasted_at = $userBeerCount->last_tasted_at;

            return new BeerResource($beer);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json([
                'error_code' => 'RES_002',
                'message' => 'Beer not found in your tracked list.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error_code' => 'BIZ_001',
                'message' => $e->getMessage()
            ], 400);
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
        try {
            $tastingLogs = $this->tastingService->getTastingLogs(Auth::id(), $id);

            return TastingLogResource::collection($tastingLogs);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json([
                'error_code' => 'RES_002',
                'message' => 'Beer not found in your tracked list.'
            ], 404);
        }
    }
}
