{{-- resources/views/admin/banners/index.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Banners')
@section('header', 'Banners')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col-md-6">
                <form method="GET" class="form-inline">
                    <select name="type" class="form-control">
                        <option value="">All Types</option>
                        <option value="hero_slider" {{ request('type') == 'hero_slider' ? 'selected' : '' }}>Hero Slider</option>
                        <option value="category_banner" {{ request('type') == 'category_banner' ? 'selected' : '' }}>Category Banner</option>
                        <option value="popup" {{ request('type') == 'popup' ? 'selected' : '' }}>Popup</option>
                        <option value="app_notification" {{ request('type') == 'app_notification' ? 'selected' : '' }}>App Notification</option>
                    </select>
                    <select name="is_active" class="form-control ml-2">
                        <option value="">All Status</option>
                        <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    <button type="submit" class="btn btn-primary ml-2">Filter</button>
                    <a href="{{ route('admin.banners.index') }}" class="btn btn-secondary ml-2">Reset</a>
                </form>
            </div>
            <div class="col-md-6 text-right">
                <a href="{{ route('admin.banners.create') }}" class="btn btn-success">
                    <i class="fas fa-plus"></i> Add Banner
                </a>
            </div>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover">
            <thead>
                 <tr>
                    <th>Image</th>
                    <th>Title</th>
                    <th>Type</th>
                    <th>CTA Text</th>
                    <th>Sort Order</th>
                    <th>Status</th>
                    <th>Valid Period</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($banners as $banner)
                <tr>
                    <td>
                        @if($banner->image_path)
                            <img src="{{asset('storage/' .$banner->image_path) }}" width="60" height="40" style="object-fit: cover;">
                        @endif
                    </td>
                    <td>
                        <strong>{{ $banner->title }}</strong>
                        @if($banner->subtitle)
                            <br><small class="text-muted">{{ $banner->subtitle }}</small>
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-info">{{ str_replace('_', ' ', ucfirst($banner->type)) }}</span>
                    </td>
                    <td>{{ $banner->cta_text ?? '-' }}</td>
                    <td>{{ $banner->sort_order }}</td>
                    <td>
                        <span class="badge badge-{{ $banner->is_active ? 'success' : 'danger' }}">
                            {{ $banner->is_active ? 'Active' : 'Inactive' }}
                        </span>
                        @if($banner->start_date && $banner->start_date > now())
                            <br><small class="text-warning">Scheduled</small>
                        @endif
                    </td>
                    <td>
                        <small>
                            @if($banner->start_date)
                                From: {{ $banner->start_date->format('d M Y') }}<br>
                            @endif
                            @if($banner->end_date)
                                To: {{ $banner->end_date->format('d M Y') }}
                            @endif
                        </small>
                    </td>
                    <td>
                        <a href="{{ route('admin.banners.edit', $banner) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('admin.banners.destroy', $banner) }}" method="POST" class="d-inline">
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
                    <td colspan="8" class="text-center">No banners found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $banners->links() }}
    </div>
</div>
@endsection