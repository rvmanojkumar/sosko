{{-- resources/views/admin/attributes/edit.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Edit Attribute')
@section('header', 'Edit Attribute: ' . $attribute->name)

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Attribute Information</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.attributes.update', $attribute) }}" method="POST" id="attributeForm">
                    @csrf
                    @method('PUT')
                    
                    <div class="form-group">
                        <label>Attribute Name *</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $attribute->name) }}" required>
                        @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Type *</label>
                                <select name="type" id="type" class="form-control" required>
                                    <option value="select" {{ old('type', $attribute->type) == 'select' ? 'selected' : '' }}>Select Dropdown</option>
                                    <option value="color" {{ old('type', $attribute->type) == 'color' ? 'selected' : '' }}>Color Picker</option>
                                    <option value="size" {{ old('type', $attribute->type) == 'size' ? 'selected' : '' }}>Size Selector</option>
                                    <option value="radio" {{ old('type', $attribute->type) == 'radio' ? 'selected' : '' }}>Radio Buttons</option>
                                    <option value="checkbox" {{ old('type', $attribute->type) == 'checkbox' ? 'selected' : '' }}>Checkboxes</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Display Type *</label>
                                <select name="display_type" id="display_type" class="form-control" required>
                                    <option value="dropdown" {{ old('display_type', $attribute->display_type) == 'dropdown' ? 'selected' : '' }}>Dropdown</option>
                                    <option value="button" {{ old('display_type', $attribute->display_type) == 'button' ? 'selected' : '' }}>Buttons</option>
                                    <option value="swatch" {{ old('display_type', $attribute->display_type) == 'swatch' ? 'selected' : '' }}>Color Swatches</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Sort Order</label>
                                <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $attribute->sort_order) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" name="is_required" class="custom-control-input" id="is_required" value="1" {{ old('is_required', $attribute->is_required) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_required">Required attribute</label>
                                </div>
                                <div class="custom-control custom-switch mt-2">
                                    <input type="checkbox" name="is_filterable" class="custom-control-input" id="is_filterable" value="1" {{ old('is_filterable', $attribute->is_filterable) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_filterable">Filterable attribute</label>
                                </div>
                                <div class="custom-control custom-switch mt-2">
                                    <input type="checkbox" name="is_global" class="custom-control-input" id="is_global" value="1" {{ old('is_global', $attribute->is_global) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_global">Global attribute</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description', $attribute->description) }}</textarea>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Attribute Values</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-success" id="addValueBtn">
                        <i class="fas fa-plus"></i> Add Value
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="valuesContainer">
                    <table class="table table-bordered" id="valuesTable" style="{{ $attribute->values->count() > 0 ? '' : 'display: none;' }}">
                        <thead>
                            <tr>
                                <th>Value</th>
                                <th>Color Code</th>
                                <th>Sort Order</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="valuesBody">
                            @foreach($attribute->values as $index => $value)
                            <tr data-value-id="{{ $value->id }}">
                                <td>
                                    <input type="text" name="values[{{ $index }}][value]" class="form-control" value="{{ $value->value }}" required>
                                    <input type="hidden" name="values[{{ $index }}][id]" value="{{ $value->id }}">
                                </td>
                                <td>
                                    <input type="color" name="values[{{ $index }}][color_code]" class="form-control" value="{{ $value->color_code }}" style="width: 60px;">
                                </td>
                                <td>
                                    <input type="number" name="values[{{ $index }}][sort_order]" class="form-control" value="{{ $value->sort_order }}" style="width: 70px;">
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-danger remove-value" data-id="{{ $value->id }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div id="noValuesMsg" class="alert alert-info text-center" style="{{ $attribute->values->count() > 0 ? 'display: none;' : '' }}">
                    <i class="fas fa-info-circle"></i> No values added yet. Click "Add Value" to add attribute options.
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Preview</h3>
            </div>
            <div class="card-body">
                <div id="previewContainer">
                    @include('admin.attributes.preview', ['attribute' => $attribute])
                </div>
            </div>
        </div>
    </div>
</div>

<div class="form-group text-right">
    <a href="{{ route('admin.attributes.index') }}" class="btn btn-secondary">Cancel</a>
    <button type="submit" form="attributeForm" class="btn btn-primary">Update Attribute</button>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    let valueIndex = {{ $attribute->values->count() }};
    
    // Add value row
    $('#addValueBtn').click(function() {
        $('#noValuesMsg').hide();
        $('#valuesTable').show();
        
        const row = `
            <tr data-value-index="${valueIndex}">
                <td>
                    <input type="text" name="values[${valueIndex}][value]" class="form-control" placeholder="e.g., Red, S, 4GB" required>
                </td>
                <td>
                    <input type="color" name="values[${valueIndex}][color_code]" class="form-control" style="width: 60px;">
                </td>
                <td>
                    <input type="number" name="values[${valueIndex}][sort_order]" class="form-control" value="0" style="width: 70px;">
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger remove-value" data-index="${valueIndex}">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        
        $('#valuesBody').append(row);
        valueIndex++;
        updatePreview();
    });
    
    // Remove value row
    $(document).on('click', '.remove-value', function() {
        const row = $(this).closest('tr');
        const valueId = row.data('value-id');
        
        if (valueId) {
            // Mark for deletion by adding a hidden field
            row.append(`<input type="hidden" name="deleted_values[]" value="${valueId}">`);
        }
        
        row.remove();
        
        if ($('#valuesBody tr').length === 0) {
            $('#valuesTable').hide();
            $('#noValuesMsg').show();
        }
        updatePreview();
    });
    
    // Update preview when type or values change
    $('#type, #display_type').on('change', updatePreview);
    $(document).on('change', 'input[name*="[value]"]', updatePreview);
    $(document).on('input', 'input[name*="[value]"]', updatePreview);
    
    function updatePreview() {
        const type = $('#type').val();
        const displayType = $('#display_type').val();
        const values = [];
        
        $('input[name*="[value]"]').each(function() {
            const val = $(this).val();
            if (val && !$(this).closest('tr').hasClass('deleted')) {
                values.push(val);
            }
        });
        
        let previewHtml = '';
        
        if (type === 'color') {
            previewHtml = '<div class="d-flex flex-wrap gap-2">';
            values.forEach(value => {
                previewHtml += `<div class="color-swatch" style="width: 40px; height: 40px; background: #ccc; border-radius: 50%; margin: 5px; border: 1px solid #ddd;" title="${value}"></div>`;
            });
            previewHtml += '</div>';
        } else if (displayType === 'button') {
            previewHtml = '<div class="d-flex flex-wrap gap-2">';
            values.forEach(value => {
                previewHtml += `<button class="btn btn-outline-secondary btn-sm" style="margin: 5px;" disabled>${value}</button>`;
            });
            previewHtml += '</div>';
        } else if (displayType === 'dropdown') {
            previewHtml = '<select class="form-control" disabled><option>Select option</option>';
            values.forEach(value => {
                previewHtml += `<option>${value}</option>`;
            });
            previewHtml += '</select>';
        } else {
            previewHtml = '<div class="text-muted">Preview not available for this configuration</div>';
        }
        
        $('#previewContainer').html(previewHtml || '<div class="text-muted text-center">Add values to see preview</div>');
    }
});
</script>
@endpush
@endsection