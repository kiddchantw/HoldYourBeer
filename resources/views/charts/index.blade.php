<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Chart Statistics') }}
        </h2>
    </x-slot>

    <div class="py-12 relative overflow-hidden">
        <x-background />

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filter Section -->
            <div class="mb-6">
                <form method="GET" action="" class="flex flex-wrap items-center gap-4 bg-white bg-opacity-50 backdrop-blur-sm p-4 rounded-lg shadow-sm">
                    <label for="month" class="font-medium text-gray-700 flex items-center">
                        <span class="mr-2">📅</span> {{ __('Filter by Month') }}:
                    </label>
                    <input type="month" id="month" name="month" value="{{ $selectedMonth }}" 
                           class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                           onchange="this.form.submit()">
                    
                    @if($selectedMonth)
                        <a href="{{ request()->url() }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors text-sm font-medium">
                            ✕ {{ __('Clear Filter') }}
                        </a>
                    @endif
                </form>
            </div>

            <!-- Grid Layout: Stacked (Stats top, Chart bottom) -->
            <div class="grid grid-cols-1 gap-6">

                <!-- 上方：統計數據 -->
                <div class="bg-white bg-opacity-50 backdrop-blur-sm overflow-hidden shadow-lg rounded-lg">
                    <div class="p-4 lg:p-6">
                        <h3 class="text-lg lg:text-xl font-bold mb-4 lg:mb-6 text-gray-800">{{ $stats['title'] }}</h3>
                        <div class="text-xs lg:text-sm text-gray-600 mb-4">{{ __('Period') }}: {{ $stats['period'] }}</div>

                        <!-- Stats Grid: 3 cols horizontal on all screens -->
                        <div class="flex gap-2 lg:gap-4 w-full">
                            <!-- 總數量 -->
                            <div class="bg-gradient-to-r from-orange-100 to-orange-200 rounded-lg p-2 lg:p-4 border border-orange-300 flex flex-col justify-center min-w-0 flex-1">
                                <div class="flex flex-col lg:flex-row items-center lg:justify-between text-center lg:text-left">
                                    <div class="w-full">
                                        <div class="text-[10px] sm:text-xs text-orange-700 lg:text-sm leading-tight mb-1 lg:mb-0 truncate">{{ __('Total') }}<span class="hidden lg:inline"> {{ __('Consumption') }}</span></div>
                                        <div class="text-lg sm:text-xl lg:text-3xl font-bold text-orange-800 leading-none">{{ $stats['totalCount'] ?? 0 }}</div>
                                    </div>
                                    <div class="text-xl sm:text-2xl lg:text-4xl opacity-30 mt-1 lg:mt-0">🍺</div>
                                </div>
                            </div>

                            <!-- 品牌數 -->
                            <div class="bg-gradient-to-r from-green-100 to-green-200 rounded-lg p-2 lg:p-4 border border-green-300 flex flex-col justify-center min-w-0 flex-1">
                                <div class="flex flex-col lg:flex-row items-center lg:justify-between text-center lg:text-left">
                                    <div class="w-full">
                                        <div class="text-[10px] sm:text-xs text-green-700 lg:text-sm leading-tight mb-1 lg:mb-0 truncate">{{ __('Brand') }}<span class="hidden lg:inline"> {{ __('Count') }}</span></div>
                                        <div class="text-lg sm:text-xl lg:text-3xl font-bold text-green-800 leading-none">{{ $stats['brandCount'] ?? 0 }}</div>
                                    </div>
                                    <div class="text-xl sm:text-2xl lg:text-4xl opacity-30 mt-1 lg:mt-0">🏷️</div>
                                </div>
                            </div>

                            <!-- 新嘗試 -->
                            <div class="bg-gradient-to-r from-blue-100 to-blue-200 rounded-lg p-2 lg:p-4 border border-blue-300 flex flex-col justify-center min-w-0 flex-1">
                                <div class="flex flex-col lg:flex-row items-center lg:justify-between text-center lg:text-left">
                                    <div class="w-full">
                                        <div class="text-[10px] sm:text-xs text-blue-700 lg:text-sm leading-tight mb-1 lg:mb-0 truncate">{{ __('New') }}<span class="hidden lg:inline"> {{ __('Brands') }}</span></div>
                                        <div class="text-lg sm:text-xl lg:text-3xl font-bold text-blue-800 leading-none">{{ $stats['newTried'] ?? 0 }}</div>
                                    </div>
                                    <div class="text-xl sm:text-2xl lg:text-4xl opacity-30 mt-1 lg:mt-0">⭐</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 下方：品牌分布圓餅圖 -->
                <div class="bg-white bg-opacity-50 backdrop-blur-sm overflow-hidden shadow-lg rounded-lg">
                    <div class="p-6">
                        <div class="flex flex-col sm:flex-row justify-between items-center mb-6">
                            <h3 class="text-xl font-bold text-gray-800">{{ __('Brand Distribution Chart') }}</h3>
                            
                            <!-- Chart Type Switcher -->
                            <div class="flex space-x-2 mt-4 sm:mt-0">
                                <button id="btn-bar" class="px-3 py-1 text-sm rounded-md bg-blue-100 text-blue-700 hover:bg-blue-200 transition-colors active-chart-type">
                                    📊 {{ __('Bar') }}
                                </button>
                                <button id="btn-pie" class="px-3 py-1 text-sm rounded-md bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors">
                                    🥧 {{ __('Pie') }}
                                </button>
                                <button id="btn-line" class="px-3 py-1 text-sm rounded-md bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors">
                                    📈 {{ __('Line') }}
                                </button>
                            </div>
                        </div>

                        @if(count($labels) > 0)
                            <div class="relative h-80">
                                <canvas id="brandChart"></canvas>
                            </div>
                        @else
                            <div class="flex items-center justify-center h-80 text-gray-500">
                                <div class="text-center">
                                    <div class="text-6xl mb-4">📊</div>
                                    <p>{{ __('No consumption data available') }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const labels = @json($labels);
                const data = @json($data);

                if (labels.length === 0) {
                    return;
                }

                const ctx = document.getElementById('brandChart').getContext('2d');
                let currentChart = null;
                let currentType = 'bar'; // Default to bar chart for better readability

                // Color schemes
                const colors = {
                    backgrounds: [
                        'rgba(255, 99, 132, 0.6)',
                        'rgba(54, 162, 235, 0.6)',
                        'rgba(255, 206, 86, 0.6)',
                        'rgba(75, 192, 192, 0.6)',
                        'rgba(153, 102, 255, 0.6)',
                        'rgba(255, 159, 64, 0.6)',
                        'rgba(231, 76, 60, 0.6)',
                        'rgba(46, 204, 113, 0.6)',
                        'rgba(52, 152, 219, 0.6)',
                        'rgba(155, 89, 182, 0.6)'
                    ],
                    borders: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)',
                        'rgba(231, 76, 60, 1)',
                        'rgba(46, 204, 113, 1)',
                        'rgba(52, 152, 219, 1)',
                        'rgba(155, 89, 182, 1)'
                    ]
                };

                /**
                 * Create or update chart
                 */
                function renderChart(type) {
                    if (currentChart) {
                        currentChart.destroy();
                    }

                    // Update active button state
                    document.querySelectorAll('.active-chart-type').forEach(btn => {
                        btn.classList.remove('bg-blue-100', 'text-blue-700');
                        btn.classList.add('bg-gray-100', 'text-gray-600');
                    });
                    const activeBtn = document.getElementById('btn-' + type);
                    if (activeBtn) {
                        activeBtn.classList.remove('bg-gray-100', 'text-gray-600');
                        activeBtn.classList.add('bg-blue-100', 'text-blue-700');
                    }

                    const isHorizontal = type === 'bar';

                    const config = {
                        type: type,
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Total Tastings',
                                data: data,
                                backgroundColor: colors.backgrounds,
                                borderColor: colors.borders,
                                borderWidth: 1,
                                fill: type === 'line' // Fill area for line chart
                            }]
                        },
                        options: {
                            indexAxis: isHorizontal ? 'y' : 'x',
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                title: {
                                    display: false, // Title is already in the HTML
                                    text: 'Brand Consumption Analytics'
                                },
                                legend: {
                                    display: type === 'pie', // Only show legend for pie chart
                                    position: 'bottom'
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.label || '';
                                            if (label) {
                                                label += ': ';
                                            }
                                            const value = context.parsed.y !== null ? context.parsed.y : context.parsed;
                                            // For horizontal bar, value is in x
                                            const val = isHorizontal ? context.parsed.x : value;
                                            
                                            label += val + ' tastings';
                                            return label;
                                        }
                                    }
                                },
                                datalabels: {
                                    display: type === 'pie' || type === 'bar',
                                    anchor: isHorizontal ? 'end' : 'center',
                                    align: isHorizontal ? 'end' : 'center',
                                    formatter: (value, ctx) => {
                                        if (type === 'pie') {
                                            const sum = ctx.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                                            const percentage = (value * 100 / sum).toFixed(1) + '%';
                                            return percentage;
                                        }
                                        return value; // Show value for bar chart
                                    },
                                    color: type === 'pie' ? '#fff' : '#666',
                                    font: {
                                        weight: 'bold'
                                    }
                                }
                            },
                            scales: (type === 'bar' || type === 'line') ? {
                                [isHorizontal ? 'x' : 'y']: {
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: 'Total Tastings'
                                    },
                                    ticks: {
                                        precision: 0
                                    }
                                },
                                [isHorizontal ? 'y' : 'x']: {
                                    title: {
                                        display: true,
                                        text: 'Brands'
                                    }
                                }
                            } : {}
                        },
                        plugins: [ChartDataLabels]
                    };

                    currentChart = new Chart(ctx, config);
                    currentType = type;
                }

                // Event Listeners
                document.getElementById('btn-bar').addEventListener('click', () => renderChart('bar'));
                document.getElementById('btn-pie').addEventListener('click', () => renderChart('pie'));
                document.getElementById('btn-line').addEventListener('click', () => renderChart('line'));

                // Initial render
                renderChart(currentType);
            });
        </script>
    @endpush
</x-app-layout>
