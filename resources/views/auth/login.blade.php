<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-6" :status="session('status')" />

    <div class="w-full max-w-md">
        <!-- Header -->
        <div class="text-center mb-8">
            <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('Start Your Beer Collection') }}</h3>
            <p class="text-sm text-gray-500 mb-6">{{ __('Begin tracking your favorite beers and discover new ones!') }}</p>
        </div>

        @php
            $loginPostRoute = request()->route()->getName() === 'localized.login' ? 'localized.login' : 'login';
            $loginPostParams = request()->route()->getName() === 'localized.login' ? ['locale' => app()->getLocale()] : [];
        @endphp
        <form method="POST" action="{{ route($loginPostRoute, $loginPostParams) }}" class="space-y-6">
            @csrf

            <!-- Email Address -->
            <div class="space-y-2">
                <x-input-label for="email" :value="__('Email')" class="text-sm font-medium text-gray-700" />
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                        </svg>
                    </div>
                    <x-text-input id="email"
                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-xl shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition duration-200"
                        type="email"
                        name="email"
                        :value="old('email')"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="{{ __('Email') }}" />
                </div>
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Password -->
            <div class="space-y-2">
                <x-input-label for="password" :value="__('Password')" class="text-sm font-medium text-gray-700" />
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <x-text-input id="password"
                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-xl shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition duration-200"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="{{ __('Password') }}" />
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Remember Me & Forgot Password -->
            <div class="flex items-center justify-between">
                <label for="remember_me" class="inline-flex items-center">
                    <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-amber-600 shadow-sm focus:ring-amber-500 focus:ring-offset-0" name="remember">
                    <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                </label>

                @if (Route::has('password.request'))
                    <a class="text-sm text-amber-600 hover:text-amber-700 font-medium transition duration-200" href="{{ route('password.request') }}">
                        {{ __('Forgot your password?') }}
                    </a>
                @endif
            </div>

            <!-- Login Button -->
            <div class="pt-4">
                <x-primary-button class="w-full justify-center py-3 px-4 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 border-0 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                    {{ __('Log in') }}
                </x-primary-button>
            </div>

            <!-- Divider -->
            <div class="relative my-8">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-white text-gray-500">{{ __('Or sign in with') }}</span>
                </div>
            </div>

            <!-- Social Login Buttons -->
            <div class="grid grid-cols-2 gap-4">
                @php
                    $socialRoute = request()->route()->getName() === 'localized.login' ? 'localized.social.redirect' : 'social.redirect';
                    $routeParams = request()->route()->getName() === 'localized.login' ? ['locale' => app()->getLocale()] : [];
                @endphp

                <a href="{{ route($socialRoute, array_merge($routeParams, ['provider' => 'google'])) }}"
                   class="flex items-center justify-center px-4 py-3 border border-gray-300 rounded-xl shadow-sm bg-white text-gray-700 hover:bg-gray-50 hover:shadow-md transition duration-200 group">
                    <svg class="w-5 h-5 mr-2 text-red-500" viewBox="0 0 24 24">
                        <path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="currentColor" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="currentColor" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="currentColor" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    <span class="font-medium">Google</span>
                </a>

                <a href="{{ route($socialRoute, array_merge($routeParams, ['provider' => 'apple'])) }}"
                   class="flex items-center justify-center px-4 py-3 border border-gray-300 rounded-xl shadow-sm bg-white text-gray-700 hover:bg-gray-50 hover:shadow-md transition duration-200 group">
                    <svg class="w-5 h-5 mr-2 text-gray-800" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/>
                    </svg>
                    <span class="font-medium">Apple</span>
                </a>
            </div>

            <!-- Sign Up Link -->
            <div class="text-center pt-6">
                <p class="text-sm text-gray-600">
                    {{ __('Don\'t have an account?') }}
                    @php
                        $registerRoute = request()->route()->getName() === 'localized.login' ? 'localized.register' : 'register';
                        $registerParams = request()->route()->getName() === 'localized.login' ? ['locale' => app()->getLocale()] : [];
                    @endphp
                    <a href="{{ route($registerRoute, $registerParams) }}" class="font-medium text-amber-600 hover:text-amber-700 transition duration-200">
                        {{ __('Sign up now') }}
                    </a>
                </p>
            </div>
        </form>
    </div>
</x-guest-layout>
