<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('brands.titles.index') }}
            </h2>
            <a href="{{ route('admin.brands.create', ['locale' => app()->getLocale()]) }}"
               class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                {{ __('brands.buttons.create') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-100">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-admin.flash-message type="success" />
            <x-admin.flash-message type="error" />

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- 搜尋表單 -->
                    <form method="GET" action="{{ route('admin.brands.index', ['locale' => app()->getLocale()]) }}" class="mb-6">
                        <div class="flex flex-col sm:flex-row gap-4 mb-4">
                            <!-- 搜尋輸入 -->
                            <div class="flex-1">
                                <input type="text"
                                       name="search"
                                       value="{{ request('search') }}"
                                       placeholder="{{ __('brands.search.placeholder') }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <!-- 每頁顯示筆數 -->
                            <div class="w-full sm:w-auto">
                                <select name="per_page"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10 筆/頁</option>
                                    <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15 筆/頁</option>
                                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 筆/頁</option>
                                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 筆/頁</option>
                                </select>
                            </div>

                            <!-- 顯示已刪除 -->
                            <div class="flex items-center">
                                <label class="inline-flex items-center">
                                    <input type="checkbox"
                                           name="show_deleted"
                                           value="1"
                                           {{ request('show_deleted') ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">{{ __('顯示已刪除') }}</span>
                                </label>
                            </div>

                            <!-- 搜尋按鈕 -->
                            <div class="flex gap-2">
                                <button type="submit"
                                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded whitespace-nowrap">
                                    {{ __('brands.buttons.search') }}
                                </button>
                                @if(request('search') || request('show_deleted') || request('per_page') != 15)
                                    <a href="{{ route('admin.brands.index', ['locale' => app()->getLocale()]) }}"
                                       class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded whitespace-nowrap">
                                        {{ __('brands.buttons.clear') }}
                                    </a>
                                @endif
                            </div>
                        </div>

                        <!-- 保留排序參數 -->
                        <input type="hidden" name="sort_by" value="{{ request('sort_by', 'created_at') }}">
                        <input type="hidden" name="sort_order" value="{{ request('sort_order', 'desc') }}">
                    </form>

                    <!-- 搜尋結果提示 -->
                    @if(request('search'))
                        <p class="text-sm text-gray-600 mb-4">
                            {{ __('brands.search.results', ['count' => $brands->total(), 'keyword' => request('search')]) }}
                        </p>
                    @endif

                    @include('admin.brands._list', ['brands' => $brands])

                    <!-- 分頁導航 -->
                    <div class="mt-6">
                        {{ $brands->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
