<div class="flow-root" x-data="{ clearMessage: false, expandedDate: null }" 
     x-init="$wire.on('clear-success-message', () => { setTimeout(() => $wire.clearSuccessMessage(), 3000) })">
    
    <!-- Success Message -->
    @if($successMessage)
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg flex items-center justify-between">
            <span>{{ $successMessage }}</span>
            <button wire:click="clearSuccessMessage" class="text-green-700 hover:text-green-900">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    @endif

    @foreach($groupedLogs as $date => $group)
        <div class="mb-6">
            <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                {{ \Carbon\Carbon::parse($group['date'])->format('M d, Y') }}
            </h3>
            
            <div class="bg-white rounded-lg border border-gray-100 shadow-sm relative">
                <!-- Decorative green dot line like Flutter -->
                <div class="absolute top-0 bottom-0 left-0 w-1 bg-green-500 rounded-l-lg"></div>
                
                <div class="flex">
                    <!-- Main content area -->
                    <div class="flex-1 p-4 pl-3">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center">
                                <svg class="h-5 w-5 text-green-700 mr-2" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M5 2h14a1 1 0 011 1v18a1 1 0 01-1 1H5a1 1 0 01-1-1V3a1 1 0 011-1zm0 2v16h14V4H5zm2 2h10v2H7V6zm0 4h10v2H7v-2z"/>
                                </svg>
                                <span class="font-bold text-lg text-gray-800">
                                    {{ $group['total_daily'] }} {{ __('units') }}
                                </span>
                            </div>
                            <!-- Toggle Arrow Button -->
                            <button 
                                @click="expandedDate = expandedDate === '{{ $date }}' ? null : '{{ $date }}'"
                                class="text-gray-400 hover:text-gray-600 transition-colors p-1"
                            >
                                <svg 
                                    class="w-5 h-5 transition-transform duration-200" 
                                    :class="{ 'rotate-180': expandedDate === '{{ $date }}' }"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        </div>

                        <div class="text-gray-500 text-sm flex items-center mb-2">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <!-- Show the latest time for that day -->
                            {{ $group['logs']->first()->tasted_at->timezone('Asia/Taipei')->format('H:i') }}
                        </div>

                        <!-- Notes -->
                        <div class="space-y-1 mt-2">
                            @foreach($group['logs'] as $log)
                                @if($log->note)
                                    <div class="flex items-start text-gray-500 italic text-sm">
                                        <svg class="w-4 h-4 mr-1 mt-0.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        {{ $log->note }}
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <!-- Action Buttons (slide in from right) -->
                    <div 
                        x-show="expandedDate === '{{ $date }}'"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-x-4"
                        x-transition:enter-end="opacity-100 translate-x-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-x-0"
                        x-transition:leave-end="opacity-0 translate-x-4"
                        class="flex flex-shrink-0"
                    >
                        <!-- Add Button (Orange) -->
                        <button 
                            wire:click="addForDate('{{ $date }}')"
                            class="w-16 min-h-[80px] bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white flex flex-col items-center justify-center transition-colors"
                        >
                            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            <span class="text-xs font-medium">{{ __('Add') }}</span>
                        </button>
                        <!-- Delete Button (Red) -->
                        <button 
                            wire:click="deleteForDate('{{ $date }}')"
                            class="w-16 min-h-[80px] !bg-gradient-to-r !from-red-800 !to-red-900 hover:!from-red-900 hover:!to-red-950 text-white flex flex-col items-center justify-center transition-colors rounded-r-lg"
                            style="background: linear-gradient(to right, rgb(153, 27, 27), rgb(127, 29, 29)) !important;"
                        >
                            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                            </svg>
                            <span class="text-xs font-medium">{{ __('Delete') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <!-- "No logs" state -->
    @if($groupedLogs->isEmpty())
        <div class="text-center py-10 text-gray-400">
            <p>{{ __('No tasting records yet') }}</p>
        </div>
    @endif

    <!-- Add Record Button (Fixed at bottom) - Dashboard Style -->
    <div class="mt-8 text-center">
        <button 
            wire:click="openAddModal"
            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition duration-200"
        >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            {{ __('Add Record') }}
        </button>
    </div>

    <!-- Add Record Modal -->
    @if($showAddModal)
        <template x-teleport="body">
            <div class="fixed inset-0 z-[99] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-data>
                <!-- Background overlay -->
                <div class="fixed inset-0 bg-gray-500 bg-opacity-50 transition-opacity" wire:click="closeAddModal"></div>

                <!-- Modal panel -->
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div class="relative transform overflow-hidden rounded-t-2xl sm:rounded-2xl bg-white text-left shadow-xl transition-all w-full sm:max-w-lg">
                        <!-- Handle bar for mobile -->
                        <div class="flex justify-center pt-3 pb-2 sm:hidden">
                            <div class="w-12 h-1.5 bg-gray-300 rounded-full"></div>
                        </div>

                        <div class="bg-white px-6 pb-6 pt-4">
                            <!-- Modal Title -->
                            <h3 class="text-xl font-bold text-gray-900 mb-6" id="modal-title">
                                {{ __('Add Record') }}
                            </h3>

                            <!-- Quantity Selector -->
                            <div class="mb-6">
                                <label class="block text-gray-700 font-medium mb-3">{{ __('Quantity') }}</label>
                                <div class="flex items-center justify-center space-x-4">
                                    <button 
                                        wire:click="decreaseQuantity"
                                        class="w-12 h-12 rounded-lg border-2 border-gray-300 flex items-center justify-center text-gray-600 hover:border-gray-400 hover:bg-gray-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                        @if($quantity <= 1) disabled @endif
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                        </svg>
                                    </button>
                                    <span class="text-3xl font-bold text-gray-900 w-16 text-center">{{ $quantity }}</span>
                                    <button 
                                        wire:click="increaseQuantity"
                                        class="w-12 h-12 rounded-lg border-2 border-gray-300 flex items-center justify-center text-gray-600 hover:border-gray-400 hover:bg-gray-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                        @if($quantity >= 99) disabled @endif
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                    </button>
                                </div>
                                @error('quantity') 
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Tasting Note -->
                            <div class="mb-6">
                                <label class="block text-gray-700 font-medium mb-2">{{ __('Tasting Note (Optional)') }}</label>
                                <textarea 
                                    wire:model="note"
                                    rows="3"
                                    maxlength="150"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 resize-none"
                                    placeholder="{{ __('How did it taste? (e.g., fruity, slightly bitter...)') }}"
                                ></textarea>
                                @error('note') 
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-400 text-right">{{ strlen($note) }}/150</p>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex items-center space-x-4">
                                <button 
                                    wire:click="closeAddModal"
                                    class="flex-1 py-3 px-4 text-gray-700 font-medium hover:bg-gray-100 rounded-lg transition-colors"
                                >
                                    {{ __('Cancel') }}
                                </button>
                                <button 
                                    wire:click="saveRecord"
                                    wire:loading.attr="disabled"
                                    class="flex-1 py-3 px-4 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-bold rounded-xl shadow-md hover:shadow-lg transition-all disabled:opacity-50"
                                >
                                    <span wire:loading.remove wire:target="saveRecord">{{ __('Save Record') }}</span>
                                    <span wire:loading wire:target="saveRecord">{{ __('Saving...') }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    @endif
</div>
