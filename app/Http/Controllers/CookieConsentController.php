<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CookieConsentController extends Controller
{
    /**
     * Store user's cookie consent preference.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'consent' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid consent value.',
                'errors' => $validator->errors(),
            ], 422);
        }

        // 儲存到 session
        $consent = $request->input('consent');
        session(['cookie_consent' => $consent]);

        return response()->json([
            'success' => true,
            'message' => $consent ? 'Cookie consent accepted.' : 'Cookie consent rejected.',
            'consent' => $consent,
        ]);
    }
}
