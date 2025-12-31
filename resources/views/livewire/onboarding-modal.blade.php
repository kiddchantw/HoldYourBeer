<div>
    @if($show)
    <!-- Onboarding Welcome Modal -->
    <div x-data="{ show: @entangle('show') }" 
         x-show="show"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true">
        
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

        <!-- Modal Content -->
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-center shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                
                <!-- Beer Icon -->
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-amber-100 mb-4">
                    <span class="text-4xl">🍺</span>
                </div>

                <!-- Title -->
                <h3 class="text-2xl font-bold text-gray-900 mb-3" id="modal-title">
                    {{ __('歡迎使用 HoldYourBeer！') }}
                </h3>

                <!-- Description -->
                <div class="mt-2 mb-6">
                    <p class="text-base text-gray-600 mb-2">
                        {{ __('想要快速了解如何使用嗎？') }}
                    </p>
                    <p class="text-sm text-gray-500">
                        {{ __('只需要 1 分鐘！') }}
                    </p>
                </div>

                <!-- Buttons -->
                <div class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
                    <!-- Start Tour Button -->
                    <button 
                        type="button"
                        wire:click="startOnboarding"
                        class="inline-flex w-full justify-center rounded-md bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-amber-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-600 sm:col-start-2 transition-colors">
                        {{ __('開始導覽') }}
                    </button>

                    <!-- Skip Button -->
                    <button 
                        type="button"
                        wire:click="skipOnboarding"
                        class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-4 py-2.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:col-start-1 sm:mt-0 transition-colors">
                        {{ __('稍後再說') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
