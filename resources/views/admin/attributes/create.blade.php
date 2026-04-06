{{-- resources/views/admin/attributes/create.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Add Attribute')
@section('header', 'Add Attribute')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Attribute Information</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.attributes.store') }}" method="POST" id="attributeForm">
                    @csrf
                    
                    <div class="form-group">
                        <label>Attribute Name *</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Type *</label>
                                <select name="type" id="type" class="form-control" required>
                                    <option value="select" {{ old('type') == 'select' ? 'selected' : '' }}>Select Dropdown</option>
                                    <option value="color" {{ old('type') == 'color' ? 'selected' : '' }}>Color Picker</option>
                                    <option value="size" {{ old('type') == 'size' ? 'selected' : '' }}>Size Selector</option>
                                    <option value="radio" {{ old('type') == 'radio' ? 'selected' : '' }}>Radio Buttons</option>
                                    <option value="checkbox" {{ old('type') == 'checkbox' ? 'selected' : '' }}>Checkboxes</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Display Type *</label>
                                <select name="display_type" id="display_type" class="form-control" required>
                                    <option value="dropdown" {{ old('display_type') == 'dropdown' ? 'selected' : '' }}>Dropdown</option>
                                    <option value="button" {{ old('display_type') == 'button' ? 'selected' : '' }}>Buttons</option>
                                    <option value="swatch" {{ old('display_type') == 'swatch' ? 'selected' : '' }}>Color Swatches</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Sort Order</label>
                                <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" name="is_required" class="custom-control-input" id="is_required" value="1" {{ old('is_required') ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_required">Required attribute</label>
                                </div>
                                <div class="custom-control custom-switch mt-2">
                                    <input type="checkbox" name="is_filterable" class="custom-control-input" id="is_filterable" value="1" {{ old('is_filterable', 1) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_filterable">Filterable attribute</label>
                                </div>
                                <div class="custom-control custom-switch mt-2">
                                    <input type="checkbox" name="is_global" class="custom-control-input" id="is_global" value="1" {{ old('is_global', 1) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_global">Global attribute</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
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
                    <table class="table table-bordered" id="valuesTable" style="display: none;">
                        <thead>
                            <tr>
                                <th>Value</th>
                                <th>Color Code</th>
                                <th>Sort Order</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="valuesBody"></tbody>
                    </table>
                </div>
                <div id="noValuesMsg" class="alert alert-info text-center">
                    <i class="fas fa-info-circle"></i> No values added yet. Click "Add Value" to add attribute options.
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Preview</h3>
            </div>
            <div class="card-body">
                <div id="previewContainer" class="text-center text-muted">
                    Select attribute values to see preview
                </div>
            </div>
        </div>
    </div>
</div>

<div class="form-group text-right">
    <a href="{{ route('admin.attributes.index') }}" class="btn btn-secondary">Cancel</a>
    <button type="submit" form="attributeForm" class="btn btn-primary">Save Attribute</button>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    let valueIndex = 0;
    
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
        $(this).closest('tr').remove();
        if ($('#valuesBody tr').length === 0) {
            $('#valuesTable').hide();
            $('#noValuesMsg').show();
        }
        updatePreview();
    });
    
    // Update preview when type changes or values change
    $('#type, #display_type').on('change', function() {
        updatePreview();
    });
    
    // Listen for value input changes
    $(document).on('change keyup', 'input[name*="[value]"]', function() {
        updatePreview();
    });
    
    function updatePreview() {
        const type = $('#type').val();
        const displayType = $('#display_type').val();
        const values = [];
        
        // Safely collect values
        $('input[name*="[value]"]').each(function() {
            const val = $(this).val();
            if (val && val.trim() !== '') {
                values.push(val.trim());
            }
        });
        
        let previewHtml = '';
        
        if (values.length === 0) {
            previewHtml = '<div class="text-muted text-center">Add values to see preview</div>';
        } else if (type === 'color') {
            previewHtml = '<div class="d-flex flex-wrap gap-2" style="gap: 10px;">';
            values.forEach(value => {
                previewHtml += `<div class="color-swatch" style="width: 40px; height: 40px; background: #ccc; border-radius: 50%; border: 1px solid #ddd;" title="${value}"></div>`;
            });
            previewHtml += '</div>';
        } else if (displayType === 'button') {
            previewHtml = '<div class="d-flex flex-wrap gap-2" style="gap: 8px;">';
            values.forEach(value => {
                previewHtml += `<button class="btn btn-outline-secondary btn-sm" style="margin: 0;" disabled>${escapeHtml(value)}</button>`;
            });
            previewHtml += '</div>';
        } else if (displayType === 'dropdown') {
            previewHtml = `<select class="form-control" disabled style="max-width: 200px;">
                <option>Select option</option>`;
            values.forEach(value => {
                previewHtml += `<option>${escapeHtml(value)}</option>`;
            });
            previewHtml += '</select>';
        } else {
            previewHtml = '<div class="text-muted">Preview not available for this configuration</div>';
        }
        
        $('#previewContainer').html(previewHtml);
    }
    
    // Helper function to escape HTML
    function escapeHtml(text) {
        if (!text) return '';
        return text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }
    
    // Initialize preview
    updatePreview();
});
</script>
@endpush
@endsection