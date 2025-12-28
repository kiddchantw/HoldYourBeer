<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBeerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by auth:sanctum middleware
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // If brand name is provided instead of brand_id, find or create the brand
        if ($this->has('brand') && !$this->has('brand_id')) {
            $brandName = trim($this->input('brand'));
            
            // Case-insensitive brand lookup
            $brand = \App\Models\Brand::whereRaw('LOWER(name) = ?', [strtolower($brandName)])->first();
            
            if (!$brand) {
                // Create new brand with proper casing
                $brand = \App\Models\Brand::create(['name' => $brandName]);
            }
            
            $this->merge([
                'brand_id' => $brand->id,
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            // Accept either brand (name) or brand_id
            'brand' => ['nullable', 'string', 'max:255', 'required_without:brand_id'],
            'brand_id' => ['nullable', 'integer', 'exists:brands,id', 'required_without:brand'],
            'style' => ['nullable', 'string', 'max:255'],
            'shop_name' => ['nullable', 'string', 'max:255'],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The beer name is required.',
            'name.max' => 'The beer name must not exceed 255 characters.',
            'brand.required_without' => 'Either brand name or brand ID is required.',
            'brand.max' => 'The brand name must not exceed 255 characters.',
            'brand_id.required_without' => 'Either brand name or brand ID is required.',
            'brand_id.exists' => 'The selected brand does not exist.',
            'style.max' => 'The beer style must not exceed 255 characters.',
        ];
    }
}
