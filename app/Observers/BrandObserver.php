<?php

namespace App\Observers;

use App\Models\Brand;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BrandObserver
{
    public function created(Brand $brand): void
    {
        $this->clearBrandCache('created', $brand);
    }

    public function updated(Brand $brand): void
    {
        $this->clearBrandCache('updated', $brand);
    }

    public function deleted(Brand $brand): void
    {
        $this->clearBrandCache('deleted', $brand);
    }

    public function restored(Brand $brand): void
    {
        $this->clearBrandCache('restored', $brand);
    }

    protected function clearBrandCache(string $action, Brand $brand): void
    {
        // 清除品牌列表快取
        Cache::forget('brands_list');

        // 清除未來可能的其他快取
        Cache::forget('brand_statistics');
        Cache::forget('beer_charts_data');

        // 開發環境記錄
        if (app()->environment('local')) {
            Log::info('品牌快取已清除', [
                'action' => $action,
                'brand_id' => $brand->id,
                'brand_name' => $brand->name,
            ]);
        }
    }
}

