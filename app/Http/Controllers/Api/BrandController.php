<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\JsonResponse;

class BrandController extends Controller
{
    /**
     * Display a listing of all brands.
     */
    public function index(): JsonResponse
    {
        $brands = Brand::orderBy('name')->get();

        return response()->json($brands);
    }
}