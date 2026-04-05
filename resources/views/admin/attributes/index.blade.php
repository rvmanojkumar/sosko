{{-- resources/views/admin/attributes/index.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Attributes')
@section('header', 'Attributes')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col-md-6">
                <form method="GET" class="form-inline">
                    <input type="text" name="search" class="form-control" placeholder="Search attributes..." value="{{ request('search') }}">
                    <select name="type" class="form-control ml-2">
                        <option value="">All Types</option>
                        <option value="select" {{ request('type') == 'select' ? 'selected' : '' }}>Select</option>
                        <option value="color" {{ request('type') == 'color' ? 'selected' : '' }}>Color</option>
                        <option value="size" {{ request('type') == 'size' ? 'selected' : '' }}>Size</option>
                        <option value="radio" {{ request('type') == 'radio' ? 'selected' : '' }}>Radio</option>
                        <option value="checkbox" {{ request('type') == 'checkbox' ? 'selected' : '' }}>Checkbox</option>
                    </select>
                    <button type="submit" class="btn btn-primary ml-2">Filter</button>
                    <a href="{{ route('admin.attributes.index') }}" class="btn btn-secondary ml-2">Reset</a>
                </form>
            </div>
            <div class="col-md-6 text-right">
                <a href="{{ route('admin.attributes.create') }}" class="btn btn-success">
                    <i class="fas fa-plus"></i> Add Attribute
                </a>
            </div>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Type</th>
                    <th>Display Type</th>
                    <th>Values</th>
                    <th>Sort Order</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attributes as $attribute)
                <tr>
                    <td>{{ $attribute->id }}</td>
                    <td>
                        <strong>{{ $attribute->name }}</strong>
                        @if($attribute->description)
                            <br><small class="text-muted">{{ Str::limit($attribute->description, 50) }}</small>
                        @endif
                    </td>
                    <td>{{ $attribute->slug }}</td>
                    <td>
                        <span class="badge badge-info">{{ ucfirst($attribute->type) }}</span>
                    </td>
                    <td>
                        <span class="badge badge-secondary">{{ ucfirst($attribute->display_type) }}</span>
                    </td>
                    <td>
                        @if($attribute->values->count() > 0)
                            <span class="badge badge-success">{{ $attribute->values->count() }} values</span>
                            <button type="button" class="btn btn-sm btn-link" data-toggle="modal" data-target="#valuesModal{{ $attribute->id }}">
                                View
                            </button>
                        @else
                            <span class="text-muted">No values</span>
                        @endif
                    </td>
                    <td>{{ $attribute->sort_order }}</td>
                    <td>
                        <span class="badge badge-{{ $attribute->is_global ? 'success' : 'secondary' }}">
                            {{ $attribute->is_global ? 'Global' : 'Local' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('admin.attributes.edit', $attribute) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('admin.attributes.destroy', $attribute) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                
                <!-- Values Modal -->
                <div class="modal fade" id="valuesModal{{ $attribute->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">{{ $attribute->name }} - Values</h5>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    @foreach($attribute->values as $value)
                                        <div class="col-md-6 mb-2">
                                            <div class="d-flex align-items-center">
                                                @if($attribute->type == 'color')
                                                    <div class="color-swatch" style="width: 30px; height: 30px; background: {{ $value->color_code }}; border-radius: 50%; margin-right: 10px; border: 1px solid #ddd;"></div>
                                                @endif
                                                <span>{{ $value->value }}</span>
                                                <span class="badge badge-secondary ml-2">Order: {{ $value->sort_order }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-4">No attributes found. <a href="{{ route('admin.attributes.create') }}">Create your first attribute</a></td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $attributes->links() }}
    </div>
</div>
@endsection