<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Brand Analytics Charts') }}
        </h2>
    </x-slot>

    <div class="py-12 relative overflow-hidden">
        <x-background />

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    @if(count($labels) > 0)
                        <!-- Controls Section -->
                        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <!-- Chart Type Switcher -->
                            <div role="group" aria-label="Chart type selection">
                                <label for="chartType" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('Chart Type') }}:
                                </label>
                                <div class="inline-flex rounded-md shadow-sm" role="group">
                                    <button type="button"
                                            class="chart-type-btn px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-l-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700"
                                            data-type="bar"
                                            aria-label="Switch to bar chart">
                                        <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                            <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"></path>
                                        </svg>
                                        Bar
                                    </button>
                                    <button type="button"
                                            class="chart-type-btn px-4 py-2 text-sm font-medium text-gray-900 bg-white border-t border-b border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 active"
                                            data-type="pie"
                                            aria-label="Switch to pie chart"
                                            aria-pressed="true">
                                        <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                            <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"></path>
                                            <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"></path>
                                        </svg>
                                        Pie
                                    </button>
                                    <button type="button"
                                            class="chart-type-btn px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-r-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700"
                                            data-type="line"
                                            aria-label="Switch to line chart">
                                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                                        </svg>
                                        Line
                                    </button>
                                </div>
                            </div>

                            <!-- Export Buttons -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('Export Data') }}:
                                </label>
                                <div class="inline-flex rounded-md shadow-sm" role="group" aria-label="Export options">
                                    <a href="{{ route('charts.export', ['format' => 'csv']) }}"
                                       class="px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-l-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700"
                                       aria-label="Export as CSV">
                                        <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                        </svg>
                                        CSV
                                    </a>
                                    <a href="{{ route('charts.export', ['format' => 'json']) }}"
                                       class="px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-r-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700"
                                       aria-label="Export as JSON">
                                        <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                        </svg>
                                        JSON
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Chart Container with ARIA attributes -->
                        <div class="relative h-96"
                             role="img"
                             aria-label="Brand analytics chart showing consumption data across different beer brands">
                            <canvas id="brandChart" aria-label="Interactive chart displaying brand consumption statistics"></canvas>
                        </div>

                        <!-- Screen Reader Accessible Data Table -->
                        <div class="sr-only" role="region" aria-label="Brand analytics data table">
                            <table>
                                <caption>Brand Consumption Data</caption>
                                <thead>
                                    <tr>
                                        <th>Brand</th>
                                        <th>Total Tastings</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($labels as $index => $label)
                                        <tr>
                                            <td>{{ $label }}</td>
                                            <td>{{ $data[$index] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div role="status" aria-live="polite">
                            <p class="text-gray-600">{{ __('No brand consumption data available.') }}</p>
                            <p class="text-sm text-gray-500 mt-2">{{ __('Start tracking beers to see your analytics.') }}</p>
                        </div>
                    @endif
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
                        'rgba(255, 99, 255, 0.6)',
                        'rgba(99, 255, 132, 0.6)',
                    ],
                    borders: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)',
                        'rgba(255, 99, 255, 1)',
                        'rgba(99, 255, 132, 1)',
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

                    // Announce chart update to screen readers
                    announceChartUpdate(type);
                }

                /**
                 * Announce chart updates for screen readers
                 */
                function announceChartUpdate(type) {
                    const announcement = document.createElement('div');
                    announcement.setAttribute('role', 'status');
                    announcement.setAttribute('aria-live', 'polite');
                    announcement.className = 'sr-only';
                    announcement.textContent = `Chart type changed to ${type}. Chart displays ${labels.length} brands with consumption data.`;
                    document.body.appendChild(announcement);
                    setTimeout(() => document.body.removeChild(announcement), 1000);
                }

                /**
                 * Handle chart type button clicks
                 */
                const chartTypeButtons = document.querySelectorAll('.chart-type-btn');
                chartTypeButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const type = this.getAttribute('data-type');

                        // Update active state
                        chartTypeButtons.forEach(btn => {
                            btn.classList.remove('active', 'bg-blue-700', 'text-white');
                            btn.setAttribute('aria-pressed', 'false');
                        });
                        this.classList.add('active', 'bg-blue-700', 'text-white');
                        this.setAttribute('aria-pressed', 'true');

                        // Render chart
                        renderChart(type);
                    });
                });

                // Initial render
                renderChart(currentType);
            });
        </script>
    @endpush
</x-app-layout>