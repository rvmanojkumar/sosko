<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()?->id;
        
        return [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $userId,
            'phone' => 'sometimes|string|unique:users,phone,' . $userId,
            'dob' => 'nullable|date|before:today',
            'profile_photo' => 'nullable|image|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'email.email' => 'Invalid email format',
            'email.unique' => 'Email already taken',
            'phone.unique' => 'Phone number already registered',
            'dob.before' => 'Date of birth must be before today',
            'profile_photo.image' => 'Profile photo must be an image',
            'profile_photo.max' => 'Profile photo cannot exceed 2MB',
        ];
    }
}