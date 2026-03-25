<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VendorRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'store_name' => 'required|string|max:255|unique:vendor_profiles,store_name',
            'description' => 'nullable|string',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'required|string|max:20',
            'address' => 'required|string',
            'logo' => 'nullable|image|max:2048',
            'gst_number' => 'nullable|string|max:15',
            'pan_number' => 'nullable|string|max:10',
        ];
    }

    public function messages(): array
    {
        return [
            'store_name.required' => 'Store name is required',
            'store_name.unique' => 'Store name already taken',
            'contact_email.required' => 'Contact email is required',
            'contact_email.email' => 'Invalid email format',
            'contact_phone.required' => 'Contact phone is required',
            'address.required' => 'Store address is required',
            'logo.image' => 'Logo must be an image',
        ];
    }
}