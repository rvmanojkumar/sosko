{{-- resources/views/admin/categories/edit.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Edit Category')
@section('header', 'Edit Category: ' . $category->name)

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Basic Information</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.categories.update', $category) }}" method="POST" enctype="multipart/form-data" id="categoryForm">
                    @csrf
                    @method('PUT')
                    
                    <div class="form-group">
                        <label>Name *</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $category->name) }}" required>
                        @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label>Parent Category</label>
                        <select name="parent_id" class="form-control">
                            <option value="">None (Root Category)</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('parent_id', $category->parent_id) == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $category->sort_order) }}">
                        <small class="text-muted">Lower numbers appear first</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Status</label>
                        <select name="is_active" class="form-control">
                            <option value="1" {{ old('is_active', $category->is_active) == 1 ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('is_active', $category->is_active) == 0 ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="4">{{ old('description', $category->description) }}</textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Icon Image</label>
                        @if($category->icon)
                            <div class="mb-2">
                                <img src="{{ Storage::url($category->icon) }}" width="50" height="50" class="img-thumbnail">
                            </div>
                        @endif
                        <input type="file" name="icon" class="form-control-file" accept="image/*">
                        <small class="text-muted">Recommended size: 64x64px</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Banner Image</label>
                        @if($category->banner_image)
                            <div class="mb-2">
                                <img src="{{ Storage::url($category->banner_image) }}" width="100" height="50" class="img-thumbnail">
                            </div>
                        @endif
                        <input type="file" name="banner_image" class="form-control-file" accept="image/*">
                        <small class="text-muted">Recommended size: 1200x400px</small>
                    </div>
                    
                    <div class="form-group text-right">
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <!-- Attribute Assignment Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Category Attributes</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#addAttributeModal">
                        <i class="fas fa-plus"></i> Add Attribute
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="assignedAttributesList">
                    @if($assignedAttributes->count() > 0)
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Attribute Name</th>
                                    <th>Required</th>
                                    <th>Filterable</th>
                                    <th>Sort Order</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="assignedAttributesBody">
                                @foreach($assignedAttributes as $attribute)
                                <tr data-attribute-id="{{ $attribute->id }}">
                                    <td>
                                        {{ $attribute->name }}
                                        <input type="hidden" name="attributes[{{ $attribute->id }}][id]" value="{{ $attribute->id }}">
                                    </td>
                                    <td>
                                        <input type="checkbox" name="attributes[{{ $attribute->id }}][is_required]" value="1" {{ $attribute->pivot->is_required ? 'checked' : '' }} class="attribute-required">
                                    </td>
                                    <td>
                                        <input type="checkbox" name="attributes[{{ $attribute->id }}][is_filterable]" value="1" {{ $attribute->pivot->is_filterable ? 'checked' : '' }} class="attribute-filterable">
                                    </td>
                                    <td>
                                        <input type="number" name="attributes[{{ $attribute->id }}][sort_order]" value="{{ $attribute->pivot->sort_order }}" class="form-control form-control-sm" style="width: 70px;">
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
                    @else
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle"></i> No attributes assigned to this category.
                            <br>Click "Add Attribute" to assign attributes.
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Attribute Groups Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Attribute Groups</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#addGroupModal">
                        <i class="fas fa-plus"></i> Add Group
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="assignedGroupsList">
                    @if($assignedGroups->count() > 0)
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Group Name</th>
                                    <th>Sort Order</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="assignedGroupsBody">
                                @foreach($assignedGroups as $group)
                                <tr data-group-id="{{ $group->id }}">
                                    <td>
                                        {{ $group->name }}
                                        <input type="hidden" name="attribute_groups[{{ $group->id }}][id]" value="{{ $group->id }}">
                                    </td>
                                    <td>
                                        <input type="number" name="attribute_groups[{{ $group->id }}][sort_order]" value="{{ $group->pivot->sort_order }}" class="form-control form-control-sm" style="width: 70px;">
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-danger remove-group" data-id="{{ $group->id }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle"></i> No attribute groups assigned to this category.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Attribute Modal -->
<div class="modal fade" id="addAttributeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Attributes to Category</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Select Attributes</label>
                    <select id="attributeSelect" class="form-control">
                        <option value="">Select an attribute to add</option>
                        @foreach($attributes as $attribute)
                            @if(!$assignedAttributes->has($attribute->id))
                                <option value="{{ $attribute->id }}" data-name="{{ $attribute->name }}">
                                    {{ $attribute->name }} ({{ $attribute->type }})
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div id="selectedAttributesPreview" class="mt-3">
                    <h6>Selected Attributes:</h6>
                    <ul id="selectedAttributesList" class="list-group"></ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="confirmAddAttributes">Add Selected</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Group Modal -->
<div class="modal fade" id="addGroupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Attribute Group to Category</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Select Attribute Group</label>
                    <select id="groupSelect" class="form-control">
                        <option value="">Select a group</option>
                        @foreach($attributeGroups as $group)
                            @if(!$assignedGroups->has($group->id))
                                <option value="{{ $group->id }}" data-name="{{ $group->name }}">
                                    {{ $group->name }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Sort Order</label>
                    <input type="number" id="groupSortOrder" class="form-control" value="0">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="confirmAddGroup">Add Group</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Attribute selection preview
    let selectedAttributes = [];
    
    $('#attributeSelect').change(function() {
        const option = $(this).find(':selected');
        const attrId = option.val();
        const attrName = option.data('name');
        
        if (attrId && !selectedAttributes.find(a => a.id == attrId)) {
            selectedAttributes.push({ id: attrId, name: attrName });
            updateSelectedAttributesList();
        }
        $(this).val('');
    });
    
    function updateSelectedAttributesList() {
        const list = $('#selectedAttributesList');
        list.empty();
        
        selectedAttributes.forEach(attr => {
            list.append(`
                <li class="list-group-item d-flex justify-content-between align-items-center" data-id="${attr.id}">
                    ${attr.name}
                    <button type="button" class="btn btn-sm btn-danger remove-selected-attr" data-id="${attr.id}">
                        <i class="fas fa-times"></i>
                    </button>
                </li>
            `);
        });
        
        $('.remove-selected-attr').click(function() {
            const id = $(this).data('id');
            selectedAttributes = selectedAttributes.filter(a => a.id != id);
            updateSelectedAttributesList();
        });
    }
    
    // Confirm add attributes
    $('#confirmAddAttributes').click(function() {
        selectedAttributes.forEach(attr => {
            addAttributeToTable(attr.id, attr.name);
        });
        
        selectedAttributes = [];
        updateSelectedAttributesList();
        $('#addAttributeModal').modal('hide');
    });
    
    function addAttributeToTable(attrId, attrName) {
        // Check if already exists
        if ($(`#assignedAttributesBody tr[data-attribute-id="${attrId}"]`).length > 0) {
            return;
        }
        
        const row = `
            <tr data-attribute-id="${attrId}">
                <td>
                    ${attrName}
                    <input type="hidden" name="attributes[${attrId}][id]" value="${attrId}">
                </td>
                <td>
                    <input type="checkbox" name="attributes[${attrId}][is_required]" value="1" class="attribute-required">
                </td>
                <td>
                    <input type="checkbox" name="attributes[${attrId}][is_filterable]" value="1" checked class="attribute-filterable">
                </td>
                <td>
                    <input type="number" name="attributes[${attrId}][sort_order]" value="0" class="form-control form-control-sm" style="width: 70px;">
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger remove-attribute" data-id="${attrId}">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        
        $('#assignedAttributesBody').append(row);
        $('#assignedAttributesList table').show();
    }
    
    // Remove attribute from table
    $(document).on('click', '.remove-attribute', function() {
        $(this).closest('tr').remove();
        if ($('#assignedAttributesBody tr').length === 0) {
            $('#assignedAttributesList').html('<div class="alert alert-info text-center">No attributes assigned to this category.</div>');
        }
    });
    
    // Confirm add group
    $('#confirmAddGroup').click(function() {
        const groupId = $('#groupSelect').val();
        const groupName = $('#groupSelect').find(':selected').data('name');
        const sortOrder = $('#groupSortOrder').val();
        
        if (groupId && !$(`#assignedGroupsBody tr[data-group-id="${groupId}"]`).length) {
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
            $('#assignedGroupsBody').append(row);
        }
        
        $('#groupSelect').val('');
        $('#groupSortOrder').val(0);
        $('#addGroupModal').modal('hide');
    });
    
    // Remove group from table
    $(document).on('click', '.remove-group', function() {
        $(this).closest('tr').remove();
        if ($('#assignedGroupsBody tr').length === 0) {
            $('#assignedGroupsList').html('<div class="alert alert-info text-center">No attribute groups assigned to this category.</div>');
        }
    });
});
</script>
@endpush
@endsection