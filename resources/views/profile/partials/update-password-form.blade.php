@php
    $user = auth()->user();
    $isFirstTimeSettingPassword = $user->canSetPasswordWithoutCurrent();
@endphp

<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            @if($isFirstTimeSettingPassword)
                {{ __('Set Password') }}
            @else
                {{ __('Update Password') }}
            @endif
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            @if($isFirstTimeSettingPassword)
                {{ __('Set a password to enable email/password login in addition to your social account.') }}
            @else
                {{ __('Ensure your account is using a long, random password to stay secure.') }}
            @endif
        </p>
    </header>

    <form method="post" action="{{ route('password.update', ['locale' => app()->getLocale() ?: 'en']) }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        {{-- Only show current password field if user already has a password --}}
        @if(!$isFirstTimeSettingPassword)
        <div>
            <x-input-label for="update_password_current_password" :value="__('Current Password')" />
            <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-1 block w-full" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>
        @endif

        <div>
            <x-input-label for="update_password_password" :value="__('New Password')" />
            <x-text-input id="update_password_password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-beer-button>{{ __('Save') }}</x-beer-button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
