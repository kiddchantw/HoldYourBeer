<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                @php
                    $sortableColumn = function($column, $label) {
                        $currentSort = request('sort_by', 'name');
                        $currentOrder = request('sort_order', 'asc');
                        $newOrder = ($currentSort === $column && $currentOrder === 'asc') ? 'desc' : 'asc';
                        // 保留所有查詢參數，並確保 tab=brands
                        $params = array_merge(request()->query(), [
                            'tab' => 'brands',
                            'sort_by' => $column,
                            'sort_order' => $newOrder
                        ]);
                        $url = request()->fullUrlWithQuery($params);
                        return compact('url', 'currentSort', 'currentOrder', 'column', 'label', 'newOrder');
                    };

                    $nameSort = $sortableColumn('name', __('brands.table.name'));
                @endphp

                <th class="px-6 py-3 text-left">
                    <a href="{{ $nameSort['url'] }}" class="text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                        {{ $nameSort['label'] }}
                        @if($nameSort['currentSort'] === $nameSort['column'])
                            <span>{{ $nameSort['currentOrder'] === 'asc' ? '▲' : '▼' }}</span>
                        @endif
                    </a>
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('brands.table.beers_count') }}</th>
                @if(request('show_deleted'))
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('brands.table.deleted_at') }}</th>
                @endif
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('brands.table.actions') }}</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($brands as $brand)
                <tr class="hover:bg-gray-50 {{ $brand->trashed() ? 'bg-gray-100' : '' }}">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $brand->trashed() ? 'text-gray-500 line-through' : 'text-gray-900' }}">
                        {{ $brand->name }}
                        @if($brand->trashed())
                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                {{ __('brands.table.deleted_at') }}
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ $brand->beers_count ?? 0 }}
                        </span>
                    </td>
                    @if(request('show_deleted'))
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $brand->deleted_at ? $brand->deleted_at->setTimezone('Asia/Taipei')->format('Y-m-d H:i') : '-' }}
                        </td>
                    @endif
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        @if($brand->trashed())
                            <!-- 恢復按鈕 -->
                            <form action="{{ route('admin.brands.restore', ['locale' => app()->getLocale(), 'id' => $brand->id]) }}"
                                  method="POST"
                                  class="inline">
                                @csrf
                                <button type="submit" class="text-green-600 hover:text-green-900 mr-3" title="{{ __('brands.buttons.restore') }}">
                                    <!-- Heroicons: arrow-path -->
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                      <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                    </svg>
                                </button>
                            </form>
                            <!-- 永久刪除按鈕 -->
                            <form action="{{ route('admin.brands.force-delete', ['locale' => app()->getLocale(), 'id' => $brand->id]) }}"
                                  method="POST"
                                  class="inline"
                                  onsubmit="return confirm('{{ __('brands.confirm.force_delete') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900" title="{{ __('brands.buttons.force_delete') }}">
                                    <!-- Heroicons: trash -->
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                      <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                    </svg>
                                </button>
                            </form>
                        @else
                            <!-- 編輯按鈕（開啟 Dialog）-->
                            <button @click="openEditModal({{ $brand->id }}, '{{ $brand->name }}')"
                               class="text-blue-600 hover:text-blue-900 mr-3 inline-block" title="{{ __('brands.buttons.edit') }}">
                                <!-- Heroicons: pencil-square -->
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                </svg>
                            </button>
                            <!-- 刪除按鈕 -->
                            <form action="{{ route('admin.brands.destroy', ['locale' => app()->getLocale(), 'brand' => $brand->id]) }}"
                                  method="POST"
                                  class="inline"
                                  onsubmit="return confirm('{{ __('brands.confirm.delete') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900" title="{{ __('brands.buttons.delete') }}">
                                    <!-- Heroicons: trash -->
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                      <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                    </svg>
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ request('show_deleted') ? 4 : 3 }}" class="px-6 py-4 text-center text-sm text-gray-500">
                        {{ __('brands.search.no_results') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
