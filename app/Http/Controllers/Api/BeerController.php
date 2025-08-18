<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Beer;
use App\Models\UserBeerCount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BeerController extends Controller
{
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
}
