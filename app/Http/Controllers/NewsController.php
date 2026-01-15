<?php

namespace App\Http\Controllers;

use App\Models\Beer;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsController extends Controller
{
    /**
     * 顯示 News 頁面
     *
     * 左側區塊：最近新增的啤酒（最新 10 筆）
     * 右側區塊：系統更新說明（預留）
     */
    public function index(Request $request): View
    {
        // 查詢最近新增的啤酒（最新 10 筆，不限時間）
        $recentBeers = Beer::with('brand')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('news.index', [
            'recentBeers' => $recentBeers,
        ]);
    }
}
