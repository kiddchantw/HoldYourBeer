<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\BeerResource;
use App\Models\Beer;
use App\Services\GoogleAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @group V2 - Global Beer Search
 *
 * APIs for searching the global beer database (Version 2)
 */
class BeerController extends Controller
{
    /**
     * Search global beers
     *
     * Retrieve a list of beers from the global database, filtered by brand or name.
     * This endpoint does not return user-specific tracking data.
     *
     * @authenticated
     *
     * @queryParam search string Fuzzy search by beer name. Example: Draught
     * @queryParam brand_id integer Filter by brand ID. Example: 1
     * @queryParam limit integer Limit the number of results (1-50). Defaults to 20. Example: 20
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
     *       "tasting_count": null,
     *       "last_tasted_at": null
     *     }
     *   ]
     * }
     */
    public function index(Request $request, GoogleAnalyticsService $analytics)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'min:1'],
            'brand_id' => ['nullable', 'integer', 'exists:brands,id'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $query = Beer::with('brand');

        // Apply brand filter
        if (isset($validated['brand_id'])) {
            $query->where('brand_id', $validated['brand_id']);
        }

        // Apply fuzzy name search
        if (isset($validated['search'])) {
            $searchTerm = '%' . $validated['search'] . '%';
            // Use 'ilike' for case-insensitive search in PostgreSQL
             $query->where('name', 'ilike', $searchTerm);
        }

        // Default sorting
        $query->orderBy('name', 'asc');

        $limit = $validated['limit'] ?? 20;

        $results = $query->limit($limit)->get();

        // Track search event if search query was provided
        if (isset($validated['search'])) {
            $analytics->trackSearch(
                Auth::id(),
                $validated['search'],
                $results->count()
            );
        }

        return BeerResource::collection($results);
    }
}
