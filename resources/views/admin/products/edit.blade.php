{{-- resources/views/admin/products/edit.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Edit Product')
@section('header', 'Edit Product: ' . $product->name)

@section('content')
<style>
    .attribute-options {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }
    .color-option {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        cursor: pointer;
        border: 3px solid transparent;
        transition: all 0.3s;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .color-option.selected {
        border-color: #2980B9;
        box-shadow: 0 0 0 2px white, 0 0 0 4px #2980B9;
    }
    .size-option, .ram-option, .storage-option, .processor-option {
        padding: 8px 16px;
        border: 1px solid #D1D5DB;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s;
        background: white;
    }
    .size-option.selected, .ram-option.selected, .storage-option.selected, .processor-option.selected {
        background: #2980B9;
        color: white;
        border-color: #2980B9;
    }
    .variants-table {
        width: 100%;
        border-collapse: collapse;
    }
    .variants-table th,
    .variants-table td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #E5E7EB;
        vertical-align: middle;
    }
    .variants-table th {
        background: #F8FAFC;
        font-weight: 600;
        font-size: 13px;
        color: #6B7280;
    }
    .variants-table td input, .variants-table td select {
        width: 100%;
        padding: 6px 10px;
        border: 1px solid #D1D5DB;
        border-radius: 6px;
    }
    .action-buttons {
        display: flex;
        gap: 12px;
        justify-content: flex-end;
        padding-top: 20px;
        border-top: 1px solid #E5E7EB;
        margin-top: 20px;
    }
    .upload-box {
        width: 120px;
        height: 120px;
        background: #F3F4F6;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        cursor: pointer;
        border: 2px dashed #D1D5DB;
        transition: all 0.3s;
        margin-bottom: 15px;
    }
    .upload-box:hover {
        border-color: #2980B9;
        background: #EBF5FB;
    }
    .image-preview-container {
        position: relative;
        display: inline-block;
        margin: 5px;
    }
    .image-preview-container img {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #E5E7EB;
    }
    .remove-image-btn {
        position: absolute;
        top: -8px;
        right: -8px;
        background: #EF4444;
        color: white;
        border: none;
        border-radius: 50%;
        width: 22px;
        height: 22px;
        font-size: 12px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .primary-badge {
        position: absolute;
        bottom: -20px;
        left: 5px;
        font-size: 10px;
        background: #2980B9;
        color: white;
        padding: 2px 6px;
        border-radius: 4px;
    }
    .existing-images-section, .new-images-section {
        margin-bottom: 20px;
    }
    .section-title {
        font-weight: 600;
        margin-bottom: 10px;
        color: #374151;
    }
    .images-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
    }
    .variant-attribute-select {
        width: 100%;
        padding: 6px 8px;
        border: 1px solid #D1D5DB;
        border-radius: 6px;
        font-size: 13px;
    }
    .editable-attribute {
        background: #FFF8E1;
    }
    .attribute-value-display {
        display: inline-block;
        padding: 4px 8px;
        background: #E3F2FD;
        border-radius: 4px;
        font-size: 12px;
    }
</style>

<form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data" id="productForm">
    @csrf
    @method('PUT')

    <div class="row">
        <div class="col-md-8">
            <!-- Basic Information Card -->
            <div class="card">
                <div class="card-header">Basic Information</div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Product Name *</label>
                            <input type="text" name="name" id="productName" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $product->name) }}" required>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group col-md-6">
                            <label>Brand</label>
                            <input type="text" name="brand" id="brand" class="form-control" value="{{ old('brand', $product->brand) }}">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Category *</label>
                            <select name="category_id" id="categorySelect" class="form-control" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Vendor *</label>
                            <select name="vendor_id" class="form-control" required>
                                <option value="">Select Vendor</option>
                                @foreach($vendors as $vendor)
                                    <option value="{{ $vendor->id }}" {{ old('vendor_id', $product->vendor_id) == $vendor->id ? 'selected' : '' }}>
                                        {{ $vendor->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Description *</label>
                        <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="4" required>{{ old('description', $product->description) }}</textarea>
                        @error('description')
                            <span class="invalid-feedback">{{ $message}}</span>
                        @enderror
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Short Description</label>
                            <textarea name="short_description" class="form-control" rows="2">{{ old('short_description', $product->short_description) }}</textarea>
                            <small class="text-muted">Brief description for listings (max 150 chars)</small>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Weight (kg)</label>
                            <input type="number" name="weight" step="0.01" class="form-control" value="{{ old('weight', $product->weight) }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dynamic Attributes Section -->
            <div class="card" id="attributesCard" style="display: none;">
                <div class="card-header">
                    Product Attributes
                    <span id="categoryNameDisplay" class="float-right text-muted" style="font-size: 12px;"></span>
                </div>
                <div class="card-body" id="attributesContainer"></div>
            </div>

            <!-- Variants Section -->
            <div class="card" id="variantsCard" style="display: none;">
                <div class="card-header">
                    Product Variants
                    <span class="float-right text-muted" style="font-size: 12px;">Configure price, SKU and stock for each combination</span>
                </div>
                <div class="card-body">
                    <div class="mb-3" style="display: flex; gap: 12px; flex-wrap: wrap;">
                        <button type="button" class="btn btn-secondary btn-sm" id="generateAllBtn">
                            ✨ Generate All Combinations
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm" id="clearVariantsBtn">
                            🗑️ Clear All
                        </button>
                        <button type="button" class="btn btn-success btn-sm" id="autoSkuBtn">
                            🏷️ Auto-generate SKUs
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" id="addVariantBtn">
                            ➕ Add Variant
                        </button>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="variants-table" id="variantsTable">
                            <thead id="variantsHeader"></thead>
                            <tbody id="variantsBody">
                                @php $variantIndex = 0; @endphp
                                @foreach($product->variants as $variant)
                                <tr data-variant-id="{{ $variant->id }}" class="variant-row">
                                    <td style="width: 40px;"><strong>{{ $loop->iteration }}</strong></td>
                                    @foreach($product->category->attributes as $attribute)
                                        <td>
                                            @php
                                                $selectedValue = $variant->attributeValues->firstWhere('attribute_id', $attribute->id);
                                            @endphp
                                            <select name="variants[{{ $variantIndex }}][attribute_values][]" class="variant-attribute-select" data-attr-id="{{ $attribute->id }}" required>
                                                <option value="">Select {{ $attribute->name }}</option>
                                                @foreach($attribute->values as $value)
                                                    <option value="{{ $value->id }}" 
                                                        data-value-name="{{ $value->value }}"
                                                        data-color-code="{{ $value->color_code }}"
                                                        {{ $selectedValue && $selectedValue->id == $value->id ? 'selected' : '' }}>
                                                        {{ $value->value }}
                                                        @if($value->color_code) ({{ $value->color_code }}) @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                    @endforeach
                                    <td><input type="text" name="variants[{{ $variantIndex }}][sku]" value="{{ $variant->sku }}" class="form-control form-control-sm sku-input" style="width: 140px;" required></td>
                                    <td><input type="number" name="variants[{{ $variantIndex }}][price]" value="{{ $variant->price }}" class="form-control form-control-sm price-input" style="width: 100px;" step="0.01" required></td>
                                    <td><input type="number" name="variants[{{ $variantIndex }}][sale_price]" value="{{ $variant->sale_price }}" class="form-control form-control-sm sale-price-input" style="width: 100px;" step="0.01" placeholder="Optional"></td>
                                    <td><input type="number" name="variants[{{ $variantIndex }}][stock]" value="{{ $variant->stock_quantity }}" class="form-control form-control-sm stock-input" style="width: 80px;" required></td>
                                    <td><button type="button" class="btn btn-danger btn-sm delete-variant"><i class="fas fa-trash"></i></button></td>
                                </tr>
                                @php $variantIndex++; @endphp
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    @if($product->variants->isEmpty())
                    <div id="noVariantsMsg" class="alert alert-info text-center">
                        <i class="fas fa-info-circle"></i> No variants configured. Click "Generate All Combinations" or "Add Variant" to create variants.
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Status Card -->
            <div class="card">
                <div class="card-header">Status</div>
                <div class="card-body">
                    <div class="custom-control custom-switch mb-3">
                        <input type="checkbox" name="is_active" class="custom-control-input" id="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="is_active">Active</label>
                    </div>
                    <div class="custom-control custom-switch">
                        <input type="checkbox" name="is_featured" class="custom-control-input" id="is_featured" value="1" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="is_featured">Featured Product</label>
                    </div>
                </div>
            </div>

            <!-- Images Card -->
            <div class="card">
                <div class="card-header">Product Images</div>
                <div class="card-body">
                    <!-- Existing Images -->
                    @if($product->images->count() > 0)
                    <div class="existing-images-section">
                        <div class="section-title">Current Images</div>
                        <div class="images-grid" id="existingImagesGrid">
                            @foreach($product->images as $image)
                            <div class="image-preview-container existing-image" data-image-id="{{ $image->id }}">
                                <img src="{{ Storage::disk('public')->url($image->image_path) }}" alt="Product Image">
                                <button type="button" class="remove-image-btn remove-existing-image" data-id="{{ $image->id }}">×</button>
                                @if($image->is_primary)
                                    <div class="primary-badge">Primary</div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- New Images Upload -->
                    <div class="new-images-section">
                        <div class="section-title">Add New Images</div>
                        <div class="upload-box" onclick="document.getElementById('imageInput').click()">
                            <span style="font-size: 32px;">📷</span>
                            <span style="font-size: 12px;">Click to Upload</span>
                        </div>
                        <input type="file" name="images[]" id="imageInput" class="d-none" multiple accept="image/*">
                        <div id="newImagesPreview" class="images-grid" style="margin-top: 15px;"></div>
                    </div>
                    <small class="text-muted mt-2 d-block">You can upload multiple images. First image will be primary.</small>
                </div>
            </div>

            <!-- SEO Information Card -->
            <div class="card">
                <div class-card-header">SEO Information</div>
                <div class="card-body">
                    @php $seoData = $product->seo_data ?? []; @endphp
                    <div class="form-group">
                        <label>Meta Title</label>
                        <input type="text" name="seo_data[meta_title]" class="form-control" value="{{ old('seo_data.meta_title', $seoData['meta_title'] ?? '') }}">
                    </div>
                    <div class="form-group">
                        <label>Meta Description</label>
                        <textarea name="seo_data[meta_description]" class="form-control" rows="2">{{ old('seo_data.meta_description', $seoData['meta_description'] ?? '') }}</textarea>
                    </div>
                    <div class="form-group">
                        <label>Meta Keywords</label>
                        <input type="text" name="seo_data[meta_keywords]" class="form-control" value="{{ old('seo_data.meta_keywords', $seoData['meta_keywords'] ?? '') }}">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="action-buttons">
        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">Update Product</button>
    </div>
</form>

@push('scripts')
<script>
let selectedAttributes = {};
let variantCounter = {{ $product->variants->count() }};
let availableAttributes = [];

$(document).ready(function() {
    // Load category attributes
    $('#categorySelect').change(function() {
        loadCategoryAttributes($(this).val());
    });
    
    if ($('#categorySelect').val()) {
        loadCategoryAttributes($('#categorySelect').val());
    }
    
    // Image upload preview
    $('#imageInput').on('change', function(e) {
        const files = e.target.files;
        const previewContainer = $('#newImagesPreview');
        previewContainer.empty();
        
        for (let i = 0; i < files.length; i++) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewContainer.append(`
                    <div class="image-preview-container new-image" data-temp-index="${i}">
                        <img src="${e.target.result}" alt="New Image">
                        <button type="button" class="remove-image-btn remove-new-image" data-index="${i}">×</button>
                    </div>
                `);
            }
            reader.readAsDataURL(files[i]);
        }
    });
    
    // Remove new image preview
    $(document).on('click', '.remove-new-image', function() {
        const index = $(this).data('index');
        $(`.new-image[data-temp-index="${index}"]`).remove();
        
        const input = $('#imageInput')[0];
        const dt = new DataTransfer();
        const files = input.files;
        for (let i = 0; i < files.length; i++) {
            if (i != index) {
                dt.items.add(files[i]);
            }
        }
        input.files = dt.files;
        
        $('.new-image').each(function(newIndex) {
            $(this).attr('data-temp-index', newIndex);
            $(this).find('.remove-new-image').data('index', newIndex);
        });
    });
    
    // Remove existing image (mark for deletion)
    $(document).on('click', '.remove-existing-image', function() {
        const imageId = $(this).data('id');
        if (confirm('Are you sure you want to delete this image?')) {
            $('<input>').attr({
                type: 'hidden',
                name: 'deleted_images[]',
                value: imageId
            }).appendTo('#productForm');
            $(this).closest('.existing-image').remove();
            toastr.success('Image marked for deletion');
        }
    });
    
    // Delete variant
    $(document).on('click', '.delete-variant', function() {
        const row = $(this).closest('tr');
        const variantId = row.data('variant-id');
        if (variantId) {
            $('<input>').attr({
                type: 'hidden',
                name: 'deleted_variants[]',
                value: variantId
            }).appendTo('#productForm');
        }
        row.remove();
        updateVariantNumbers();
        if ($('#variantsBody tr').length === 0) {
            $('#variantsTable').hide();
            $('#noVariantsMsg').show();
        }
    });
    
    // Add variant manually
    $('#addVariantBtn').click(function() {
        addManualVariantRow();
    });
    
    // Generate all combinations
    $('#generateAllBtn').click(function() {
        $('.color-option, .size-option, .ram-option, .storage-option, .processor-option').each(function() {
            if (!$(this).hasClass('selected')) {
                $(this).click();
            }
        });
    });
    
    // Clear all variants
    $('#clearVariantsBtn').click(function() {
        if (confirm('Are you sure you want to clear all variants?')) {
            $('#variantsBody tr').each(function() {
                const variantId = $(this).data('variant-id');
                if (variantId) {
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'deleted_variants[]',
                        value: variantId
                    }).appendTo('#productForm');
                }
            });
            $('#variantsBody').empty();
            $('#variantsTable').hide();
            $('#noVariantsMsg').show();
            $('.color-option, .size-option, .ram-option, .storage-option, .processor-option').removeClass('selected');
            selectedAttributes = {};
        }
    });
    
    // Auto-generate SKUs with duplicate check
    $('#autoSkuBtn').click(function() {
        const productName = $('#productName').val() || 'PRODUCT';
        const baseSku = productName.toUpperCase().replace(/[^A-Z0-9]/g, '_').substring(0, 10);
        const existingSkus = new Set();
        
        $('#variantsBody tr').each(function() {
            const skuInput = $(this).find('.sku-input');
            if (skuInput.val()) {
                existingSkus.add(skuInput.val());
            }
        });
        
        $('#variantsBody tr').each(function() {
            const cells = $(this).find('td');
            const skuInput = cells.eq(cells.length - 5).find('.sku-input');
            let attrValues = [];
            
            for (let i = 1; i < cells.length - 5; i++) {
                const select = cells.eq(i).find('select');
                if (select.length) {
                    const selectedOption = select.find('option:selected');
                    if (selectedOption.val() && selectedOption.val() !== '') {
                        const code = selectedOption.text().substring(0, 3).toUpperCase();
                        if (code) attrValues.push(code);
                    }
                }
            }
            
            let newSku = baseSku;
            if (attrValues.length > 0) {
                newSku = `${baseSku}-${attrValues.join('-')}`;
            } else {
                newSku = `${baseSku}-${Math.random().toString(36).substring(2, 8).toUpperCase()}`;
            }
            
            let finalSku = newSku;
            let counter = 1;
            while (existingSkus.has(finalSku)) {
                finalSku = `${newSku}-${counter}`;
                counter++;
            }
            existingSkus.add(finalSku);
            skuInput.val(finalSku);
        });
    });
});

function loadCategoryAttributes(categoryId) {
    if (!categoryId) {
        $('#attributesCard').hide();
        $('#variantsCard').hide();
        return;
    }

    $.ajax({
        url: `/api/v1/categories/${categoryId}/attributes`,
        method: 'GET',
        success: function(response) {
            if (response.data && response.data.length > 0) {
                availableAttributes = response.data;
                displayAttributes(response.data);
                $('#attributesCard').show();
            } else {
                $('#attributesCard').hide();
                showSimpleVariantForm();
                $('#variantsCard').show();
            }
        },
        error: function() {
            showSimpleVariantForm();
            $('#variantsCard').show();
        }
    });
}

function displayAttributes(attributes) {
    const container = $('#attributesContainer');
    container.empty();
    selectedAttributes = {};

    attributes.forEach(attr => {
        const html = `
            <div class="form-group attribute-section">
                <label>${attr.name} ${attr.is_required ? '*' : ''}</label>
                <div class="attribute-options" data-attribute="${attr.name}" data-type="${attr.type}">
                    ${generateAttributeOptions(attr)}
                </div>
            </div>
        `;
        container.append(html);
    });
}

function generateAttributeOptions(attr) {
    if (attr.type === 'color') {
        return attr.values.map(value => 
            `<div class="color-option" style="background: ${value.color_code || '#ccc'};" 
                  title="${value.value}" 
                  data-value-id="${value.id}"
                  data-value-name="${value.value}"
                  onclick="selectAttributeValue(this, '${attr.name}', '${value.id}', '${value.value}')"></div>`
        ).join('');
    } else {
        return attr.values.map(value => 
            `<div class="${attr.type}-option" 
                  data-value-id="${value.id}"
                  data-value-name="${value.value}"
                  onclick="selectAttributeValue(this, '${attr.name}', '${value.id}', '${value.value}')">
                ${value.value}
            </div>`
        ).join('');
    }
}

function selectAttributeValue(element, attributeName, valueId, valueName) {
    if (element.classList.contains('selected')) {
        element.classList.remove('selected');
        if (selectedAttributes[attributeName]) {
            selectedAttributes[attributeName] = selectedAttributes[attributeName].filter(v => v.id != valueId);
            if (selectedAttributes[attributeName].length === 0) {
                delete selectedAttributes[attributeName];
            }
        }
    } else {
        element.classList.add('selected');
        if (!selectedAttributes[attributeName]) {
            selectedAttributes[attributeName] = [];
        }
        selectedAttributes[attributeName].push({ id: valueId, name: valueName });
    }
    
    generateVariants();
}

function generateVariants() {
    const attributeNames = Object.keys(selectedAttributes);
    if (attributeNames.length === 0) {
        if ($('#variantsBody tr').length === 0) {
            $('#variantsTable').hide();
            $('#noVariantsMsg').show();
        }
        return;
    }
    
    let combinations = [[]];
    for (let attr of attributeNames) {
        const newCombinations = [];
        for (let combo of combinations) {
            for (let value of selectedAttributes[attr]) {
                newCombinations.push([...combo, { attribute: attr, value: value }]);
            }
        }
        combinations = newCombinations;
    }
    
    generateVariantTable(combinations, attributeNames);
}

function generateVariantTable(combinations, attributeNames) {
    const thead = $('#variantsHeader');
    const tbody = $('#variantsBody');
    const productName = $('#productName').val() || 'PRODUCT';
    const baseSku = productName.toUpperCase().replace(/[^A-Z0-9]/g, '_').substring(0, 10);
    const existingSkus = new Set();
    
    $('#variantsBody tr').each(function() {
        const sku = $(this).find('.sku-input').val();
        if (sku) existingSkus.add(sku);
    });
    
    let headerHtml = '<tr><th style="width: 40px;">#</th>';
    attributeNames.forEach(attr => {
        headerHtml += `<th>${attr}</th>`;
    });
    headerHtml += '<th>SKU</th><th>Price (₹)</th><th>Sale Price (₹)</th><th>Stock</th><th style="width: 50px;">Actions</th></tr>';
    thead.html(headerHtml);
    
    tbody.empty();
    combinations.forEach((combo, index) => {
        let attrCode = combo.map(c => c.value.name.substring(0, 3).toUpperCase()).join('-');
        if (attrCode === '') attrCode = 'VAR';
        
        let newSku = `${baseSku}-${attrCode}`;
        let finalSku = newSku;
        let counter = 1;
        while (existingSkus.has(finalSku)) {
            finalSku = `${newSku}-${counter}`;
            counter++;
        }
        existingSkus.add(finalSku);
        
        let rowHtml = `<tr>`;
        rowHtml += `<td><strong>${index + 1}</strong></td>`;
        
        attributeNames.forEach(attr => {
            const value = combo.find(c => c.attribute === attr)?.value.name || '-';
            rowHtml += `<td><select class="variant-attribute-select" data-attr-name="${attr}" required>
                <option value="">Select ${attr}</option>
                ${selectedAttributes[attr].map(v => `<option value="${v.id}" ${v.name === value ? 'selected' : ''}>${v.name}</option>`).join('')}
            </select></td>`;
        });
        
        rowHtml += `
            <td><input type="text" name="variants[${variantCounter}][sku]" value="${finalSku}" class="form-control form-control-sm sku-input" style="width: 140px;" required></td>
            <td><input type="number" name="variants[${variantCounter}][price]" value="999" class="form-control form-control-sm price-input" style="width: 100px;" step="0.01" required></td>
            <td><input type="number" name="variants[${variantCounter}][sale_price]" class="form-control form-control-sm sale-price-input" style="width: 100px;" step="0.01" placeholder="Optional"></td>
            <td><input type="number" name="variants[${variantCounter}][stock]" value="0" class="form-control form-control-sm stock-input" style="width: 80px;" required></td>
            <td><button type="button" class="btn btn-danger btn-sm delete-variant"><i class="fas fa-trash"></i></button></td>
        `;
        
        combo.forEach(c => {
            rowHtml += `<input type="hidden" name="variants[${variantCounter}][attribute_values][]" value="${c.value.id}">`;
        });
        
        rowHtml += `</tr>`;
        tbody.append(rowHtml);
        variantCounter++;
    });
    
    $('#variantsTable').show();
    $('#noVariantsMsg').hide();
    updateVariantNumbers();
}

function addManualVariantRow() {
    const tbody = $('#variantsBody');
    const rowCount = tbody.children().length + 1;
    const productName = $('#productName').val() || 'PRODUCT';
    const baseSku = productName.toUpperCase().replace(/[^A-Z0-9]/g, '_').substring(0, 10);
    const existingSkus = new Set();
    
    $('#variantsBody tr').each(function() {
        const sku = $(this).find('.sku-input').val();
        if (sku) existingSkus.add(sku);
    });
    
    let uniqueSku = `${baseSku}-MANUAL-${Math.random().toString(36).substring(2, 6).toUpperCase()}`;
    while (existingSkus.has(uniqueSku)) {
        uniqueSku = `${baseSku}-MANUAL-${Math.random().toString(36).substring(2, 6).toUpperCase()}`;
    }
    existingSkus.add(uniqueSku);
    
    // Create attribute select dropdowns based on available attributes
    let attributeCells = '';
    if (availableAttributes.length > 0) {
        availableAttributes.forEach((attr, attrIndex) => {
            attributeCells += `
                <td>
                    <select class="variant-attribute-select manual-attr-select" data-attr-id="${attr.id}" data-attr-name="${attr.name}" data-attr-index="${attrIndex}">
                        <option value="">Select ${attr.name}</option>
                        ${attr.values.map(v => `<option value="${v.id}" data-value-name="${v.value}">${v.value}</option>`).join('')}
                    </select>
                </td>
            `;
        });
    } else {
        attributeCells = `
            <td><input type="text" placeholder="Attribute Name" class="form-control form-control-sm manual-attr-name" style="width: 100px;"></td>
            <td><input type="text" placeholder="Value" class="form-control form-control-sm manual-attr-value" style="width: 100px;"></td>
        `;
    }
    
    let rowHtml = `<tr>
        <td><strong>${rowCount}</strong></td>
        ${attributeCells}
        <td><input type="text" name="variants[${variantCounter}][sku]" value="${uniqueSku}" class="form-control form-control-sm sku-input" style="width: 140px;" required></td>
        <td><input type="number" name="variants[${variantCounter}][price]" class="form-control form-control-sm price-input" style="width: 100px;" step="0.01" placeholder="Price" required></td>
        <td><input type="number" name="variants[${variantCounter}][sale_price]" class="form-control form-control-sm sale-price-input" style="width: 100px;" step="0.01" placeholder="Sale Price"></td>
        <td><input type="number" name="variants[${variantCounter}][stock]" class="form-control form-control-sm stock-input" style="width: 80px;" placeholder="Stock" required></td>
        <td><button type="button" class="btn btn-danger btn-sm delete-variant"><i class="fas fa-trash"></i></button></td>
    </tr>`;
    
    tbody.append(rowHtml);
    
    // Handle manual attribute selection change to update hidden fields
    if (availableAttributes.length > 0) {
        const row = tbody.children().last();
        row.find('.manual-attr-select').each(function() {
            const attrId = $(this).data('attr-id');
            const attrIndex = $(this).data('attr-index');
            $(this).on('change', function() {
                const selectedOption = $(this).find('option:selected');
                const valueId = $(this).val();
                if (valueId) {
                    let hiddenInput = row.find(`input[data-attr-index="${attrIndex}"]`);
                    if (hiddenInput.length === 0) {
                        hiddenInput = $(`<input type="hidden" name="variants[${variantCounter}][attribute_values][]" value="${valueId}" data-attr-index="${attrIndex}">`);
                        row.append(hiddenInput);
                    } else {
                        hiddenInput.val(valueId);
                    }
                }
            });
        });
    }
    
    variantCounter++;
    $('#variantsTable').show();
    $('#noVariantsMsg').hide();
    updateVariantNumbers();
}

function showSimpleVariantForm() {
    if ($('#variantsBody tr').length === 0) {
        addManualVariantRow();
    }
    $('#variantsTable').show();
    $('#noVariantsMsg').hide();
}

function updateVariantNumbers() {
    $('#variantsBody tr').each(function(index) {
        $(this).find('td:first').html(`<strong>${index + 1}</strong>`);
    });
}
</script>
@endpush
@endsection