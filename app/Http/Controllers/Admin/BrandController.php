<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBrandRequest;
use App\Http\Requests\Admin\UpdateBrandRequest;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $perPage = $request->get('per_page', 15);
        $sortBy = $request->get('sort_by', 'name'); // Default sort by name
        $sortOrder = $request->get('sort_order', 'asc'); // Default order asc
        $showDeleted = $request->get('show_deleted', false);

        // 驗證排序欄位（防止 SQL injection）
        $allowedSortFields = ['id', 'name', 'created_at', 'updated_at'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'name';
        }

        // 驗證排序方向
        $sortOrder = in_array($sortOrder, ['asc', 'desc']) ? $sortOrder : 'asc';

        // 查詢品牌（包含啤酒數量統計）
        $brands = Brand::query()
            ->withCount('beers') // 統計每個品牌的啤酒數量
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%");
            })
            ->when($showDeleted, function ($query) {
                return $query->withTrashed();
            })
            ->orderBy($sortBy, $sortOrder)
            ->paginate($perPage)
            ->appends($request->query());

        return view('admin.brands.index', compact('brands'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.brands.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBrandRequest $request)
    {
        try {
            $brand = Brand::create($request->validated());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => __('brands.messages.created'),
                    'brand' => $brand
                ]);
            }

            return redirect()->route('admin.brands.index', ['locale' => app()->getLocale()])
                ->with('success', __('brands.messages.created'));
        } catch (\Exception $e) {
            \Log::error('Brand creation failed: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => '建立品牌時發生錯誤，請稍後再試',
                    'errors' => ['name' => [$e->getMessage()]]
                ], 500);
            }

            return back()
                ->withInput()
                ->with('error', '建立品牌時發生錯誤，請稍後再試');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($locale, Brand $brand)
    {
        $brand->load('beers'); // Load associated beers
        return view('admin.brands.show', compact('brand'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($locale, Brand $brand)
    {
        return view('admin.brands.edit', compact('brand'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBrandRequest $request, $locale, Brand $brand)
    {
        $brand->update($request->validated());

        // 如果是 AJAX 請求，回傳 JSON
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('brands.messages.updated'),
                'brand' => $brand
            ]);
        }

        // 傳統表單提交，重導向回 Dashboard
        return redirect()->route('admin.brands.index', ['locale' => app()->getLocale()])
            ->with('success', __('brands.messages.updated'));
    }

    /**
     * Remove the specified resource from storage (soft delete).
     */
    public function destroy($locale, Brand $brand)
    {
        $beersCount = $brand->beers()->count();

        if ($beersCount > 0) {
            throw new \App\Exceptions\BrandHasBeersException($brand, $beersCount);
        }

        // 軟刪除（因為 Brand Model 使用了 SoftDeletes trait）
        $brand->delete();
        return redirect()->route('admin.brands.index', ['locale' => app()->getLocale()])
            ->with('success', __('brands.messages.deleted'));
    }

    /**
     * Restore a soft deleted brand.
     */
    public function restore($locale, $id)
    {
        $brand = Brand::withTrashed()->findOrFail($id);
        $brand->restore();

        return redirect()->route('admin.brands.index', ['locale' => app()->getLocale()])
            ->with('success', __('brands.messages.restored'));
    }

    /**
     * Permanently delete a brand.
     */
    public function forceDelete($locale, $id)
    {
        $brand = Brand::withTrashed()->findOrFail($id);

        // 檢查是否有關聯的 Beer（Beer Model 沒有使用 SoftDeletes，所以不用 withTrashed）
        $beersCount = $brand->beers()->count();
        if ($beersCount > 0) {
            throw new \App\Exceptions\BrandHasBeersException($brand, $beersCount);
        }

        $brand->forceDelete();
        return redirect()->route('admin.brands.index', ['locale' => app()->getLocale()])
            ->with('success', __('brands.messages.force_deleted'));
    }
}
