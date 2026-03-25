<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user && $user->hasRole('admin');
    }

    public function rules(): array
    {
        $rules = [
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:500',
            'cta_text' => 'nullable|string|max:100',
            'cta_link' => 'nullable|url|max:500',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
            'type' => 'required|in:hero_slider,category_banner,popup,app_notification',
            'target_type' => 'nullable|in:all,category,vendor',
            'target_id' => 'nullable|uuid',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
        ];

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['image'] = 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Banner title is required',
            'type.required' => 'Banner type is required',
            'image.required' => 'Banner image is required',
            'image.image' => 'File must be an image',
            'image.max' => 'Image cannot exceed 5MB',
        ];
    }
}