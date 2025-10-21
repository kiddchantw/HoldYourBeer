<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Chart Statistics') }}
        </h2>
    </x-slot>

    <div class="py-12 relative overflow-hidden">
        <x-background />

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- 1x2 Grid Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <!-- 左側：本月統計 -->
                <div class="bg-white bg-opacity-50 backdrop-blur-sm overflow-hidden shadow-lg rounded-lg">
                    <div class="p-6">
                        <h3 class="text-xl font-bold mb-6 text-gray-800">{{ __('This Month Statistics') }}</h3>
                        <div class="text-sm text-gray-600 mb-4">{{ __('Current Month') }}: {{ date('Y-m') }}</div>

                        <div class="space-y-4">
                            <!-- 總數量 -->
                            <div class="bg-gradient-to-r from-orange-100 to-orange-200 rounded-lg p-4 border border-orange-300">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-sm text-orange-700">{{ __('Total Consumption') }}</div>
                                        <div class="text-3xl font-bold text-orange-800">{{ $monthlyStats['totalCount'] ?? 0 }}</div>
                                    </div>
                                    <div class="text-4xl opacity-30">🍺</div>
                                </div>
                            </div>

                            <!-- 品牌數 -->
                            <div class="bg-gradient-to-r from-green-100 to-green-200 rounded-lg p-4 border border-green-300">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-sm text-green-700">{{ __('Brand Count') }}</div>
                                        <div class="text-3xl font-bold text-green-800">{{ $monthlyStats['brandCount'] ?? 0 }}</div>
                                    </div>
                                    <div class="text-4xl opacity-30">🏷️</div>
                                </div>
                            </div>

                            <!-- 新嘗試 -->
                            <div class="bg-gradient-to-r from-blue-100 to-blue-200 rounded-lg p-4 border border-blue-300">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-sm text-blue-700">{{ __('New Brands Tried') }}</div>
                                        <div class="text-3xl font-bold text-blue-800">{{ $monthlyStats['newTried'] ?? 0 }}</div>
                                    </div>
                                    <div class="text-4xl opacity-30">⭐</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 右側：品牌分布圓餅圖 -->
                <div class="bg-white bg-opacity-50 backdrop-blur-sm overflow-hidden shadow-lg rounded-lg">
                    <div class="p-6">
                        <h3 class="text-xl font-bold mb-6 text-gray-800">{{ __('Brand Distribution Chart') }}</h3>

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
                let currentType = 'pie';

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

                    const config = {
                        type: type,
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Total Tastings',
                                data: data,
                                backgroundColor: colors.backgrounds,
                                borderColor: colors.borders,
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                title: {
                                    display: true,
                                    text: 'Brand Consumption Analytics',
                                    font: {
                                        size: 16
                                    }
                                },
                                legend: {
                                    display: type !== 'bar',
                                    position: 'bottom'
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.label || '';
                                            if (label) {
                                                label += ': ';
                                            }
                                            const value = context.parsed.y || context.parsed;
                                            label += value + ' tastings';
                                            return label;
                                        }
                                    }
                                },
                                datalabels: {
                                    display: type === 'pie',
                                    formatter: (value, ctx) => {
                                        if (type !== 'pie') return '';
                                        const sum = ctx.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                                        const percentage = (value * 100 / sum).toFixed(1) + '%';
                                        return percentage;
                                    },
                                    color: '#fff',
                                    font: {
                                        weight: 'bold'
                                    }
                                }
                            },
                            scales: type === 'bar' || type === 'line' ? {
                                y: {
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: 'Total Tastings'
                                    },
                                    ticks: {
                                        precision: 0
                                    }
                                },
                                x: {
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

                // Initial render
                renderChart(currentType);
            });
        </script>
    @endpush
</x-app-layout>
