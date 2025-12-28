<?php

namespace App\Livewire;

use App\Models\Beer;
use App\Models\Brand;
use App\Models\Shop;
use App\Models\UserBeerCount;
use App\Models\TastingLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CreateBeer extends Component
{
    // 步驟控制
    public $currentStep = 1;
    public $totalSteps = 2;

    // 步驟 1：基本資訊
    public $brand_name = '';
    public $name = '';
    
    // 步驟 2：詳細資訊
    public $shop_name = '';
    public $note = '';
    public $quantity = 1; // 預設數量

    // 自動完成建議
    public $brand_suggestions = [];
    public $beer_suggestions = [];
    public $shop_suggestions = [];

    // 搜尋品牌建議
    public function updatedBrandName($value)
    {
        \Log::info('DEBUG: updatedBrandName triggered', ['value' => $value, 'length' => strlen($value)]);

        if (strlen($value) < 1) {
            $this->brand_suggestions = [];
            return;
        }

        $this->brand_suggestions = Brand::where('name', 'like', '%' . $value . '%')
            ->orderBy('name')
            ->limit(5)
            ->get()
            ->toArray();
            
        \Log::info('DEBUG: Suggestions found', ['count' => count($this->brand_suggestions)]);
    }

    // 搜尋啤酒建議
    public function updatedName($value)
    {
        if (strlen($this->brand_name) == 0 || strlen($value) < 1) {
            $this->beer_suggestions = [];
            return;
        }

        $brand = Brand::where('name', $this->brand_name)->first();
        if ($brand) {
            $this->beer_suggestions = Beer::where('brand_id', $brand->id)
                ->where('name', 'like', '%' . $value . '%')
                ->orderBy('name')
                ->limit(5)
                ->get()
                ->toArray();
        }
    }

    // 搜尋店家建議
    public function updatedShopName($value)
    {
        if (strlen($value) < 1) {
            $this->shop_suggestions = [];
            return;
        }

        // 搜尋店家，並關聯計算被選擇的次數（熱門度）
        $this->shop_suggestions = Shop::where('name', 'like', '%' . $value . '%')
            ->withCount(['tastingLogs as total_reports'])
            ->orderByDesc('total_reports')
            ->orderBy('name')
            ->limit(5)
            ->get()
            ->toArray();
    }

    public function selectBrand($name)
    {
        $this->brand_name = $name;
        $this->brand_suggestions = [];
    }

    public function selectBeer($name)
    {
        $this->name = $name;
        $this->beer_suggestions = [];
    }

    public function selectShop($name)
    {
        $this->shop_name = $name;
        $this->shop_suggestions = [];
    }

    public function incrementQuantity()
    {
        $this->quantity++;
    }

    public function decrementQuantity()
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }

    public function nextStep()
    {
        $this->validate([
            'brand_name' => ['required', 'string', 'min:1', 'max:255'],
            'name' => ['required', 'string', 'min:1', 'max:255'],
        ]);

        $this->currentStep = 2;
    }

    public function previousStep()
    {
        $this->currentStep = 1;
    }

    public function save()
    {
        $this->validate([
            'brand_name' => ['required', 'string', 'min:1', 'max:255'],
            'name' => ['required', 'string', 'min:1', 'max:255'],
            'shop_name' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:1000'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        try {
            DB::beginTransaction();

            // 1. 處理 Brand (不區分大小寫)
            $brandName = trim($this->brand_name);
            $brand = Brand::whereRaw('LOWER(name) = ?', [strtolower($brandName)])->first();
            
            if (!$brand) {
                $brand = Brand::create(['name' => $brandName]);
            }

            // 2. 處理 Beer
            $beer = Beer::firstOrCreate(
                ['brand_id' => $brand->id, 'name' => trim($this->name)],
                ['style' => null]
            );

            // 3. 處理 Shop (如果有的話)
            $shopId = null;
            if (!empty($this->shop_name)) {
                $shop = Shop::firstOrCreate(['name' => trim($this->shop_name)]);
                $shopId = $shop->id;
            }

            // 4. 處理 UserBeerCount (用戶收藏)
            // 先取得或建立初始狀態
            $userBeerCount = UserBeerCount::firstOrCreate(
                ['user_id' => Auth::id(), 'beer_id' => $beer->id],
                ['count' => 0, 'last_tasted_at' => now()]
            );

            // 檢查是否為新建立的記錄 (count == 0 且 wasRecentlyCreated)
            // 但如果剛建立，count 是 0。
            // 我們使用 wasRecentlyCreated 來判斷是否為 "initial"
            $isInitial = $userBeerCount->wasRecentlyCreated;

            // 增加計數
            $userBeerCount->increment('count', $this->quantity);
            $userBeerCount->update(['last_tasted_at' => now()]);

            // 5. 創建 Tasting Log
            TastingLog::create([
                'user_beer_count_id' => $userBeerCount->id,
                'action' => $isInitial ? 'initial' : 'increment',
                'shop_id' => $shopId,
                'note' => $this->note,
                'tasted_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('localized.dashboard', ['locale' => app()->getLocale() ?: 'en'])
                ->with('success', __('Beer saved successfully!'));

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Beer creation error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'data' => $this->all()
            ]);
            $this->addError('name', 'Error adding beer. Please try again.');
        }
    }

    public function render()
    {
        return view('livewire.create-beer');
    }
}
