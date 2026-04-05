{{-- resources/views/admin/attribute-groups/edit.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Edit Attribute Group')
@section('header', 'Edit Group: ' . $attributeGroup->name)

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Group Information</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.attribute-groups.update', $attributeGroup) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="form-group">
                        <label>Group Name *</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $attributeGroup->name) }}" required>
                        @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description', $attributeGroup->description) }}</textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $attributeGroup->sort_order) }}">
                        <small class="text-muted">Lower numbers appear first</small>
                    </div>
                    
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" name="is_active" class="custom-control-input" id="is_active" value="1" {{ old('is_active', $attributeGroup->is_active) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_active">Active</label>
                        </div>
                    </div>
                    
                    <div class="form-group text-right">
                        <a href="{{ route('admin.attribute-groups.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Group</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Assigned Attributes</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#addAttributesModal">
                        <i class="fas fa-plus"></i> Add Attributes
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="assignedAttributesContainer">
                    <table class="table table-bordered" id="attributesTable">
                        <thead>
                            <tr>
                                <th>Attribute</th>
                                <th>Sort Order</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="attributesBody">
                            @foreach($attributeGroup->attributes as $index => $attribute)
                            <tr data-attribute-id="{{ $attribute->id }}">
                                <td>
                                    {{ $attribute->name }}
                                    <input type="hidden" name="attributes[]" value="{{ $attribute->id }}">
                                </td>
                                <td>
                                    <input type="number" name="attribute_orders[{{ $attribute->id }}]" value="{{ $attribute->pivot->sort_order ?? $index }}" class="form-control form-control-sm" style="width: 70px;">
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-danger remove-attribute" data-id="{{ $attribute->id }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Attributes Modal -->
<div class="modal fade" id="addAttributesModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Attributes to Group</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Select Attributes</label>
                    <select id="attributeSelect" class="form-control">
                        <option value="">Select an attribute</option>
                        @foreach($attributes as $attribute)
                            @if(!in_array($attribute->id, $assignedAttributes))
                                <option value="{{ $attribute->id }}" data-name="{{ $attribute->name }}">
                                    {{ $attribute->name }} ({{ $attribute->type }})
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Sort Order</label>
                    <input type="number" id="attrSortOrder" class="form-control" value="0">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmAddAttribute">Add Attribute</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#confirmAddAttribute').click(function() {
        const attrId = $('#attributeSelect').val();
        const attrName = $('#attributeSelect').find(':selected').data('name');
        const sortOrder = $('#attrSortOrder').val();
        
        if (!attrId) {
            alert('Please select an attribute');
            return;
        }
        
        if ($(`#attributesBody tr[data-attribute-id="${attrId}"]`).length > 0) {
            alert('Attribute already added');
            return;
        }
        
        const row = `
            <tr data-attribute-id="${attrId}">
                <td>
                    ${attrName}
                    <input type="hidden" name="attributes[]" value="${attrId}">
                </td>
                <td>
                    <input type="number" name="attribute_orders[${attrId}]" value="${sortOrder}" class="form-control form-control-sm" style="width: 70px;">
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger remove-attribute" data-id="${attrId}">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        
        $('#attributesBody').append(row);
        
        $('#attributeSelect').val('');
        $('#attrSortOrder').val(0);
        $('#addAttributesModal').modal('hide');
    });
    
    $(document).on('click', '.remove-attribute', function() {
        $(this).closest('tr').remove();
    });
});
</script>
@endpush
@endsection