<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BrandController extends Controller
{
    /**
     * Display a listing of all brands.
     */
    public function index(): AnonymousResourceCollection
    {
        $brands = Brand::orderBy('name')->get();

        return BrandResource::collection($brands);
    }
}