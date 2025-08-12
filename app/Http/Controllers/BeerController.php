<?php

namespace App\Http\Controllers;

use App\Models\Beer;
use App\Models\Brand;
use App\Models\UserBeerCount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BeerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('beers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'brand_name' => ['required', 'string', 'min:1', 'max:255'],
            'name' => ['required', 'string', 'min:1', 'max:255'],
            'style' => ['nullable', 'string', 'max:100'],
        ], [
            'brand_name.required' => '品牌名稱為必填項目。',
            'brand_name.string' => '品牌名稱必須是字串格式。',
            'brand_name.min' => '品牌名稱不得為空。',
            'brand_name.max' => '品牌名稱不得超過 255 個字元。',
            'name.required' => '啤酒名稱為必填項目。',
            'name.string' => '啤酒名稱必須是字串格式。',
            'name.min' => '啤酒名稱不得為空。',
            'name.max' => '啤酒名稱不得超過 255 個字元。',
            'style.string' => '啤酒類型必須是字串格式。',
            'style.max' => '啤酒類型不得超過 100 個字元。',
        ]);

        try {
            DB::beginTransaction();

            // Create or find brand
            $brand = Brand::firstOrCreate([
                'name' => trim($request->brand_name)
            ]);

            // Check if user already has this beer
            $existingBeer = Beer::where('brand_id', $brand->id)
                ->where('name', trim($request->name))
                ->first();

            if ($existingBeer) {
                $existingCount = UserBeerCount::where('user_id', Auth::id())
                    ->where('beer_id', $existingBeer->id)
                    ->exists();

                if ($existingCount) {
                    return back()->withErrors([
                        'beer' => '您已經追蹤過此品牌的這款啤酒。'
                    ])->withInput();
                }
            }

            // Create beer if not exists
            $beer = Beer::firstOrCreate([
                'brand_id' => $brand->id,
                'name' => trim($request->name)
            ], [
                'style' => $request->style ? trim($request->style) : null
            ]);

            // Create user beer count with initial count of 1
            UserBeerCount::create([
                'user_id' => Auth::id(),
                'beer_id' => $beer->id,
                'count' => 1,
                'last_tasted_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('dashboard')->with('success', '新啤酒已成功加入您的追蹤列表！');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => '新增啤酒時發生錯誤，請重試。'])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Beer $beer)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Beer $beer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Beer $beer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Beer $beer)
    {
        //
    }

    /**
     * Get brand suggestions for autocomplete
     */
    public function getBrandSuggestions(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $brands = Brand::where('name', 'ILIKE', "%{$query}%")
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name']);

        return response()->json($brands);
    }

    /**
     * Get beer name suggestions for autocomplete based on brand
     */
    public function getBeerSuggestions(Request $request)
    {
        $brandId = $request->get('brand_id');
        $query = $request->get('q', '');
        
        if (!$brandId || strlen($query) < 2) {
            return response()->json([]);
        }

        $beers = Beer::where('brand_id', $brandId)
            ->where('name', 'ILIKE', "%{$query}%")
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name']);

        return response()->json($beers);
    }
}