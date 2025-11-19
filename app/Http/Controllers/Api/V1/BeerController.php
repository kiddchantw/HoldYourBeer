<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBeerRequest;
use App\Http\Requests\CountActionRequest;
use App\Http\Resources\BeerResource;
use App\Http\Resources\TastingLogResource;
use App\Services\TastingService;
use App\Models\UserBeerCount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

/**
 * @group V1 - Beer Tracking
 *
 * APIs for tracking beer tastings and managing beer collections (Version 1)
 */
class BeerController extends Controller
{
    /**
     * The tasting service instance.
     */
    public function __construct(
        private TastingService $tastingService
    ) {}

    /**
     * Get my tracked beers
     *
     * Retrieve a paginated list of beers the authenticated user is tracking,
     * with tasting counts and last tasted dates.
     *
     * @authenticated
     *
     * @queryParam per_page integer Number of items per page (1-100). Defaults to 20. Example: 20
     * @queryParam page integer Page number. Example: 1
     * @queryParam sort string Sort field. Options: -tasted_at (newest first), tasted_at (oldest first), name (A-Z), -name (Z-A). Example: -tasted_at
     * @queryParam brand_id integer Filter by brand ID. Example: 1
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Guinness Draught",
     *       "style": "Dry Stout",
     *       "brand": {
     *         "id": 1,
     *         "name": "Guinness"
     *       },
     *       "tasting_count": 5,
     *       "last_tasted_at": "2025-11-05T10:00:00.000000Z"
     *     }
     *   ],
     *   "meta": {
     *     "current_page": 1,
     *     "last_page": 2,
     *     "per_page": 20,
     *     "total": 25,
     *     "from": 1,
     *     "to": 20
     *   },
     *   "links": {
     *     "first": "http://localhost/api/beers?page=1",
     *     "last": "http://localhost/api/beers?page=2",
     *     "prev": null,
     *     "next": "http://localhost/api/beers?page=2"
     *   }
     * }
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
     * Add a beer to tracking
     *
     * Add a new beer to the authenticated user's tracking list with an initial count of 1.
     *
     * @authenticated
     *
     * @bodyParam name string required The beer's name. Example: Guinness Draught
     * @bodyParam brand_id integer required The brand ID this beer belongs to. Example: 1
     * @bodyParam style string The beer style (e.g., IPA, Stout). Example: Dry Stout
     *
     * @response 201 {
     *   "data": {
     *     "id": 1,
     *     "name": "Guinness Draught",
     *     "style": "Dry Stout",
     *     "brand": {
     *       "id": 1,
     *       "name": "Guinness"
     *     },
     *     "tasting_count": 1,
     *     "last_tasted_at": "2025-11-05T10:00:00.000000Z"
     *   }
     * }
     *
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "brand_id": ["The selected brand id is invalid."]
     *   }
     * }
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
     * Update tasting count
     *
     * Increment or decrement the tasting count for a tracked beer.
     * Each action is logged with a timestamp and optional note.
     *
     * @authenticated
     *
     * @urlParam id integer required The beer ID. Example: 1
     *
     * @bodyParam action string required The action to perform. Options: increment, decrement. Example: increment
     * @bodyParam note string Optional note for this tasting. Example: Enjoyed at the pub with friends
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "name": "Guinness Draught",
     *     "style": "Dry Stout",
     *     "brand": {
     *       "id": 1,
     *       "name": "Guinness"
     *     },
     *     "tasting_count": 6,
     *     "last_tasted_at": "2025-11-05T10:30:00.000000Z"
     *   }
     * }
     *
     * @response 404 {
     *   "error_code": "RES_002",
     *   "message": "Beer not found in your tracked list."
     * }
     *
     * @response 400 {
     *   "error_code": "BIZ_001",
     *   "message": "Cannot decrement count below zero."
     * }
     */
    public function countAction(CountActionRequest $request, int $id)
    {
        // Authorization check: only users tracking this beer can update counts
        $beer = \App\Models\Beer::findOrFail($id);
        Gate::authorize('update', $beer);

        $action = $request->validated()['action'];
        $note = $request->validated()['note'] ?? null;

        // Let exceptions bubble up to the global exception handler
        $userBeerCount = match($action) {
            'increment' => $this->tastingService->incrementCount(Auth::id(), $id, $note),
            'decrement' => $this->tastingService->decrementCount(Auth::id(), $id, $note),
        };

        $beer = $userBeerCount->beer;
        $beer->tasting_count = $userBeerCount->count;
        $beer->last_tasted_at = $userBeerCount->last_tasted_at;

        return new BeerResource($beer);
    }

    /**
     * Get tasting history
     *
     * Retrieve the complete tasting history log for a specific beer,
     * including all increment/decrement actions with timestamps and notes.
     *
     * @authenticated
     *
     * @urlParam id integer required The beer ID. Example: 1
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 10,
     *       "action": "increment",
     *       "tasted_at": "2025-11-05T10:30:00.000000Z",
     *       "note": "Enjoyed at the pub with friends"
     *     },
     *     {
     *       "id": 9,
     *       "action": "increment",
     *       "tasted_at": "2025-11-04T18:15:00.000000Z",
     *       "note": null
     *     }
     *   ]
     * }
     *
     * @response 404 {
     *   "error_code": "RES_002",
     *   "message": "Beer not found in your tracked list."
     * }
     */
    public function tastingLogs(int $id)
    {
        // Authorization check: only users tracking this beer can view tasting logs
        $beer = \App\Models\Beer::findOrFail($id);
        Gate::authorize('viewTastingLogs', $beer);

        // Let exceptions bubble up to the global exception handler
        $tastingLogs = $this->tastingService->getTastingLogs(Auth::id(), $id);

        return TastingLogResource::collection($tastingLogs);
    }
}
