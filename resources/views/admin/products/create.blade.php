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
    }
    .color-option.selected {
        border-color: #2980B9;
        box-shadow: 0 0 0 2px white, 0 0 0 4px #2980B9;
    }
    .size-option, .ram-option, .storage-option {
        padding: 8px 16px;
        border: 1px solid #ddd;
        border-radius: 8px;
        cursor: pointer;
        background: white;
        transition: all 0.3s;
    }
    .size-option.selected, .ram-option.selected, .storage-option.selected {
        background: #2980B9;
        color: white;
        border-color: #2980B9;
    }
    .variants-table th, .variants-table td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #e5e7eb;
    }
    .variants-table th {
        background: #f8fafc;
        font-weight: 600;
    }
</style>

<form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" id="productForm">
    @csrf

    <div class="row">
        <div class="col-md-8">
            <!-- Basic Information -->
            <div class="card">
                <div class="card-header">Basic Information</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Product Name *</label>
                                <input type="text" name="name" id="productName" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Brand</label>
                                <input type="text" name="brand" id="brand" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Category *</label>
                                <select name="category_id" id="categorySelect" class="form-control" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Vendor *</label>
                                <select name="vendor_id" class="form-control" required>
                                    <option value="">Select Vendor</option>
                                    @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Description *</label>
                        <textarea name="description" id="description" class="form-control" rows="4" required></textarea>
                    </div>
                </div>
            </div>

            <!-- Dynamic Attributes Section -->
            <div class="card" id="attributesCard" style="display: none;">
                <div class="card-header">
                    Product Attributes
                    <span id="categoryNameDisplay" class="float-right"></span>
                </div>
                <div class="card-body" id="attributesContainer"></div>
            </div>

            <!-- Variants Section -->
            <div class="card" id="variantsCard" style="display: none;">
                <div class="card-header">
                    Product Variants
                    <span class="float-right">Configure price and stock for each combination</span>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <button type="button" class="btn btn-secondary btn-sm" id="generateAllBtn">✨ Generate All Combinations</button>
                        <button type="button" class="btn btn-secondary btn-sm" id="clearVariantsBtn">🗑️ Clear All</button>
                        <button type="button" class="btn btn-success btn-sm" id="autoSkuBtn">🏷️ Auto-generate SKUs</button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered variants-table" id="variantsTable">
                            <thead id="variantsHeader"></thead>
                            <tbody id="variantsBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Images -->
            <div class="card">
                <div class="card-header">Product Images</div>
                <div class="card-body">
                    <input type="file" name="images[]" class="form-control-file" multiple accept="image/*">
                    <small class="text-muted">You can upload multiple images</small>
                </div>
            </div>
        </div>
    </div>

    <div class="action-buttons">
        <button type="button" class="btn btn-secondary" onclick="window.history.back()">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Product</button>
    </div>
</form>

@push('scripts')
<script>
let selectedAttributes = {};

$(document).ready(function() {
    $('#categorySelect').change(function() {
        loadCategoryAttributes($(this).val());
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
            } else {
                $('#attributesCard').hide();
                showSimpleVariantForm();
            }
        },
        error: function() {
            showSimpleVariantForm();
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
        clearVariants();
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
    
    // Header
    let headerHtml = '<tr><th>#</th>';
    attributeNames.forEach(attr => {
        headerHtml += `<th>${attr}</th>`;
    });
    headerHtml += '<th>SKU</th><th>Price (₹)</th><th>Sale Price (₹)</th><th>Stock</th><th>Actions</th></tr>';
    thead.html(headerHtml);
    
    // Body
    tbody.empty();
    combinations.forEach((combo, index) => {
        const productName = $('#productName').val() || 'PRODUCT';
        const skuBase = productName.toUpperCase().replace(/[^A-Z0-9]/g, '_').substring(0, 10);
        const attrCode = combo.map(c => c.value.name.substring(0, 3).toUpperCase()).join('-');
        const defaultSKU = `${skuBase}-${attrCode}`;
        
        let rowHtml = `<tr data-combination='${JSON.stringify(combo)}'>
            <td><strong>${index + 1}</strong></td>`;
        
        attributeNames.forEach(attr => {
            const value = combo.find(c => c.attribute === attr)?.value.name || '-';
            rowHtml += `<td>${value}</td>`;
        });
        
        rowHtml += `
            <td><input type="text" name="variants[${index}][sku]" value="${defaultSKU}" class="form-control form-control-sm" style="width: 140px;" required></td>
            <td><input type="number" name="variants[${index}][price]" value="999" class="form-control form-control-sm" style="width: 100px;" step="0.01" required></td>
            <td><input type="number" name="variants[${index}][sale_price]" class="form-control form-control-sm" style="width: 100px;" step="0.01" placeholder="Optional"></td>
            <td><input type="number" name="variants[${index}][stock]" value="0" class="form-control form-control-sm" style="width: 80px;" required></td>
            <td><button type="button" class="btn btn-danger btn-sm" onclick="deleteVariant(this)">🗑️</button></td>
        </tr>`;
        
        // Add hidden fields for attribute values
        combo.forEach(c => {
            rowHtml += `<input type="hidden" name="variants[${index}][attribute_values][]" value="${c.value.id}">`;
        });
        
        tbody.append(rowHtml);
    });
    
    $('#variantsCard').show();
}

function clearVariants() {
    $('#variantsHeader').empty();
    $('#variantsBody').empty();
    $('#variantsCard').hide();
}

function deleteVariant(button) {
    $(button).closest('tr').remove();
    if ($('#variantsBody tr').length === 0) {
        $('#variantsCard').hide();
    }
}

function showSimpleVariantForm() {
    const tbody = $('#variantsBody');
    tbody.empty();
    
    const rowHtml = `
        <tr>
            <td><strong>1</strong></td>
            <td>-</td>
            <td><input type="text" name="variants[0][sku]" class="form-control form-control-sm" style="width: 140px;" required></td>
            <td><input type="number" name="variants[0][price]" class="form-control form-control-sm" style="width: 100px;" step="0.01" required></td>
            <td><input type="number" name="variants[0][sale_price]" class="form-control form-control-sm" style="width: 100px;" step="0.01" placeholder="Optional"></td>
            <td><input type="number" name="variants[0][stock]" class="form-control form-control-sm" style="width: 80px;" required></td>
            <td><button type="button" class="btn btn-danger btn-sm" onclick="deleteVariant(this)">🗑️</button></td>
        </tr>
    `;
    tbody.append(rowHtml);
    $('#variantsCard').show();
}

// Button handlers
$('#generateAllBtn').click(function() {
    $('.color-option, .size-option, .ram-option, .storage-option').each(function() {
        if (!$(this).hasClass('selected')) {
            $(this).click();
        }
    });
});

$('#clearVariantsBtn').click(function() {
    clearVariants();
    $('.color-option, .size-option, .ram-option, .storage-option').removeClass('selected');
    selectedAttributes = {};
});

$('#autoSkuBtn').click(function() {
    const productName = $('#productName').val() || 'PRODUCT';
    const rows = $('#variantsBody tr');
    rows.each(function(index) {
        const cells = $(this).find('td');
        const skuInput = cells.eq(cells.length - 5).find('input');
        if (skuInput && !skuInput.val()) {
            // Generate SKU from attribute values
            let attrValues = [];
            for (let i = 1; i < cells.length - 5; i++) {
                attrValues.push(cells.eq(i).text().substring(0, 3).toUpperCase());
            }
            const attrCode = attrValues.join('-');
            skuInput.val(`${productName.toUpperCase().replace(/[^A-Z0-9]/g, '_').substring(0, 10)}-${attrCode}`);
        }
    });
});

// Load default category if preselected
@if(old('category_id'))
    loadCategoryAttributes('{{ old('category_id') }}');
@endif
</script>
@endpush
@endsection