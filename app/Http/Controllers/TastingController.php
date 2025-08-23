<?php

namespace App\Http\Controllers;

use App\Models\Beer;
use App\Models\UserBeerCount;
use App\Models\TastingLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TastingController extends Controller
{
    public function increment($locale, UserBeerCount $userBeerCount)
    {
        $userBeerCount->increment('count');
        $userBeerCount->update(['last_tasted_at' => now()]);

        TastingLog::create([
            'user_beer_count_id' => $userBeerCount->id,
            'action' => 'increment',
            'tasted_at' => now(),
        ]);

        return back();
    }

    public function decrement(UserBeerCount $userBeerCount, $locale = null)
    {
        if ($userBeerCount->count > 0) {
            $userBeerCount->decrement('count');
            $userBeerCount->update(['last_tasted_at' => now()]);

            TastingLog::create([
                'user_beer_count_id' => $userBeerCount->id,
                'action' => 'decrement',
                'tasted_at' => now(),
            ]);
        }

        return back();
    }

    public function history(Request $request,$locale, $beerId)
    {

        // Manual model binding since automatic binding doesn't work in locale prefixed routes
        $beer = Beer::findOrFail($beerId);

        $userBeerCount = UserBeerCount::where('user_id', Auth::id())
            ->where('beer_id', $beer->id)
            ->firstOrFail();

        $tastingLogs = $userBeerCount->tastingLogs()->orderBy('tasted_at', 'desc')->get();

        return view('beers.history', [
            'beer' => $beer,
            'tastingLogs' => $tastingLogs,
        ]);
    }
}
