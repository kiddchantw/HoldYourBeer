<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\UserBeerCount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Beer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChartsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('throttle:data-export')->only('export');
    }

    public function index()
    {
        $user = auth()->user();

        // Eager load relationships and group by brand name
        $brandData = UserBeerCount::where('user_id', $user->id)
            ->with('beer.brand')
            ->get()
            ->mapToGroups(function ($userBeerCount) {
                // Check if beer and brand relationships are loaded
                if ($userBeerCount->beer && $userBeerCount->beer->brand) {
                    return [$userBeerCount->beer->brand->name => $userBeerCount->count];
                }
                return ['Unknown' => 0]; // Handle cases where relationships are missing
            })
            ->map(function ($counts) {
                return $counts->sum();
            });

        // Prepare chart data
        $labels = $brandData->keys()->all();
        $data = $brandData->values()->all();

        // Debug information
        $debug = [
            'user_id' => $user->id,
            'brand_data' => $brandData->toArray(),
            'labels' => $labels,
            'data' => $data,
        ];

        return view('charts.index', compact('labels', 'data', 'debug'));
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
