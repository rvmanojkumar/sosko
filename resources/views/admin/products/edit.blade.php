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
    .variants-table td input {
        width: 100%;
        padding: 6px 10px;
        border: 1px solid #D1D5DB;
        border-radius: 6px;
    }
    .variant-card {
        background: #F9FAFB;
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 16px;
        position: relative;
        border: 1px solid #E5E7EB;
    }
    .variant-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 1px solid #E5E7EB;
    }
    .variant-title {
        font-weight: 600;
        color: #1F2937;
    }
    .variant-badge {
        background: #2980B9;
        color: white;
        padding: 2px 8px;
        border-radius: 20px;
        font-size: 11px;
    }
    .upload-box {
        width: 100px;
        height: 100px;
        background: #F3F4F6;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        cursor: pointer;
        border: 2px dashed #D1D5DB;
        transition: all 0.3s;
    }
    .upload-box:hover {
        border-color: #2980B9;
        background: #EBF5FB;
    }
    .action-buttons {
        display: flex;
        gap: 12px;
        justify-content: flex-end;
        padding-top: 20px;
        border-top: 1px solid #E5E7EB;
        margin-top: 20px;
    }
    .image-preview {
        position: relative;
        display: inline-block;
        margin-right: 12px;
        margin-bottom: 12px;
    }
    .image-preview img {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #E5E7EB;
    }
    .image-preview .remove-image {
        position: absolute;
        top: -8px;
        right: -8px;
        background: #EF4444;
        color: white;
        border: none;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        font-size: 12px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .btn-icon {
        padding: 6px 12px;
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
                            <span class="invalid-feedback">{{ $message }}</span>
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
                                            @if($attribute->type == 'color')
                                                <div class="color-value" style="display: flex; align-items: center; gap: 8px;">
                                                    <div style="width: 24px; height: 24px; background: {{ $selectedValue->color_code ?? '#ccc' }}; border-radius: 50%; border: 1px solid #ddd;"></div>
                                                    <span>{{ $selectedValue->value ?? '-' }}</span>
                                                </div>
                                                <input type="hidden" name="variants[{{ $variantIndex }}][attribute_values][]" value="{{ $selectedValue->id ?? '' }}">
                                            @else
                                                <span class="attribute-value-display">{{ $selectedValue->value ?? '-' }}</span>
                                                <input type="hidden" name="variants[{{ $variantIndex }}][attribute_values][]" value="{{ $selectedValue->id ?? '' }}">
                                            @endif
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
                    <div id="existingImages" style="display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
                        @foreach($product->images as $image)
                        <div class="image-preview" data-image-id="{{ $image->id }}">
                            <img src="{{ Storage::url($image->image_path) }}" alt="Product Image">
                            <button type="button" class="remove-image" data-id="{{ $image->id }}">×</button>
                            @if($image->is_primary)
                                <span class="badge badge-primary" style="position: absolute; bottom: -20px; left: 5px;">Primary</span>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    <div class="upload-box" onclick="document.getElementById('imageInput').click()">
                        <span style="font-size: 32px;">📷</span>
                        <span style="font-size: 12px;">Upload Image</span>
                    </div>
                    <input type="file" name="images[]" id="imageInput" class="d-none" multiple accept="image/*">
                    <div id="newImagePreview" class="mt-3" style="display: flex; flex-wrap: wrap; gap: 12px;"></div>
                    <small class="text-muted mt-2 d-block">You can upload multiple images. First image will be primary.</small>
                </div>
            </div>

            <!-- SEO Card -->
            <div class="card">
                <div class="card-header">SEO Information</div>
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
                    <div class="form-group">
                        <label>Slug</label>
                        <input type="text" name="slug" class="form-control" value="{{ $product->slug }}" readonly disabled>
                        <small class="text-muted">Auto-generated from product name</small>
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

$(document).ready(function() {
    $('#categorySelect').change(function() {
        loadCategoryAttributes($(this).val());
    });
    
    // Load attributes if category is already selected
    if ($('#categorySelect').val()) {
        loadCategoryAttributes($('#categorySelect').val());
    }
    
    // Image preview
    $('#imageInput').on('change', function(e) {
        const files = e.target.files;
        const preview = $('#newImagePreview');
        preview.empty();
        
        for (let i = 0; i < files.length; i++) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.append(`
                    <div class="image-preview new-image">
                        <img src="${e.target.result}" alt="New Image">
                        <button type="button" class="remove-new-image">×</button>
                    </div>
                `);
            }
            reader.readAsDataURL(files[i]);
        }
    });
    
    // Remove new image preview
    $(document).on('click', '.remove-new-image', function() {
        $(this).closest('.image-preview').remove();
        $('#imageInput').val('');
    });
    
    // Remove existing image
    $(document).on('click', '.remove-image', function() {
        const imageId = $(this).data('id');
        if (confirm('Are you sure you want to delete this image?')) {
            $.ajax({
                url: '{{ route("admin.products.delete-image", ["product" => $product->id, "image" => ":imageId"]) }}'.replace(':imageId', imageId),
                method: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    $(`.image-preview[data-image-id="${imageId}"]`).remove();
                    toastr.success('Image deleted successfully');
                },
                error: function() {
                    toastr.error('Failed to delete image');
                }
            });
        }
    });
    
    // Delete variant
    $(document).on('click', '.delete-variant', function() {
        $(this).closest('tr').remove();
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
            $('#variantsBody').empty();
            $('#variantsTable').hide();
            $('#noVariantsMsg').show();
            $('.color-option, .size-option, .ram-option, .storage-option, .processor-option').removeClass('selected');
            selectedAttributes = {};
        }
    });
    
    // Auto-generate SKUs
    $('#autoSkuBtn').click(function() {
        const productName = $('#productName').val() || 'PRODUCT';
        const rows = $('#variantsBody tr');
        rows.each(function(index) {
            const cells = $(this).find('td');
            const skuInput = cells.eq(cells.length - 5).find('input');
            if (skuInput && !skuInput.val()) {
                let attrValues = [];
                for (let i = 1; i < cells.length - 5; i++) {
                    const text = cells.eq(i).text().trim();
                    if (text && text !== '-') {
                        attrValues.push(text.substring(0, 3).toUpperCase());
                    }
                }
                const attrCode = attrValues.join('-');
                skuInput.val(`${productName.toUpperCase().replace(/[^A-Z0-9]/g, '_').substring(0, 10)}-${attrCode}`);
            }
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
                displayAttributes(response.data);
                $('#attributesCard').show();
                loadExistingVariants();
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
        const isSelected = false;
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
    
    // Header
    let headerHtml = '<tr><th style="width: 40px;">#</th>';
    attributeNames.forEach(attr => {
        headerHtml += `<th>${attr}</th>`;
    });
    headerHtml += '<th>SKU</th><th>Price (₹)</th><th>Sale Price (₹)</th><th>Stock</th><th style="width: 50px;">Actions</th></tr>';
    thead.html(headerHtml);
    
    // Body
    tbody.empty();
    combinations.forEach((combo, index) => {
        const attrCode = combo.map(c => c.value.name.substring(0, 3).toUpperCase()).join('-');
        const defaultSKU = `${productName.toUpperCase().replace(/[^A-Z0-9]/g, '_').substring(0, 10)}-${attrCode}`;
        
        let rowHtml = `<tr data-combination='${JSON.stringify(combo)}'>`;
        rowHtml += `<td><strong>${index + 1}</strong></td>`;
        
        attributeNames.forEach(attr => {
            const value = combo.find(c => c.attribute === attr)?.value.name || '-';
            rowHtml += `<td>${value}</td>`;
        });
        
        rowHtml += `
            <td><input type="text" name="variants[${variantCounter}][sku]" value="${defaultSKU}" class="form-control form-control-sm sku-input" style="width: 140px;" required></td>
            <td><input type="number" name="variants[${variantCounter}][price]" value="999" class="form-control form-control-sm price-input" style="width: 100px;" step="0.01" required></td>
            <td><input type="number" name="variants[${variantCounter}][sale_price]" class="form-control form-control-sm sale-price-input" style="width: 100px;" step="0.01" placeholder="Optional"></td>
            <td><input type="number" name="variants[${variantCounter}][stock]" value="0" class="form-control form-control-sm stock-input" style="width: 80px;" required></td>
            <td><button type="button" class="btn btn-danger btn-sm delete-variant"><i class="fas fa-trash"></i></button></td>
        `;
        
        // Add hidden fields for attribute values
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
    
    let rowHtml = `<tr>
        <td><strong>${rowCount}</strong></td>
        <td><input type="text" placeholder="Color/Size/etc" class="form-control form-control-sm" style="width: 100px;"></td>
        <td><input type="text" placeholder="Value" class="form-control form-control-sm" style="width: 100px;"></td>
        <td><input type="text" name="variants[${variantCounter}][sku]" class="form-control form-control-sm sku-input" style="width: 140px;" placeholder="SKU" required></td>
        <td><input type="number" name="variants[${variantCounter}][price]" class="form-control form-control-sm price-input" style="width: 100px;" step="0.01" placeholder="Price" required></td>
        <td><input type="number" name="variants[${variantCounter}][sale_price]" class="form-control form-control-sm sale-price-input" style="width: 100px;" step="0.01" placeholder="Sale Price"></td>
        <td><input type="number" name="variants[${variantCounter}][stock]" class="form-control form-control-sm stock-input" style="width: 80px;" placeholder="Stock" required></td>
        <td><button type="button" class="btn btn-danger btn-sm delete-variant"><i class="fas fa-trash"></i></button></td>
    </tr>`;
    
    tbody.append(rowHtml);
    variantCounter++;
    $('#variantsTable').show();
    $('#noVariantsMsg').hide();
    updateVariantNumbers();
}

function showSimpleVariantForm() {
    const tbody = $('#variantsBody');
    if (tbody.children().length === 0) {
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

function loadExistingVariants() {
    // Existing variants are already in the table
    // This function can be used to update attribute displays if needed
}
</script>
@endpush
@endsection