{{-- resources/views/admin/attribute-groups/index.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Attribute Groups')
@section('header', 'Attribute Groups')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col-md-6">
                <form method="GET" class="form-inline">
                    <input type="text" name="search" class="form-control" placeholder="Search groups..." value="{{ request('search') }}">
                    <button type="submit" class="btn btn-primary ml-2">Search</button>
                    <a href="{{ route('admin.attribute-groups.index') }}" class="btn btn-secondary ml-2">Reset</a>
                </form>
            </div>
            <div class="col-md-6 text-right">
                <a href="{{ route('admin.attribute-groups.create') }}" class="btn btn-success">
                    <i class="fas fa-plus"></i> Add Group
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
                    <th>Description</th>
                    <th>Attributes</th>
                    <th>Sort Order</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($groups as $group)
                <tr>
                    <td>{{ $group->id }}</td>
                    <td>
                        <strong>{{ $group->name }}</strong>
                    </td>
                    <td>{{ $group->slug }}</td>
                    <td>{{ Str::limit($group->description, 50) }}</td>
                    <td>
                        <span class="badge badge-info">{{ $group->attributes_count }} attributes</span>
                    </td>
                    <td>{{ $group->sort_order }}</td>
                    <td>
                        <span class="badge badge-{{ $group->is_active ? 'success' : 'danger' }}">
                            {{ $group->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('admin.attribute-groups.edit', $group) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('admin.attribute-groups.destroy', $group) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-4">No attribute groups found. <a href="{{ route('admin.attribute-groups.create') }}">Create your first group</a></td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $groups->links() }}
    </div>
</div>
@endsection