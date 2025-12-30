<div>
    <div class="flex justify-end mb-4">
        <button wire:click="openCreateModal" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded touch-manipulation min-h-[44px]">
            Create
        </button>
    </div>

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

    <!-- Responsive Table View (both desktop and mobile) -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg rounded-lg border border-gray-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:text-gray-700"
                            wire:click="toggleSort('name')">
                            Name
                            @if($sortBy === 'name')
                                <span>{{ $sortOrder === 'asc' ? '▲' : '▼' }}</span>
                            @endif
                        </th>
                        @if($showDeleted)
                            <th class="hidden sm:table-cell px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('brands.table.deleted_at') }}
                            </th>
                        @endif
                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($brands as $brand)
                        <tr class="hover:bg-gray-50 {{ $brand->trashed() ? 'bg-gray-100' : '' }}">
                            <td class="px-3 sm:px-6 py-4 text-sm font-medium {{ $brand->trashed() ? 'text-gray-500 line-through' : 'text-gray-900' }}">
                                {{ $brand->name }}
                                @if($brand->trashed())
                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                        <span class="hidden sm:inline">{{ __('brands.table.deleted_at') }}</span>
                                        <span class="sm:hidden">Deleted</span>
                                    </span>
                                @endif
                            </td>
                            @if($showDeleted)
                                <td class="hidden sm:table-cell px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $brand->deleted_at ? $brand->deleted_at->setTimezone('Asia/Taipei')->format('Y-m-d H:i') : '-' }}
                                </td>
                            @endif
                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center gap-2 sm:gap-3">
                                    <!-- Info Button -->
                                    <a href="{{ route('admin.brands.show', ['brand' => $brand->id, 'locale' => app()->getLocale()]) }}"
                                       class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 touch-manipulation min-h-[44px]"
                                       title="Info">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                                        </svg>
                                        <span class="hidden sm:inline">Info</span>
                                    </a>

                                    @if(!$brand->trashed())
                                        <!-- Edit Button -->
                                        <button wire:click="openEditModal({{ $brand->id }})"
                                                class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-900 touch-manipulation min-h-[44px]"
                                                title="{{ __('brands.buttons.edit') }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                            </svg>
                                            <span class="hidden sm:inline">Edit</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $showDeleted ? 3 : 2 }}" class="px-6 py-4 text-center text-sm text-gray-500">
                                {{ __('brands.search.no_results') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-3 sm:px-6 py-4">
            {{ $brands->links() }}
        </div>
    </div>

    <!-- Livewire Native Dialog (Modal) - Responsive -->
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-3 sm:px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true" wire:click="$set('showModal', false)"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <!-- Modal panel - Responsive sizing -->
                <div class="inline-block w-full max-w-md p-4 sm:p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-lg">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium leading-6 text-gray-900" id="modal-title">
                            {{ $isEdit ? 'Edit' : 'Create' }}
                        </h3>
                        <!-- Close button for mobile -->
                        <button type="button"
                                wire:click="$set('showModal', false)"
                                class="sm:hidden p-2 rounded-md text-gray-400 hover:text-gray-600 focus:outline-none">
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form wire:submit.prevent="save">
                        <div class="mb-4">
                            <label for="brand-name" class="block text-sm font-medium text-gray-700 mb-2">
                                Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   id="brand-name"
                                   wire:model="name"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 min-h-[44px]"
                                   required>
                            @error('name') <span class="mt-1 text-sm text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex flex-col sm:flex-row sm:justify-end gap-3 mt-6">
                            <button type="submit"
                                    class="w-full sm:w-auto bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded flex items-center justify-center touch-manipulation min-h-[44px] order-1 sm:order-2">
                                <span wire:loading.remove wire:target="save">Submit</span>
                                <span wire:loading wire:target="save">Submitting...</span>
                            </button>
                            <button type="button"
                                    wire:click="$set('showModal', false)"
                                    class="w-full sm:w-auto bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded touch-manipulation min-h-[44px] order-2 sm:order-1">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
