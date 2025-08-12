<?php

namespace App\Http\Controllers;

use App\Models\UserBeerCount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with user's beer collection.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get user's tracked beers with brand information, sorted by last_tasted_at desc
        $trackedBeers = UserBeerCount::with(['beer.brand'])
            ->where('user_id', $user->id)
            ->orderBy('last_tasted_at', 'desc')
            ->get();

        return view('dashboard', compact('trackedBeers'));
    }
}