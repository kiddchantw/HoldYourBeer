<?php

namespace App\Livewire\Admin;

use App\Models\Beer;
use App\Models\Brand;
use Livewire\Component;
use Livewire\WithPagination;

class BrandBeerManager extends Component
{
    use WithPagination;

    public Brand $brand;
    
    // Modal state
    public $showModal = false;
    public $isEdit = false;
    public $beerId;
    
    // Form fields
    public $name = '';
    public $style = '';

    public function rules()
    {
        return [
            'name' => 'required|string|max:255', // Unique validation logic needs care
            'style' => 'nullable|string|max:100',
        ];
    }

    public function mount(Brand $brand)
    {
        $this->brand = $brand;
    }

    public function openCreateModal()
    {
        $this->resetValidation();
        $this->reset(['name', 'style', 'beerId']);
        $this->isEdit = false;
        $this->showModal = true;
    }

    public function openEditModal(Beer $beer)
    {
        $this->resetValidation();
        $this->beerId = $beer->id;
        $this->name = $beer->name;
        $this->style = $beer->style;
        $this->isEdit = true;
        $this->showModal = true;
    }

    public function save()
    {
        // Custom validation to handle unique constraint within brand
        $rules = $this->rules();
        // Unique check: ignore current beer ID if editing
        // The unique constraint is on (brand_id, name)
        // Laravel unique rule: unique:table,column,except,id,whereColumn,whereValue
        
        // Simpler manual check might be cleaner or complex rule string
        // Let's use validation rule closure or just try catch or complex unique rule
        // unique:beers,name,NULL,id,brand_id,123 (but 123 is dynamic)
        
        $brandId = $this->brand->id;
        $beerId = $this->beerId;
        
        $rules['name'] = [
            'required',
            'string',
            'max:255',
            function ($attribute, $value, $fail) use ($brandId, $beerId) {
                $query = Beer::where('brand_id', $brandId)->where('name', $value);
                if ($beerId) {
                    $query->where('id', '!=', $beerId);
                }
                if ($query->exists()) {
                    $fail('The beer name has already been taken for this brand.');
                }
            },
        ];

        $this->validate($rules);

        if ($this->isEdit) {
            $beer = Beer::findOrFail($this->beerId);
            $beer->update([
                'name' => $this->name,
                // 'style' => $this->style, // Disabled per user request
            ]);
            $message = 'Beer updated successfully.';
        } else {
            $this->brand->beers()->create([
                'name' => $this->name,
                // 'style' => $this->style, // Disabled per user request
            ]);
            $message = 'Beer created successfully.';
        }

        $this->showModal = false;
        $this->dispatch('saved'); // Optional: for notification
        session()->flash('success', $message);
    }

    public function delete($id)
    {
        $beer = Beer::findOrFail($id);
        $beer->delete();
        session()->flash('success', 'Beer deleted successfully.');
    }

    public function render()
    {
        return view('livewire.admin.brand-beer-manager', [
            'beers' => $this->brand->beers()->orderBy('name')->get() // Or paginate if list gets long
        ]);
    }
}
