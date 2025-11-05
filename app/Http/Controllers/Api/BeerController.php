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
        // Validate pagination parameters
        $validated = $request->validate([
            'per_page' => ['integer', 'min:1', 'max:100'],
            'page' => ['integer', 'min:1'],
            'sort' => ['string', 'in:-tasted_at,tasted_at,name,-name'],
            'brand_id' => ['integer', 'exists:brands,id'],
        ]);

        $perPage = $validated['per_page'] ?? 20;

        // Eager load relationships to avoid N+1 queries
        $query = UserBeerCount::with(['beer.brand'])
            ->where('user_id', Auth::id());

        // Apply sorting
        $sort = $validated['sort'] ?? '-tasted_at';
        if ($sort === '-tasted_at') {
            $query->orderBy('last_tasted_at', 'desc');
        } elseif ($sort === 'tasted_at') {
            $query->orderBy('last_tasted_at', 'asc');
        } elseif ($sort === 'name') {
            $query->join('beers', 'user_beer_counts.beer_id', '=', 'beers.id')
                ->orderBy('beers.name', 'asc')
                ->select('user_beer_counts.*');
        } elseif ($sort === '-name') {
            $query->join('beers', 'user_beer_counts.beer_id', '=', 'beers.id')
                ->orderBy('beers.name', 'desc')
                ->select('user_beer_counts.*');
        }

        // Apply brand filter (optimized with whereHas)
        if (isset($validated['brand_id'])) {
            $query->whereHas('beer', function ($q) use ($validated) {
                $q->where('brand_id', $validated['brand_id']);
            });
        }

        // Paginate results
        $paginated = $query->paginate($perPage);

        // Transform the data using BeerResource
        $beers = $paginated->getCollection()->map(function ($userBeerCount) {
            $beer = $userBeerCount->beer;
            $beer->tasting_count = $userBeerCount->count;
            $beer->last_tasted_at = $userBeerCount->last_tasted_at;
            return $beer;
        });

        // Return paginated response with metadata
        return BeerResource::collection($beers)
            ->additional([
                'meta' => [
                    'current_page' => $paginated->currentPage(),
                    'last_page' => $paginated->lastPage(),
                    'per_page' => $paginated->perPage(),
                    'total' => $paginated->total(),
                    'from' => $paginated->firstItem(),
                    'to' => $paginated->lastItem(),
                ],
                'links' => [
                    'first' => $paginated->url(1),
                    'last' => $paginated->url($paginated->lastPage()),
                    'prev' => $paginated->previousPageUrl(),
                    'next' => $paginated->nextPageUrl(),
                ],
            ]);
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
