<div>
    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <button wire:click="confirmDelete"
            class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded touch-manipulation min-h-[44px] w-full sm:w-auto">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 inline-block mr-2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
        </svg>
        {{ __('Delete Brand') }}
    </button>

    <!-- Confirmation Modal -->
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-3 sm:px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true" wire:click="$set('showDeleteModal', false)"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <!-- Modal panel -->
                <div class="inline-block w-full max-w-md p-4 sm:p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-lg">
                    <div class="flex items-center mb-4">
                        <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium leading-6 text-gray-900" id="modal-title">
                                {{ __('Delete Brand') }}
                            </h3>
                        </div>
                    </div>

                    <div class="mb-4">
                        <p class="text-sm text-gray-500">
                            {{ __('Are you sure you want to delete this brand? This action cannot be undone.') }}
                        </p>
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                        <button type="button"
                                wire:click="$set('showDeleteModal', false)"
                                class="w-full sm:w-auto bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded touch-manipulation min-h-[44px]">
                            {{ __('Cancel') }}
                        </button>
                        <button type="button"
                                wire:click="delete"
                                class="w-full sm:w-auto bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded touch-manipulation min-h-[44px]">
                            <span wire:loading.remove wire:target="delete">{{ __('Delete') }}</span>
                            <span wire:loading wire:target="delete">{{ __('Deleting...') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
