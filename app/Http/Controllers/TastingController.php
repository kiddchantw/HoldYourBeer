<?php

namespace App\Http\Controllers;

use App\Models\Beer;
use App\Models\UserBeerCount;
use App\Models\TastingLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TastingController extends Controller
{
    public function increment($locale, $id)
    {
        DB::transaction(function () use ($id) {
            $userBeerCount = UserBeerCount::whereKey($id)->lockForUpdate()->firstOrFail();
            $userBeerCount->increment('count');
            $userBeerCount->update(['last_tasted_at' => now()]);

            TastingLog::create([
                'user_beer_count_id' => $userBeerCount->id,
                'action' => 'increment',
                'tasted_at' => now(),
            ]);
        });

        return back();
    }

    public function decrement($locale, $id)
    {
        DB::transaction(function () use ($id) {
            $userBeerCount = UserBeerCount::whereKey($id)->lockForUpdate()->firstOrFail();
            if ($userBeerCount->count <= 0) {
                return;
            }

            $userBeerCount->decrement('count');
            $userBeerCount->update(['last_tasted_at' => now()]);

            TastingLog::create([
                'user_beer_count_id' => $userBeerCount->id,
                'action' => 'decrement',
                'tasted_at' => now(),
            ]);
        });

        return back();
    }

    public function count(Request $request, $locale, $id)
    {
        $validatedData = $request->validate([
            'action' => ['required', 'string', 'in:increment,decrement'],
            'note' => ['nullable', 'string', 'max:150'],
        ]);

        $action = $validatedData['action'];
        $note = $validatedData['note'] ?? null;

        DB::transaction(function () use ($id, $action, $note) {
            $userBeerCount = UserBeerCount::whereKey($id)->lockForUpdate()->firstOrFail();

            if ($action === 'increment') {
                $userBeerCount->increment('count');
            } elseif ($action === 'decrement') {
                if ($userBeerCount->count <= 0) {
                    return;
                }
                $userBeerCount->decrement('count');
            }

            $userBeerCount->update(['last_tasted_at' => now()]);

            TastingLog::create([
                'user_beer_count_id' => $userBeerCount->id,
                'action' => $action,
                'tasted_at' => now(),
                'note' => $note,
            ]);
        });

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
            'userBeerCount' => $userBeerCount,
            'tastingLogs' => $tastingLogs,
        ]);
    }
}
