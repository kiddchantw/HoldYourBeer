<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-gray-600 mt-1">
                {{ __('Welcome to the Admin Dashboard!') }}
            </p>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-100">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div x-data="{
                        activeTab: '{{ is_string(request('tab')) ? request('tab') : 'users' }}',
                        init() {
                            // 支援 URL 參數切換 tab
                            const urlParams = new URLSearchParams(window.location.search);
                            if (urlParams.get('tab')) {
                                this.activeTab = urlParams.get('tab');
                            }
                        }
                    }">
                        <!-- Tab Navigation -->
                        <div class="border-b border-gray-200 mb-6">
                            <nav class="-mb-px flex space-x-8">
                                <button
                                    @click="activeTab = 'users'; window.history.pushState({}, '', '{{ route('admin.dashboard', ['locale' => app()->getLocale()]) }}?tab=users')"
                                    :class="activeTab === 'users'
                                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                    {{ __('Users') }}
                                </button>
                                <button
                                    @click="activeTab = 'brands'; window.history.pushState({}, '', '{{ route('admin.dashboard', ['locale' => app()->getLocale()]) }}?tab=brands')"
                                    :class="activeTab === 'brands'
                                        ? 'border-blue-500 text-blue-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                    {{ __('brands.menu') }}
                                </button>
                            </nav>
                        </div>

                        <!-- Users Tab Content -->
                        <div x-show="activeTab === 'users'" x-transition>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('All Users') }}</h3>

                            @php
                                $users = \App\Models\User::all()->map(function ($user) {
                                    $registrationMethod = 'email';
                                    if ($user->google_id) {
                                        $registrationMethod = 'Google';
                                    } elseif ($user->apple_id) {
                                        $registrationMethod = 'Apple';
                                    }
                                    return [
                                        'id' => $user->id,
                                        'name' => $user->name,
                                        'email' => $user->email,
                                        'registration_method' => $registrationMethod,
                                        'created_at' => $user->created_at,
                                    ];
                                });
                            @endphp

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('ID') }}</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Name') }}</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Email') }}</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Registration Method') }}</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Created At') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($users as $user)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $user['id'] }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $user['name'] }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user['email'] }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                        {{ $user['registration_method'] === 'Google' ? 'bg-blue-100 text-blue-800' :
                                                           ($user['registration_method'] === 'Apple' ? 'bg-gray-100 text-gray-800' : 'bg-green-100 text-green-800') }}">
                                                        {{ $user['registration_method'] }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $user['created_at'] ? $user['created_at']->setTimezone('Asia/Taipei')->format('Y-m-d H:i') : '-' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Brands Tab Content -->
                        <div x-show="activeTab === 'brands'" x-transition
                             x-data="{
                                 showEditModal: false,
                                 editBrandId: null,
                                 editBrandName: '',
                                 submitting: false,
                                 errors: {}
                             }"
                             @open-edit-modal.window="
                                 showEditModal = true;
                                 editBrandId = $event.detail.id;
                                 editBrandName = $event.detail.name;
                                 errors = {};
                             ">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900">{{ __('All Brands') }}</h3>
                                <a href="{{ route('admin.brands.create', ['locale' => app()->getLocale()]) }}"
                                   class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    {{ __('brands.buttons.create') }}
                                </a>
                            </div>

                            <x-admin.flash-message type="success" />
                            <x-admin.flash-message type="error" />

                            @php
                                // 取得排序參數
                                $sortBy = request('sort_by', 'name');
                                $sortOrder = request('sort_order', 'asc');
                                $showDeleted = request('show_deleted', false);

                                // 驗證排序欄位（防止 SQL injection）
                                $allowedSortFields = ['id', 'name', 'created_at', 'updated_at'];
                                if (!in_array($sortBy, $allowedSortFields)) {
                                    $sortBy = 'name';
                                }

                                // 驗證排序方向
                                $sortOrder = in_array($sortOrder, ['asc', 'desc']) ? $sortOrder : 'asc';

                                // 查詢品牌（包含啤酒數量統計）
                                $brandsQuery = \App\Models\Brand::withCount('beers');

                                if ($showDeleted) {
                                    $brandsQuery->withTrashed();
                                }

                                $brands = $brandsQuery->orderBy($sortBy, $sortOrder)->get();
                            @endphp

                            @include('admin.brands._list', ['brands' => $brands])

                            <!-- Edit Brand Modal -->
                            <div x-show="showEditModal"
                                 x-cloak
                                 class="fixed inset-0 z-50 overflow-y-auto"
                                 aria-labelledby="modal-title"
                                 role="dialog"
                                 aria-modal="true">
                                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                                    <!-- Background overlay -->
                                    <div x-show="showEditModal"
                                         x-transition:enter="ease-out duration-300"
                                         x-transition:enter-start="opacity-0"
                                         x-transition:enter-end="opacity-100"
                                         x-transition:leave="ease-in duration-200"
                                         x-transition:leave-start="opacity-100"
                                         x-transition:leave-end="opacity-0"
                                         class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
                                         @click="showEditModal = false"
                                         aria-hidden="true"></div>

                                    <!-- Center modal -->
                                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                                    <!-- Modal panel -->
                                    <div x-show="showEditModal"
                                         x-transition:enter="ease-out duration-300"
                                         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                         x-transition:leave="ease-in duration-200"
                                         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                         class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-lg">
                                        <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4" id="modal-title">
                                            {{ __('brands.titles.edit') }}
                                        </h3>

                                        <form @submit.prevent="
                                            submitting = true;
                                            errors = {};
                                            fetch('{{ route('admin.brands.update', ['locale' => app()->getLocale(), 'brand' => '__ID__']) }}'.replace('__ID__', editBrandId), {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/json',
                                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                    'Accept': 'application/json'
                                                },
                                                body: JSON.stringify({
                                                    _method: 'PUT',
                                                    name: editBrandName
                                                })
                                            })
                                            .then(response => response.json())
                                            .then(data => {
                                                submitting = false;
                                                if (data.success) {
                                                    showEditModal = false;
                                                    window.location.reload();
                                                } else if (data.errors) {
                                                    errors = data.errors;
                                                }
                                            })
                                            .catch(error => {
                                                submitting = false;
                                                alert('{{ __('brands.messages.error') }}');
                                            });
                                        ">
                                            <div class="mb-4">
                                                <label for="edit-brand-name" class="block text-sm font-medium text-gray-700 mb-2">
                                                    {{ __('brands.attributes.name') }}
                                                </label>
                                                <input type="text"
                                                       id="edit-brand-name"
                                                       x-model="editBrandName"
                                                       :disabled="submitting"
                                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                       required>
                                                <p x-show="errors.name" x-text="errors.name ? errors.name[0] : ''" class="mt-1 text-sm text-red-600"></p>
                                            </div>

                                            <div class="flex justify-end space-x-3 mt-4">
                                                <button type="submit"
                                                        :disabled="submitting"
                                                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50 flex items-center">
                                                    <svg x-show="submitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                    </svg>
                                                    <span x-text="submitting ? '{{ __('brands.buttons.submitting') }}...' : '{{ __('brands.buttons.submit') }}'"></span>
                                                </button>
                                                <button type="button"
                                                        @click="showEditModal = false"
                                                        :disabled="submitting"
                                                        class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50">
                                                    {{ __('brands.buttons.cancel') }}
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
