<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'address_id' => 'required|exists:user_addresses,id',
            'payment_method' => 'required|in:razorpay,cod,wallet',
            'promo_code' => 'nullable|string|exists:promo_codes,code',
            'notes' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'address_id.required' => 'Please select a delivery address',
            'address_id.exists' => 'Selected address does not exist',
            'payment_method.required' => 'Please select a payment method',
            'payment_method.in' => 'Invalid payment method',
        ];
    }
}