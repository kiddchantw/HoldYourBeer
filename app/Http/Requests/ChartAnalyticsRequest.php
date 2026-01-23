<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChartAnalyticsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only authenticated users can access chart analytics
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'month' => ['nullable', 'string', 'regex:/^\d{4}-\d{2}$/'],
            'type' => ['nullable', 'string', 'in:bar,pie,line'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
            'device' => ['nullable', 'string', 'in:mobile,tablet,desktop'],
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
            'month' => 'filter month',
            'type' => 'chart type',
            'limit' => 'data limit',
            'device' => 'device type',
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
            'month.regex' => 'The month must be in YYYY-MM format (e.g., 2025-12).',
            'type.in' => 'The chart type must be one of: bar, pie, line.',
            'limit.min' => 'The limit must be at least 1.',
            'limit.max' => 'The limit cannot exceed 100.',
            'device.in' => 'The device type must be one of: mobile, tablet, desktop.',
        ];
    }
}
