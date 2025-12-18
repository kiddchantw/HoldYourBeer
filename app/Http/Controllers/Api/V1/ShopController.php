<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @group V1 - Shop Suggestions
 *
 * APIs for shop autocomplete suggestions
 */
class ShopController extends Controller
{
    /**
     * Get shop suggestions for autocomplete
     *
     * Returns a list of shop names matching the query, sorted by popularity (report_count).
     *
     * @authenticated
     *
     * @queryParam query string required The search query (min 2 characters). Example: 全
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "全聯福利中心",
     *       "total_reports": 25
     *     },
     *     {
     *       "id": 2,
     *       "name": "全家便利商店",
     *       "total_reports": 18
     *     }
     *   ]
     * }
     */
    public function suggestions(Request $request)
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'min:1', 'max:255'],
        ]);

        $query = $validated['query'];

        // Get shops with total report count (sum of all beer_shop.report_count)
        $shops = Shop::select('shops.id', 'shops.name')
            ->leftJoin('beer_shop', 'shops.id', '=', 'beer_shop.shop_id')
            ->where('shops.name', 'like', $query . '%')
            ->groupBy('shops.id', 'shops.name')
            ->selectRaw('COALESCE(SUM(beer_shop.report_count), 0) as total_reports')
            ->orderByDesc('total_reports')
            ->limit(10)
            ->get();

        return response()->json([
            'data' => $shops->map(function ($shop) {
                return [
                    'id' => $shop->id,
                    'name' => $shop->name,
                    'total_reports' => (int) $shop->total_reports,
                ];
            }),
        ]);
    }
}
