<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CountActionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by auth:sanctum middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'action' => ['required', 'string', 'in:add,delete,increment,decrement'], // Support both new and legacy values
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
            'action.required' => 'The action is required.',
            'action.in' => 'The action must be either add, delete, increment, or decrement.',
            'note.max' => 'The note must not exceed 1000 characters.',
        ];
    }
}
