<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\UserBeerCount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Beer;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChartsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('throttle:data-export')->only('export');
    }

    public function index(Request $request)
    {
        \Log::info('ChartsController index method called');
        $user = auth()->user();
        \Log::info('User ID: ' . $user->id);

        $selectedMonth = $request->input('month');

        // Ensure selectedMonth is a string (prevent htmlspecialchars error if array is passed)
        if (is_array($selectedMonth)) {
            $selectedMonth = null;
        }
        
        // Base query for user's beer counts
        $query = UserBeerCount::where('user_id', $user->id);
        
        // Apply date filter if selected
        if ($selectedMonth) {
            $startDate = Carbon::parse($selectedMonth)->startOfMonth();
            $endDate = Carbon::parse($selectedMonth)->endOfMonth();
            
            // For chart data (consumption), we use updated_at as it reflects when the count was updated
            $query->whereBetween('updated_at', [$startDate, $endDate]);
            
            $periodLabel = $startDate->format('Y-m');
            $statsTitle = __('Statistics for :period', ['period' => $periodLabel]);
        } else {
            $periodLabel = __('All Time');
            $statsTitle = __('All Time Statistics');
        }

        // 1. Chart Data (Brand Distribution)
        // We need to clone the query because we'll use it for stats too, 
        // but for the chart we need to group by brand.
        // Note: The original query logic for $brandData was slightly different (fetching all then mapping).
        // To support filtering efficiently, we should filter first.
        
        $filteredBeerCounts = $query->with('beer.brand')->get();

        $brandData = $filteredBeerCounts
            ->mapToGroups(function ($userBeerCount) {
                if ($userBeerCount->beer && $userBeerCount->beer->brand) {
                    return [$userBeerCount->beer->brand->name => $userBeerCount->count];
                }
                return ['Unknown' => 0];
            })
            ->map(function ($counts) {
                return $counts->sum();
            });

        // 2. Statistics Cards
        
        // Total Consumption (Sum of counts in the period)
        $totalCount = $filteredBeerCounts->sum('count');

        // Brand Count (Unique brands in the period)
        $brandCount = $filteredBeerCounts
            ->pluck('beer.brand.name')
            ->filter()
            ->unique()
            ->count();

        // New Brands Tried
        // Logic: "New Tried" usually means created_at is within the period.
        // If we are in "All Time" mode, it's just total count of entries.
        // If we are in "Month" mode, it's entries created in that month.
        $newTriedQuery = UserBeerCount::where('user_id', $user->id);
        if ($selectedMonth) {
            $startDate = Carbon::parse($selectedMonth)->startOfMonth();
            $endDate = Carbon::parse($selectedMonth)->endOfMonth();
            $newTriedQuery->whereBetween('created_at', [$startDate, $endDate]);
        }
        $newTried = $newTriedQuery->count();

        $stats = [
            'totalCount' => $totalCount,
            'brandCount' => $brandCount,
            'newTried' => $newTried,
            'title' => (string)$statsTitle,
            'period' => (string)$periodLabel,
        ];

        \Log::info('Stats calculated', $stats);

        // Prepare chart data
        $labels = $brandData->keys()->all();
        $data = $brandData->values()->all();

        // Debug information
        $debug = [
            'user_id' => $user->id,
            'selected_month' => $selectedMonth,
            'stats' => $stats,
            'labels_count' => count($labels),
        ];

        return view('charts.index', compact('labels', 'data', 'stats', 'debug', 'selectedMonth'));
    }

    /**
     * Export brand analytics data
     *
     * @param Request $request
     * @return \Illuminate\Http\Response|StreamedResponse
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'csv');

        // Validate format
        if (!in_array($format, ['csv', 'json'])) {
            return back()->withErrors(['format' => 'Invalid export format. Only CSV and JSON are supported.']);
        }

        $user = auth()->user();

        // Get brand analytics data with additional details
        $brandData = UserBeerCount::where('user_id', $user->id)
            ->with('beer.brand')
            ->get()
            ->mapToGroups(function ($userBeerCount) {
                if ($userBeerCount->beer && $userBeerCount->beer->brand) {
                    return [$userBeerCount->beer->brand->name => [
                        'count' => $userBeerCount->count,
                        'beer_id' => $userBeerCount->beer->id,
                        'beer_name' => $userBeerCount->beer->name,
                    ]];
                }
                return [];
            })
            ->map(function ($items, $brandName) {
                return [
                    'brand' => $brandName,
                    'total_tastings' => $items->sum('count'),
                    'unique_beers' => $items->count(),
                    'beers' => $items->pluck('beer_name')->unique()->values()->all(),
                ];
            });

        if ($format === 'csv') {
            return $this->exportToCsv($brandData);
        }

        return $this->exportToJson($brandData);
    }

    /**
     * Export data to CSV format
     *
     * @param \Illuminate\Support\Collection $data
     * @return StreamedResponse
     */
    private function exportToCsv($data): StreamedResponse
    {
        $filename = 'brand-analytics-' . now()->format('Y-m-d-His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');

            // Add BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // CSV header
            fputcsv($file, ['Brand', 'Total Tastings', 'Unique Beers', 'Beer Names']);

            // CSV rows
            foreach ($data as $row) {
                fputcsv($file, [
                    $row['brand'],
                    $row['total_tastings'],
                    $row['unique_beers'],
                    implode(', ', $row['beers']),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export data to JSON format
     *
     * @param \Illuminate\Support\Collection $data
     * @return \Illuminate\Http\JsonResponse
     */
    private function exportToJson($data)
    {
        $filename = 'brand-analytics-' . now()->format('Y-m-d-His') . '.json';

        $exportData = [
            'exported_at' => now()->toIso8601String(),
            'user_id' => auth()->id(),
            'data' => $data->values()->all(),
            'summary' => [
                'total_brands' => $data->count(),
                'total_tastings' => $data->sum('total_tastings'),
                'total_unique_beers' => $data->sum('unique_beers'),
            ],
        ];

        return response()->json($exportData, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
