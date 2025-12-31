<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OnboardingController extends Controller
{
    /**
     * 完成導覽
     */
    public function complete(Request $request)
    {
        $user = Auth::user();
        
        if ($user) {
            $user->update([
                'onboarding_completed_at' => now()
            ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * 重新啟動導覽
     */
    public function restart(Request $request)
    {
        $user = Auth::user();
        
        if ($user) {
            $user->update([
                'onboarding_completed_at' => null
            ]);
        }

        return redirect()->route('dashboard')->with('restart_onboarding', true);
    }
}
