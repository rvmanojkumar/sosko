@extends('admin.layouts.app')

@section('title', 'Vendors')
@section('header', 'Vendors')

@section('content')
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $stats['total'] }}</h3>
                <p>Total Vendors</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $stats['pending'] }}</h3>
                <p>Pending Approval</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $stats['approved'] }}</h3>
                <p>Approved</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $stats['rejected'] + $stats['suspended'] }}</h3>
                <p>Inactive</p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col-md-6">
                <form method="GET" class="form-inline">
                    <input type="text" name="search" class="form-control" placeholder="Search vendors..." value="{{ request('search') }}">
                    <select name="status" class="form-control ml-2">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                    </select>
                    <button type="submit" class="btn btn-primary ml-2">Filter</button>
                    <a href="{{ route('admin.vendors.index') }}" class="btn btn-secondary ml-2">Reset</a>
                </form>
            </div>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover">
            <thead>
                 <tr>
                    <th>Store Name</th>
                    <th>Owner</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Products</th>
                    <th>Status</th>
                    <th>Actions</th>
                 </tr>
            </thead>
            <tbody>
                @forelse($vendors as $vendor)
                 <tr>
                    <td>
                        <strong>{{ $vendor->store_name }}</strong><br>
                        <small class="text-muted">Since: {{ $vendor->created_at->format('d M Y') }}</small>
                    </td>
                    <td>{{ $vendor->user->name ?? 'N/A' }}</td>
                    <td>{{ $vendor->contact_email ?? $vendor->user->email ?? 'N/A' }}</td>
                    <td>{{ $vendor->contact_phone ?? $vendor->user->phone ?? 'N/A' }}</td>
                    <td>{{ $vendor->products()->count() }}</td>
                    <td>
                        @php
                            $badge = 'secondary';
                            if($vendor->status == 'approved') $badge = 'success';
                            elseif($vendor->status == 'pending') $badge = 'warning';
                            elseif($vendor->status == 'rejected') $badge = 'danger';
                            elseif($vendor->status == 'suspended') $badge = 'danger';
                        @endphp
                        <span class="badge badge-{{ $badge }}">{{ ucfirst($vendor->status) }}</span>
                    </td>
                    <td>
                        <a href="{{ route('admin.vendors.show', $vendor) }}" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i>
                        </a>
                     </td>
                 </tr>
                @empty
                 <tr>
                    <td colspan="7" class="text-center">No vendors found.</td>
                 </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $vendors->links() }}
    </div>
</div>
@endsection