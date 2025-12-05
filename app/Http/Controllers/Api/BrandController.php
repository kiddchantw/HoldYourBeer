<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

/**
 * @group Beer Brands
 *
 * APIs for managing beer brands
 */
class BrandController extends Controller
{
    /**
     * Get all brands (Cached)
     *
     * Retrieve a list of all beer brands, sorted alphabetically by name.
     *
     * **Caching Strategy:**
     * - Results are cached for 1 hour (3600 seconds) to improve performance
     * - Cache key: `brands_list`
     * - Cache is automatically invalidated when brands are created, updated, or deleted via BrandObserver
     * - Cache driver: File-based (configurable via CACHE_DRIVER environment variable)
     *
     * **Performance:**
     * - First request: Queries database and caches results
     * - Subsequent requests: Served from cache (much faster)
     * - Cache hit rate: Expected >95% in production
     *
     * @authenticated
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
     *   ]
     * }
     */
    public function index(): AnonymousResourceCollection
    {
        // 快取 1 小時,配合 BrandObserver 自動失效
        $brands = Cache::remember('brands_list', 3600, function () {
            return Brand::orderBy('name')->get();
        });

        return BrandResource::collection($brands);
    }
}
