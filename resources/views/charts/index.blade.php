<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Brand Analytics Charts') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    
                    @if(count($labels) > 0)
                        
                        <div class="relative h-96">
                            <canvas id="brandChart"></canvas>
                        </div>
                    @else
                        <p class="text-gray-600">{{ __('No brand consumption data available.') }}</p>
                        <p class="text-sm text-gray-500 mt-2">Labels count: {{ count($labels) }}, Data count: {{ count($data) }}</p>
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

                if (labels.length > 0) {
                    const ctx = document.getElementById('brandChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'pie',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Total Consumption',
                                data: data,
                                backgroundColor: [
                                    'rgba(255, 99, 132, 0.6)',
                                    'rgba(54, 162, 235, 0.6)',
                                    'rgba(255, 206, 86, 0.6)',
                                    'rgba(75, 192, 192, 0.6)',
                                    'rgba(153, 102, 255, 0.6)',
                                    'rgba(255, 159, 64, 0.6)'
                                ],
                                borderColor: [
                                    'rgba(255, 99, 132, 1)',
                                    'rgba(54, 162, 235, 1)',
                                    'rgba(255, 206, 86, 1)',
                                    'rgba(75, 192, 192, 1)',
                                    'rgba(153, 102, 255, 1)',
                                    'rgba(255, 159, 64, 1)'
                                ],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.label || '';
                                            if (label) {
                                                label += ': ';
                                            }
                                            if (context.parsed !== null) {
                                                label += context.parsed;
                                            }
                                            return label;
                                        }
                                    }
                                },
                                datalabels: {
                                    formatter: (value, ctx) => {
                                        const sum = ctx.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                                        const percentage = (value * 100 / sum).toFixed(2) + '%';
                                        return percentage;
                                    },
                                    color: '#fff',
                                }
                            }
                        },
                        plugins: [ChartDataLabels]
                    });
                }
            });
        </script>
    @endpush
</x-app-layout>