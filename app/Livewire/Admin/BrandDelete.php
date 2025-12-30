<?php

namespace App\Livewire\Admin;

use App\Models\Brand;
use Livewire\Component;

class BrandDelete extends Component
{
    public $brand;
    public $showDeleteModal = false;

    public function mount(Brand $brand)
    {
        $this->brand = $brand;
    }

    public function confirmDelete()
    {
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        try {
            // Check if brand has associated beers
            $beersCount = $this->brand->beers()->count();
            if ($beersCount > 0) {
                session()->flash('error', __('brands.messages.cannot_delete_with_beers'));
                $this->showDeleteModal = false;
                return;
            }

            $this->brand->delete();
            session()->flash('success', __('brands.messages.deleted'));

            // Redirect to brands index
            return redirect()->route('admin.brands.index', ['locale' => app()->getLocale()]);
        } catch (\Exception $e) {
            session()->flash('error', __('brands.messages.error'));
            $this->showDeleteModal = false;
        }
    }

    public function render()
    {
        return view('livewire.admin.brand-delete');
    }
}
