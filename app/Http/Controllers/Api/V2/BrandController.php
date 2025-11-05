<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @group V2 - Beer Brands
 *
 * APIs for managing beer brands (Version 2)
 *
 * Version 2 introduces pagination and search capabilities for brand listings.
 */
class BrandController extends Controller
{
    /**
     * Get all brands (paginated)
     *
     * Retrieve a paginated list of beer brands with optional search functionality.
     * This is an enhanced version of the V1 endpoint with pagination support.
     *
     * @authenticated
     *
     * @queryParam per_page integer Number of items per page (1-100). Defaults to 20. Example: 20
     * @queryParam page integer Page number. Example: 1
     * @queryParam search string Search brands by name. Example: Guinness
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Guinness"
     *     },
     *     {
     *       "id": 2,
     *       "name": "Heineken"
     *     }
     *   ],
     *   "meta": {
     *     "current_page": 1,
     *     "last_page": 5,
     *     "per_page": 20,
     *     "total": 100,
     *     "from": 1,
     *     "to": 20
     *   },
     *   "links": {
     *     "first": "http://localhost/api/v2/brands?page=1",
     *     "last": "http://localhost/api/v2/brands?page=5",
     *     "prev": null,
     *     "next": "http://localhost/api/v2/brands?page=2"
     *   }
     * }
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        // Validate pagination parameters
        $validated = $request->validate([
            'per_page' => ['integer', 'min:1', 'max:100'],
            'page' => ['integer', 'min:1'],
            'search' => ['string', 'max:255'],
        ]);

        $perPage = $validated['per_page'] ?? 20;

        // Build query with optional search
        $query = Brand::query()->orderBy('name');

        if (isset($validated['search'])) {
            $query->where('name', 'ILIKE', '%' . $validated['search'] . '%');
        }

        // Paginate results
        $paginated = $query->paginate($perPage);

        // Return paginated response with metadata
        return BrandResource::collection($paginated->items())
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
}
