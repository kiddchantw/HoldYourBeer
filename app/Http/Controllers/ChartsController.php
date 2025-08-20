<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\UserBeerCount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Beer; // Added this import for Beer model

class ChartsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();

        // Eager load relationships and group by brand name
        $brandData = UserBeerCount::where('user_id', $user->id)
            ->with('beer.brand')
            ->get()
            ->mapToGroups(function ($userBeerCount) {
                // Check if beer and brand relationships are loaded
                if ($userBeerCount->beer && $userBeerCount->beer->brand) {
                    return [$userBeerCount->beer->brand->name => $userBeerCount->count];
                }
                return ['Unknown' => 0]; // Handle cases where relationships are missing
            })
            ->map(function ($counts) {
                return $counts->sum();
            });

        // Prepare chart data
        $labels = $brandData->keys()->all();
        $data = $brandData->values()->all();
        
        // Debug information
        $debug = [
            'user_id' => $user->id,
            'brand_data' => $brandData->toArray(),
            'labels' => $labels,
            'data' => $data,
        ];

        return view('charts.index', compact('labels', 'data', 'debug'));
    }
}
