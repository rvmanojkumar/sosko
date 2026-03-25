<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PromoCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user && $user->hasRole('admin');
    }

    public function rules(): array
    {
        $rules = [
            'code' => 'nullable|string|max:50|unique:promo_codes,code',
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:flat,percentage',
            'value' => 'required|numeric|min:0',
            'min_order_value' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'usage_type' => 'required|in:single,multi',
            'usage_limit' => 'nullable|integer|min:1',
            'per_user_limit' => 'nullable|integer|min:1',
            'user_id' => 'nullable|exists:users,id',
            'vendor_id' => 'nullable|exists:users,id',
            'applicable_products' => 'nullable|array',
            'applicable_categories' => 'nullable|array',
            'excluded_products' => 'nullable|array',
            'excluded_categories' => 'nullable|array',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'boolean',
            'is_first_order_only' => 'boolean',
            'stackable' => 'boolean',
        ];

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['code'] = 'nullable|string|max:50|unique:promo_codes,code,' . $this->promo_code?->id;
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Discount type is required',
            'value.required' => 'Discount value is required',
            'value.min' => 'Discount value must be at least 0',
            'usage_type.required' => 'Usage type is required',
            'start_date.required' => 'Start date is required',
            'end_date.required' => 'End date is required',
            'end_date.after' => 'End date must be after start date',
        ];
    }
}