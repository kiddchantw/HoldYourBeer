<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('profile.connected_accounts') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('profile.connected_accounts_description') }}
        </p>
    </header>

    <div class="mt-6 space-y-6">
        @foreach(['google', 'apple', 'facebook'] as $provider)
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                <div class="flex items-center space-x-3">
                    <!-- Icon for provider (Optional: Add SVGs) -->
                    <span class="capitalize font-medium text-gray-900">
                        {{ __('profile.providers.'.$provider) }}
                    </span>
                    
                    @if($user->hasOAuthProvider($provider))
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            {{ __('profile.connected') }}
                        </span>
                    @endif
                </div>

                <div>
                    @if($user->hasOAuthProvider($provider))
                        <!-- Details -->
                        <div class="text-sm text-gray-500 mb-2 text-right">
                             @php
                                $linkedProvider = $user->oauthProviders->where('provider', $provider)->first();
                             @endphp
                             @if($linkedProvider->last_used_at)
                                <div title="{{ $linkedProvider->last_used_at }}">{{ __('profile.last_used', ['time' => $linkedProvider->last_used_at->diffForHumans()]) }}</div>
                             @else
                                <div title="{{ $linkedProvider->linked_at }}">{{ __('profile.connected_at', ['time' => $linkedProvider->linked_at->diffForHumans()]) }}</div>
                             @endif
                        </div>

                        <!-- Unlink Button -->
                        @if($user->canUnlinkOAuthProvider())
                            <!-- TODO: Route for unlinking -->
                            <form method="POST" action="#" class="inline-block"> 
                                @csrf
                                @method('DELETE')
                                <x-danger-button disabled class="opacity-50 cursor-not-allowed" title="Functionality coming soon">
                                    {{ __('profile.disconnect') }}
                                </x-danger-button>
                            </form>
                        @else
                             <span class="text-xs text-gray-400" title="{{ __('profile.cannot_disconnect_last') }}">
                                {{ __('profile.disconnect') }} ({{ __('profile.cannot_disconnect_last') }})
                             </span>
                        @endif
                    @else
                        <!-- Link Button -->
                        <a href="{{ route('localized.social.redirect', ['provider' => $provider, 'locale' => app()->getLocale() ?: 'en']) }}" 
                           class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                            {{ __('profile.connect') }}
                        </a>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</section>
