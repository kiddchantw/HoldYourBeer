<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class TestOAuthController extends Controller
{
    public function showConfig()
    {
        $redirect = Socialite::driver('google')->stateless()->redirect();
        $redirectUrl = $redirect->getTargetUrl();

        // Parse URL
        $url = parse_url($redirectUrl);
        parse_str($url['query'] ?? '', $params);

        return view('test-oauth', [
            'appUrl' => config('app.url'),
            'clientId' => config('services.google.client_id'),
            'redirectUri' => config('services.google.redirect'),
            'fullOAuthUrl' => $redirectUrl,
            'oauthParams' => $params,
            'actualRedirectUri' => $params['redirect_uri'] ?? 'NOT SET',
        ]);
    }
}
