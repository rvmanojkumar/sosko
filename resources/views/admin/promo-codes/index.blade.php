{{-- resources/views/admin/promo-codes/index.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Promo Codes')
@section('header', 'Promo Codes')

@section('content')
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $stats['total'] }}</h3>
                <p>Total Promo Codes</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $stats['active'] }}</h3>
                <p>Active</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $stats['expired'] }}</h3>
                <p>Expired</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $stats['total_used'] }}</h3>
                <p>Total Uses</p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col-md-6">
                <form method="GET" class="form-inline">
                    <input type="text" name="search" class="form-control" placeholder="Search by code..." value="{{ request('search') }}">
                    <select name="type" class="form-control ml-2">
                        <option value="">All Types</option>
                        <option value="flat" {{ request('type') == 'flat' ? 'selected' : '' }}>Flat</option>
                        <option value="percentage" {{ request('type') == 'percentage' ? 'selected' : '' }}>Percentage</option>
                    </select>
                    <select name="is_active" class="form-control ml-2">
                        <option value="">All Status</option>
                        <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    <button type="submit" class="btn btn-primary ml-2">Filter</button>
                    <a href="{{ route('admin.promo-codes.index') }}" class="btn btn-secondary ml-2">Reset</a>
                </form>
            </div>
            <div class="col-md-6 text-right">
                <a href="{{ route('admin.promo-codes.create') }}" class="btn btn-success">
                    <i class="fas fa-plus"></i> Add Promo Code
                </a>
            </div>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover">
            <thead>
                 <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Value</th>
                    <th>Uses</th>
                    <th>Valid Period</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($promoCodes as $promoCode)
                <tr>
                    <td><strong>{{ $promoCode->code }}</strong></td>
                    <td>{{ $promoCode->name ?? '-' }}</td>
                    <td>
                        <span class="badge badge-{{ $promoCode->type == 'percentage' ? 'info' : 'warning' }}">
                            {{ ucfirst($promoCode->type) }}
                        </span>
                    </td>
                    <td>
                        @if($promoCode->type == 'percentage')
                            {{ $promoCode->value }}% OFF
                        @else
                            ₹{{ number_format($promoCode->value, 2) }} OFF
                        @endif
                    </td>
                    <td>{{ $promoCode->used_count }} / {{ $promoCode->usage_limit ?? '∞' }}</td>
                    <td>
                        <small>
                            {{ $promoCode->start_date->format('d M Y') }}<br>
                            to {{ $promoCode->end_date->format('d M Y') }}
                        </small>
                    </td>
                    <td>
                        @if($promoCode->is_active && $promoCode->end_date > now())
                            <span class="badge badge-success">Active</span>
                        @elseif($promoCode->end_date <= now())
                            <span class="badge badge-danger">Expired</span>
                        @else
                            <span class="badge badge-secondary">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.promo-codes.edit', $promoCode) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('admin.promo-codes.destroy', $promoCode) }}" method="POST" class="d-inline">
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
                    <td colspan="8" class="text-center">No promo codes found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $promoCodes->links() }}
    </div>
</div>
@endsection