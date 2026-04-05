{{-- resources/views/admin/categories/create.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Add Category')
@section('header', 'Add Category')

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Basic Information</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data" id="categoryForm">
                    @csrf
                    
                    <div class="form-group">
                        <label>Name *</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label>Parent Category</label>
                        <select name="parent_id" class="form-control">
                            <option value="">None (Root Category)</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('parent_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}">
                        <small class="text-muted">Lower numbers appear first</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Status</label>
                        <select name="is_active" class="form-control">
                            <option value="1" {{ old('is_active', 1) == 1 ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('is_active') == 0 ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Icon Image</label>
                        <input type="file" name="icon" class="form-control-file" accept="image/*">
                        <small class="text-muted">Recommended size: 64x64px</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Banner Image</label>
                        <input type="file" name="banner_image" class="form-control-file" accept="image/*">
                        <small class="text-muted">Recommended size: 1200x400px</small>
                    </div>
                    
                    <div class="form-group text-right">
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Create Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Assign Attributes (Optional)</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-success" id="addAttributeBtn">
                        <i class="fas fa-plus"></i> Add Attribute
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="assignedAttributesContainer">
                    <table class="table table-bordered" id="attributesTable" style="display: none;">
                        <thead>
                            <tr>
                                <th>Attribute Name</th>
                                <th>Required</th>
                                <th>Filterable</th>
                                <th>Sort Order</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="attributesBody"></tbody>
                    </table>
                    <div id="noAttributesMsg" class="alert alert-info text-center">
                        <i class="fas fa-info-circle"></i> No attributes assigned yet. Click "Add Attribute" to assign.
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Assign Attribute Groups (Optional)</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-success" id="addGroupBtn">
                        <i class="fas fa-plus"></i> Add Group
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="assignedGroupsContainer">
                    <table class="table table-bordered" id="groupsTable" style="display: none;">
                        <thead>
                            <tr>
                                <th>Group Name</th>
                                <th>Sort Order</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="groupsBody"></tbody>
                     </table>
                    <div id="noGroupsMsg" class="alert alert-info text-center">
                        <i class="fas fa-info-circle"></i> No attribute groups assigned yet.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Attribute Modal -->
<div class="modal fade" id="addAttributeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Attribute to Category</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Select Attribute</label>
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
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="attrIsRequired">
                    <label class="form-check-label">Required attribute</label>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="attrIsFilterable" checked>
                    <label class="form-check-label">Filterable attribute</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmAddAttribute">Add Attribute</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Group Modal -->
<div class="modal fade" id="addGroupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Attribute Group</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Select Attribute Group</label>
                    <select id="groupSelect" class="form-control">
                        <option value="">Select a group</option>
                        @foreach($attributeGroups as $group)
                            <option value="{{ $group->id }}" data-name="{{ $group->name }}">
                                {{ $group->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Sort Order</label>
                    <input type="number" id="groupSortOrder" class="form-control" value="0">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmAddGroup">Add Group</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Add Attribute Button
    $('#addAttributeBtn').click(function() {
        $('#addAttributeModal').modal('show');
    });
    
    // Add Group Button
    $('#addGroupBtn').click(function() {
        $('#addGroupModal').modal('show');
    });
    
    // Confirm Add Attribute
    $('#confirmAddAttribute').click(function() {
        const attrId = $('#attributeSelect').val();
        const attrName = $('#attributeSelect').find(':selected').data('name');
        const sortOrder = $('#attrSortOrder').val();
        const isRequired = $('#attrIsRequired').is(':checked') ? 1 : 0;
        const isFilterable = $('#attrIsFilterable').is(':checked') ? 1 : 0;
        
        if (!attrId) {
            alert('Please select an attribute');
            return;
        }
        
        addAttributeToTable(attrId, attrName, sortOrder, isRequired, isFilterable);
        
        $('#attributeSelect').val('');
        $('#attrSortOrder').val(0);
        $('#attrIsRequired').prop('checked', false);
        $('#attrIsFilterable').prop('checked', true);
        $('#addAttributeModal').modal('hide');
    });
    
    function addAttributeToTable(attrId, attrName, sortOrder, isRequired, isFilterable) {
        if ($(`#attributesBody tr[data-attribute-id="${attrId}"]`).length > 0) {
            alert('Attribute already added');
            return;
        }
        
        $('#noAttributesMsg').hide();
        $('#attributesTable').show();
        
        const row = `
            <tr data-attribute-id="${attrId}">
                <td>
                    ${attrName}
                    <input type="hidden" name="attributes[${attrId}][id]" value="${attrId}">
                </td>
                <td>
                    <input type="checkbox" name="attributes[${attrId}][is_required]" value="1" ${isRequired ? 'checked' : ''}>
                </td>
                <td>
                    <input type="checkbox" name="attributes[${attrId}][is_filterable]" value="1" ${isFilterable ? 'checked' : ''}>
                </td>
                <td>
                    <input type="number" name="attributes[${attrId}][sort_order]" value="${sortOrder}" class="form-control form-control-sm" style="width: 70px;">
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger remove-attribute" data-id="${attrId}">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        
        $('#attributesBody').append(row);
    }
    
    // Confirm Add Group
    $('#confirmAddGroup').click(function() {
        const groupId = $('#groupSelect').val();
        const groupName = $('#groupSelect').find(':selected').data('name');
        const sortOrder = $('#groupSortOrder').val();
        
        if (!groupId) {
            alert('Please select a group');
            return;
        }
        
        if ($(`#groupsBody tr[data-group-id="${groupId}"]`).length > 0) {
            alert('Group already added');
            return;
        }
        
        $('#noGroupsMsg').hide();
        $('#groupsTable').show();
        
        const row = `
            <tr data-group-id="${groupId}">
                <td>
                    ${groupName}
                    <input type="hidden" name="attribute_groups[${groupId}][id]" value="${groupId}">
                </td>
                <td>
                    <input type="number" name="attribute_groups[${groupId}][sort_order]" value="${sortOrder}" class="form-control form-control-sm" style="width: 70px;">
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger remove-group" data-id="${groupId}">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        
        $('#groupsBody').append(row);
        
        $('#groupSelect').val('');
        $('#groupSortOrder').val(0);
        $('#addGroupModal').modal('hide');
    });
    
    // Remove attribute
    $(document).on('click', '.remove-attribute', function() {
        $(this).closest('tr').remove();
        if ($('#attributesBody tr').length === 0) {
            $('#attributesTable').hide();
            $('#noAttributesMsg').show();
        }
    });
    
    // Remove group
    $(document).on('click', '.remove-group', function() {
        $(this).closest('tr').remove();
        if ($('#groupsBody tr').length === 0) {
            $('#groupsTable').hide();
            $('#noGroupsMsg').show();
        }
    });
});
</script>
@endpush
@endsection