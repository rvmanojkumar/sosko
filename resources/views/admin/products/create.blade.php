{{-- resources/views/admin/products/create.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Add Product')
@section('header', 'Add Product')

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
    .action-buttons {
        display: flex;
        gap: 12px;
        justify-content: flex-end;
        padding-top: 20px;
        border-top: 1px solid #E5E7EB;
        margin-top: 20px;
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
</style>

<form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" id="productForm">
    @csrf

    <div class="row">
        <div class="col-md-8">
            <!-- Basic Information Card -->
            <div class="card">
                <div class="card-header">Basic Information</div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Product Name *</label>
                            <input type="text" name="name" id="productName" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group col-md-6">
                            <label>Brand</label>
                            <input type="text" name="brand" id="brand" class="form-control" value="{{ old('brand') }}">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Category *</label>
                            <select name="category_id" id="categorySelect" class="form-control" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
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
                                    <option value="{{ $vendor->id }}" {{ old('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                        {{ $vendor->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Description *</label>
                        <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="4" required>{{ old('description') }}</textarea>
                        @error('description')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Short Description</label>
                            <textarea name="short_description" class="form-control" rows="2">{{ old('short_description') }}</textarea>
                            <small class="text-muted">Brief description for listings (max 150 chars)</small>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Weight (kg)</label>
                            <input type="number" name="weight" step="0.01" class="form-control" value="{{ old('weight') }}">
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
                        <table class="variants-table" id="variantsTable" style="display: none;">
                            <thead id="variantsHeader"></thead>
                            <tbody id="variantsBody"></tbody>
                        </table>
                    </div>
                    <div id="noVariantsMsg" class="alert alert-info text-center">
                        <i class="fas fa-info-circle"></i> No variants configured. Select attribute values above or click "Add Variant" to create variants.
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Status Card -->
            <div class="card">
                <div class="card-header">Status</div>
                <div class="card-body">
                    <div class="custom-control custom-switch mb-3">
                        <input type="checkbox" name="is_active" class="custom-control-input" id="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="is_active">Active</label>
                    </div>
                    <div class="custom-control custom-switch">
                        <input type="checkbox" name="is_featured" class="custom-control-input" id="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}>
                        <label class="custom-control-label" for="is_featured">Featured Product</label>
                    </div>
                </div>
            </div>

            <!-- Images Card -->
            <div class="card">
                <div class="card-header">Product Images</div>
                <div class="card-body">
                    <div class="upload-box" onclick="document.getElementById('imageInput').click()">
                        <span style="font-size: 32px;">📷</span>
                        <span style="font-size: 12px;">Upload Images</span>
                    </div>
                    <input type="file" name="images[]" id="imageInput" class="d-none" multiple accept="image/*">
                    <div id="imagePreview" class="mt-3" style="display: flex; flex-wrap: wrap; gap: 12px;"></div>
                    <small class="text-muted mt-2 d-block">You can upload multiple images. First image will be primary.</small>
                </div>
            </div>

            <!-- SEO Information Card -->
            <div class="card">
                <div class="card-header">SEO Information</div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Meta Title</label>
                        <input type="text" name="seo_data[meta_title]" class="form-control" value="{{ old('seo_data.meta_title') }}" placeholder="SEO Title">
                        <small class="text-muted">Recommended length: 50-60 characters</small>
                    </div>
                    <div class="form-group">
                        <label>Meta Description</label>
                        <textarea name="seo_data[meta_description]" class="form-control" rows="3" placeholder="SEO Description">{{ old('seo_data.meta_description') }}</textarea>
                        <small class="text-muted">Recommended length: 150-160 characters</small>
                    </div>
                    <div class="form-group">
                        <label>Meta Keywords</label>
                        <input type="text" name="seo_data[meta_keywords]" class="form-control" value="{{ old('seo_data.meta_keywords') }}" placeholder="keyword1, keyword2, keyword3">
                        <small class="text-muted">Comma-separated keywords</small>
                    </div>
                    <div class="form-group">
                        <label>SEO URL (Slug)</label>
                        <input type="text" name="slug" id="slug" class="form-control" placeholder="auto-generated-from-name" readonly disabled>
                        <small class="text-muted">Auto-generated from product name</small>
                    </div>
                    <div class="form-group">
                        <label>Canonical URL</label>
                        <input type="text" name="seo_data[canonical_url]" class="form-control" value="{{ old('seo_data.canonical_url') }}" placeholder="https://example.com/product">
                        <small class="text-muted">Leave empty to use auto-generated URL</small>
                    </div>
                    <div class="form-group">
                        <label>OG Title</label>
                        <input type="text" name="seo_data[og_title]" class="form-control" value="{{ old('seo_data.og_title') }}" placeholder="Open Graph Title">
                    </div>
                    <div class="form-group">
                        <label>OG Description</label>
                        <textarea name="seo_data[og_description]" class="form-control" rows="2" placeholder="Open Graph Description">{{ old('seo_data.og_description') }}</textarea>
                    </div>
                    <div class="form-group">
                        <label>OG Image</label>
                        <input type="file" name="seo_data[og_image]" class="form-control-file" accept="image/*">
                        <small class="text-muted">Recommended size: 1200x630px</small>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" name="seo_data[no_index]" class="custom-control-input" id="no_index" value="1" {{ old('seo_data.no_index') ? 'checked' : '' }}>
                            <label class="custom-control-label" for="no_index">No Index (hide from search engines)</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" name="seo_data[no_follow]" class="custom-control-input" id="no_follow" value="1" {{ old('seo_data.no_follow') ? 'checked' : '' }}>
                            <label class="custom-control-label" for="no_follow">No Follow (don't follow links)</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Settings Card -->
            <div class="card">
                <div class="card-header">Additional Settings</div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Low Stock Threshold</label>
                        <input type="number" name="low_stock_threshold" class="form-control" value="{{ old('low_stock_threshold', 5) }}">
                        <small class="text-muted">Alert when stock reaches this level</small>
                    </div>
                    <div class="form-group">
                        <label>Tags</label>
                        <input type="text" name="tags" class="form-control" value="{{ old('tags') }}" placeholder="summer, sale, new">
                        <small class="text-muted">Comma-separated tags for filtering</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="action-buttons">
        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">Save Product</button>
    </div>
</form>

@push('scripts')
<script>
let selectedAttributes = {};
let variantCounter = 0;

$(document).ready(function() {
    // Auto-generate slug from product name
    $('#productName').on('keyup', function() {
        let slug = $(this).val()
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
        $('#slug').val(slug);
    });
    
    $('#categorySelect').change(function() {
        loadCategoryAttributes($(this).val());
    });
    
    // Image preview
    $('#imageInput').on('change', function(e) {
        const files = e.target.files;
        const preview = $('#imagePreview');
        preview.empty();
        
        for (let i = 0; i < files.length; i++) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.append(`
                    <div class="image-preview">
                        <img src="${e.target.result}" alt="Product Image">
                        <button type="button" class="remove-image">×</button>
                    </div>
                `);
            }
            reader.readAsDataURL(files[i]);
        }
    });
    
    // Remove image preview
    $(document).on('click', '.remove-image', function() {
        $(this).closest('.image-preview').remove();
        $('#imageInput').val('');
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
            variantCounter = 0;
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
                $('#variantsCard').show();
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
        <td><input type="text" placeholder="Attribute" class="form-control form-control-sm" style="width: 100px;"></td>
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
    addManualVariantRow();
}

function updateVariantNumbers() {
    $('#variantsBody tr').each(function(index) {
        $(this).find('td:first').html(`<strong>${index + 1}</strong>`);
    });
}
</script>
@endpush
@endsection