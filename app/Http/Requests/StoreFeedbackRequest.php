<?php

namespace App\Http\Requests;

use App\Models\Feedback;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFeedbackRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Allow everyone to submit feedback (including anonymous users)
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', Rule::in(Feedback::getTypes())],
            'description' => ['required', 'string', 'min:10'],
            'priority' => ['nullable', 'string', Rule::in(Feedback::getPriorities())],

            // Technical metadata (optional)
            'browser' => ['nullable', 'string', 'max:100'],
            'device' => ['nullable', 'string', 'max:100'],
            'os' => ['nullable', 'string', 'max:100'],
            'ip_address' => ['nullable', 'ip'],
            'metadata' => ['nullable', 'array'],
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
            'type.required' => '請選擇回饋類型',
            'type.in' => '無效的回饋類型',
            'description.required' => '請輸入詳細描述',
            'description.min' => '描述至少需要 10 個字元',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'type' => '回饋類型',
            'description' => '描述',
            'priority' => '優先級',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Automatically capture IP address
        if (!$this->has('ip_address')) {
            $this->merge([
                'ip_address' => $this->ip(),
            ]);
        }
    }
}
