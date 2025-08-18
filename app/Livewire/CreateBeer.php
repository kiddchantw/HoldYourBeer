<?php

namespace App\Livewire;

use App\Models\Beer;
use App\Models\Brand;
use App\Models\UserBeerCount;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CreateBeer extends Component
{
    public $brand_name = '';
    public $name = '';
    public $style = '';

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
        ]);

        try {
            DB::beginTransaction();

            $brand = Brand::firstOrCreate(['name' => trim($this->brand_name)]);

            $existingBeer = Beer::where('brand_id', $brand->id)
                ->where('name', trim($this->name))
                ->first();

            if ($existingBeer) {
                $existingCount = UserBeerCount::where('user_id', Auth::id())
                    ->where('beer_id', $existingBeer->id)
                    ->exists();

                if ($existingCount) {
                    $this->addError('name', 'You are already tracking this beer.');
                    DB::rollBack();
                    return;
                }
            }

            $beer = Beer::firstOrCreate([
                'brand_id' => $brand->id,
                'name' => trim($this->name)
            ], [
                'style' => $this->style ? trim($this->style) : null
            ]);

            UserBeerCount::create([
                'user_id' => Auth::id(),
                'beer_id' => $beer->id,
                'count' => 1,
                'last_tasted_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('dashboard')->with('success', 'New beer added successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('name', 'Error adding beer. Please try again.');
        }
    }

    public function render()
    {
        return view('livewire.create-beer');
    }
}