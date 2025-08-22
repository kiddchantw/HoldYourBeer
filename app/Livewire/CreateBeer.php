<?php

namespace App\Livewire;

use App\Models\Beer;
use App\Models\Brand;
use App\Models\UserBeerCount;
use App\Models\TastingLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CreateBeer extends Component
{
    public $brand_name = '';
    public $name = '';
    public $style = '';
    public $note = '';

    public $brand_suggestions = [];
    public $beer_suggestions = [];

    public function updatedBrandName($value)
    {
        if (strlen($value) < 2) {
            $this->brand_suggestions = [];
            return;
        }

        $this->brand_suggestions = Brand::where('name', 'like', '%' . $value . '%')->get()->toArray();
    }

    public function updatedName($value)
    {
        if (strlen($this->brand_name) == 0 || strlen($value) < 2) {
            $this->beer_suggestions = [];
            return;
        }

        $brand = Brand::where('name', $this->brand_name)->first();
        if ($brand) {
            $this->beer_suggestions = Beer::where('brand_id', $brand->id)->where('name', 'like', '%' . $value . '%')->get()->toArray();
        }
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

    public function save()
    {
        $this->validate([
            'brand_name' => ['required', 'string', 'min:1', 'max:255'],
            'name' => ['required', 'string', 'min:1', 'max:255'],
            'style' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            DB::beginTransaction();

            $brand = Brand::firstOrCreate(['name' => trim($this->brand_name)]);

            // 檢查啤酒是否已存在
            $existingBeer = Beer::where('brand_id', $brand->id)
                ->where('name', trim($this->name))
                ->first();

            if ($existingBeer) {
                // 啤酒已存在，檢查用戶是否已在追蹤
                $userBeerCount = UserBeerCount::where('user_id', Auth::id())
                    ->where('beer_id', $existingBeer->id)
                    ->first();

                if ($userBeerCount) {
                    // 用戶已在追蹤，增加計數
                    $userBeerCount->increment('count');
                    $userBeerCount->update(['last_tasted_at' => now()]);

                    TastingLog::create([
                        'user_beer_count_id' => $userBeerCount->id,
                        'action' => 'increment',
                        'note' => $this->note,
                        'tasted_at' => now(),
                    ]);
                    $message = 'Beer count incremented successfully!';
                } else {
                    // 啤酒存在但用戶未追蹤，創建追蹤記錄
                    $userBeerCount = UserBeerCount::create([
                        'user_id' => Auth::id(),
                        'beer_id' => $existingBeer->id,
                        'count' => 1,
                        'last_tasted_at' => now(),
                    ]);

                    TastingLog::create([
                        'user_beer_count_id' => $userBeerCount->id,
                        'action' => 'initial',
                        'note' => $this->note,
                        'tasted_at' => now(),
                    ]);
                    $message = 'Existing beer added to your collection!';
                }
            } else {
                // 創建新啤酒
                $beer = Beer::create([
                    'brand_id' => $brand->id,
                    'name' => trim($this->name),
                    'style' => $this->style ? trim($this->style) : null
                ]);

                $userBeerCount = UserBeerCount::create([
                    'user_id' => Auth::id(),
                    'beer_id' => $beer->id,
                    'count' => 1,
                    'last_tasted_at' => now(),
                ]);

                TastingLog::create([
                    'user_beer_count_id' => $userBeerCount->id,
                    'action' => 'initial',
                    'note' => $this->note,
                    'tasted_at' => now(),
                ]);
                $message = 'New beer added successfully!';
            }

            DB::commit();

            return redirect()->route('localized.dashboard', ['locale' => app()->getLocale() ?: 'en'])->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Beer creation error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'brand_name' => $this->brand_name,
                'beer_name' => $this->name,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->addError('name', 'Error adding beer. Please try again. Error: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.create-beer');
    }
}
