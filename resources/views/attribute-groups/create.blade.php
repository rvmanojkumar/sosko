{{-- resources/views/admin/attribute-groups/create.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Add Attribute Group')
@section('header', 'Add Attribute Group')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Group Information</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.attribute-groups.store') }}" method="POST">
                    @csrf
                    
                    <div class="form-group">
                        <label>Group Name *</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}">
                        <small class="text-muted">Lower numbers appear first</small>
                    </div>
                    
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" name="is_active" class="custom-control-input" id="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_active">Active</label>
                        </div>
                    </div>
                    
                    <div class="form-group text-right">
                        <a href="{{ route('admin.attribute-groups.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Create Group</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Assign Attributes</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#addAttributesModal">
                        <i class="fas fa-plus"></i> Add Attributes
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="assignedAttributesContainer">
                    <div class="alert alert-info text-center" id="noAttributesMsg">
                        <i class="fas fa-info-circle"></i> No attributes assigned yet.
                    </div>
                    <table class="table table-bordered" id="attributesTable" style="display: none;">
                        <thead>
                            <tr>
                                <th>Attribute</th>
                                <th>Sort Order</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="attributesBody"></tbody>
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
                            <option value="{{ $attribute->id }}" data-name="{{ $attribute->name }}">
                                {{ $attribute->name }} ({{ $attribute->type }})
                            </option>
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
    let selectedAttributes = [];
    
    $('#confirmAddAttribute').click(function() {
        const attrId = $('#attributeSelect').val();
        const attrName = $('#attributeSelect').find(':selected').data('name');
        const sortOrder = $('#attrSortOrder').val();
        
        if (!attrId) {
            alert('Please select an attribute');
            return;
        }
        
        if (selectedAttributes.find(a => a.id == attrId)) {
            alert('Attribute already added');
            return;
        }
        
        selectedAttributes.push({ id: attrId, name: attrName, sort_order: sortOrder });
        updateAttributesTable();
        
        $('#attributeSelect').val('');
        $('#attrSortOrder').val(0);
        $('#addAttributesModal').modal('hide');
    });
    
    function updateAttributesTable() {
        if (selectedAttributes.length === 0) {
            $('#attributesTable').hide();
            $('#noAttributesMsg').show();
            return;
        }
        
        $('#noAttributesMsg').hide();
        $('#attributesTable').show();
        $('#attributesBody').empty();
        
        selectedAttributes.forEach((attr, index) => {
            const row = `
                <tr data-attribute-id="${attr.id}">
                    <td>
                        ${attr.name}
                        <input type="hidden" name="attributes[]" value="${attr.id}">
                    </td>
                    <td>
                        <input type="number" name="attribute_orders[${attr.id}]" value="${attr.sort_order}" class="form-control form-control-sm" style="width: 70px;">
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger remove-attribute" data-id="${attr.id}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            $('#attributesBody').append(row);
        });
    }
    
    $(document).on('click', '.remove-attribute', function() {
        const id = $(this).data('id');
        selectedAttributes = selectedAttributes.filter(a => a.id != id);
        updateAttributesTable();
    });
});
</script>
@endpush
@endsection