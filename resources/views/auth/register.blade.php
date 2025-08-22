<x-guest-layout>
    <div class="w-full max-w-md">
        <!-- Header -->
        <div class="text-center mb-8">
            <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('Start Your Beer Collection') }}</h3>
            <p class="text-sm text-gray-500 mb-6">{{ __('Begin tracking your favorite beers and discover new ones!') }}</p>
        </div>

        <form method="POST" action="{{ route('localized.register', ['locale' => app()->getLocale() ?: 'en']) }}">
            @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('localized.login', ['locale' => app()->getLocale() ?: 'en']) }}">
                {{ __('Already registered?') }}
            </a>

            <x-beer-button class="ms-4">
                {{ __('Register') }}
            </x-beer-button>
        </div>
    </form>
    </div>
</x-guest-layout>
