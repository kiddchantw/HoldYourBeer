<?php

namespace App\Livewire\Admin;

use App\Models\Brand;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;

class BrandManager extends Component
{
    use WithPagination;

    // Search and Sort
    public $search = '';
    public $sortBy = 'name';
    public $sortOrder = 'asc';
    public $showDeleted = false;

    // Modal State
    public $showModal = false;
    public $isEdit = false;
    public $brandId = null;
    public $name = '';

    // Query String connection
    protected $queryString = [
        'search' => ['except' => ''],
        'sortBy' => ['except' => 'name'],
        'sortOrder' => ['except' => 'asc'],
        'showDeleted' => ['except' => false],
    ];

    public function render()
    {
        $brands = Brand::query()
            ->withCount('beers')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->when($this->showDeleted, function ($query) {
                $query->withTrashed();
            })
            ->orderBy($this->sortBy, $this->sortOrder)
            ->paginate(15);

        return view('livewire.admin.brand-manager', compact('brands'));
    }

    // Actions
    public function openCreateModal()
    {
        $this->resetValidation();
        $this->isEdit = false;
        $this->brandId = null;
        $this->name = '';
        $this->showModal = true;
    }

    public function openEditModal($id)
    {
        $this->resetValidation();
        $brand = Brand::withTrashed()->findOrFail($id);
        
        $this->isEdit = true;
        $this->brandId = $brand->id;
        $this->name = $brand->name;
        $this->showModal = true;
    }

    public function save()
    {
        $rules = [
            'name' => [
                'required', 
                'string', 
                'max:255', 
                Rule::unique('brands', 'name')->ignore($this->brandId)
            ],
        ];

        $messages = [
            'name.required' => __('brands.validation.name_required'),
            'name.unique' => __('brands.validation.name_unique'),
            'name.max' => __('brands.validation.name_max', ['max' => 255]),
        ];

        $attributes = [
            'name' => __('brands.attributes.name'),
        ];

        $this->validate($rules, $messages, $attributes);

        try {
            if ($this->isEdit) {
                $brand = Brand::withTrashed()->findOrFail($this->brandId);
                $brand->update(['name' => $this->name]);
                session()->flash('success', __('brands.messages.updated'));
            } else {
                Brand::create(['name' => $this->name]);
                session()->flash('success', __('brands.messages.created'));
            }
            
            $this->showModal = false;
        } catch (\Exception $e) {
            session()->flash('error', __('brands.messages.error'));
        }
    }

    public function delete($id)
    {
        $brand = Brand::find($id);
        if ($brand) {
            // Check for beers relationship if necessary, mimic controller logic
             $beersCount = $brand->beers()->count();
             if ($beersCount > 0) {
                 // Or handle Exception
                 session()->flash('error', "Cannot delete brand with associated beers.");
                 return;
             }

            $brand->delete();
            session()->flash('success', __('brands.messages.deleted'));
        }
    }

    public function restore($id)
    {
        $brand = Brand::withTrashed()->find($id);
        if ($brand) {
            $brand->restore();
            session()->flash('success', __('brands.messages.restored'));
        }
    }

    public function forceDelete($id)
    {
        $brand = Brand::withTrashed()->find($id);
        if ($brand) {
             $beersCount = $brand->beers()->count();
             if ($beersCount > 0) {
                 session()->flash('error', "Cannot force delete brand with associated beers.");
                 return;
             }
            $brand->forceDelete();
            session()->flash('success', __('brands.messages.force_deleted'));
        }
    }

    public function toggleSort($column)
    {
        if ($this->sortBy === $column) {
            $this->sortOrder = $this->sortOrder === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortOrder = 'asc';
        }
    }
}
