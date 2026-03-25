<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Check if user is vendor or admin
        $user = $this->user();
        return $user && ($user->hasRole('vendor') || $user->hasRole('admin') || $user->hasRole('super-admin'));
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'required|string',
            'short_description' => 'nullable|string|max:500',
            'brand' => 'nullable|string|max:100',
            'weight' => 'nullable|numeric|min:0',
            'specifications' => 'nullable|array',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'seo_data' => 'nullable|array',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'variants' => 'required|array|min:1',
            'variants.*.sku' => 'required|string|distinct',
            'variants.*.price' => 'required|numeric|min:0',
            'variants.*.sale_price' => 'nullable|numeric|min:0|lt:variants.*.price',
            'variants.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'variants.*.stock_quantity' => 'required|integer|min:0',
            'variants.*.low_stock_threshold' => 'nullable|integer|min:0',
            'variants.*.weight' => 'nullable|numeric|min:0',
            'variants.*.is_default' => 'boolean',
            'variants.*.is_active' => 'boolean',
            'variants.*.attribute_values' => 'nullable|array',
            'variants.*.attribute_values.*' => 'exists:attribute_values,id',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
        ];

        // For update requests, make fields sometimes required
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules = array_map(function($rule) {
                return str_replace('required', 'sometimes|required', $rule);
            }, $rules);
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Product name is required',
            'category_id.required' => 'Category is required',
            'category_id.exists' => 'Selected category does not exist',
            'description.required' => 'Product description is required',
            'variants.required' => 'At least one product variant is required',
            'variants.*.sku.required' => 'SKU is required for each variant',
            'variants.*.sku.distinct' => 'SKU must be unique for each variant',
            'variants.*.price.required' => 'Price is required for each variant',
            'variants.*.price.min' => 'Price cannot be negative',
            'variants.*.sale_price.lt' => 'Sale price must be less than regular price',
            'variants.*.stock_quantity.required' => 'Stock quantity is required for each variant',
            'variants.*.stock_quantity.min' => 'Stock quantity cannot be negative',
            'images.max' => 'Maximum 10 images allowed',
            'images.*.image' => 'Each file must be an image',
            'images.*.max' => 'Each image cannot exceed 5MB',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Ensure at least one variant is marked as default
        if ($this->has('variants')) {
            $hasDefault = false;
            foreach ($this->variants as $variant) {
                if (isset($variant['is_default']) && $variant['is_default']) {
                    $hasDefault = true;
                    break;
                }
            }
            
            // If no default variant, set first as default
            if (!$hasDefault && !empty($this->variants)) {
                $this->merge([
                    'variants' => array_map(function($key, $variant) {
                        if ($key === 0) {
                            $variant['is_default'] = true;
                        }
                        return $variant;
                    }, array_keys($this->variants), $this->variants)
                ]);
            }
        }
    }
}