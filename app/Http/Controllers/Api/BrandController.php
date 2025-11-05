<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @group Beer Brands
 *
 * APIs for managing beer brands
 */
class BrandController extends Controller
{
    /**
     * Get all brands
     *
     * Retrieve a list of all beer brands, sorted alphabetically by name.
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
        $brands = Brand::orderBy('name')->get();

        return BrandResource::collection($brands);
    }
}