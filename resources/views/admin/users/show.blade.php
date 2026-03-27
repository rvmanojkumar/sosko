{{-- resources/views/admin/users/show.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'User Details')
@section('header', 'User: ' . $user->name)

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Profile Information</h3>
            </div>
            <div class="card-body text-center">
                @if($user->profile_photo)
                    <img src="{{ Storage::url($user->profile_photo) }}" class="img-circle elevation-2" width="150" height="150" style="object-fit: cover;">
                @else
                    <div class="bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 150px; height: 150px;">
                        <i class="fas fa-user fa-4x text-white"></i>
                    </div>
                @endif
                <h4 class="mt-3">{{ $user->name }}</h4>
                <p class="text-muted">{{ $user->email }}</p>
                <p class="text-muted">{{ $user->phone }}</p>
                <p>
                    @foreach($user->roles as $role)
                        <span class="badge badge-{{ $role->name == 'super-admin' ? 'danger' : ($role->name == 'admin' ? 'warning' : ($role->name == 'vendor' ? 'info' : 'success')) }} badge-lg">
                            {{ ucfirst($role->name) }}
                        </span>
                    @endforeach
                </p>
                <p>
                    <span class="badge badge-{{ $user->is_active ? 'success' : 'danger' }} badge-lg">
                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </p>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Statistics</h3>
            </div>
            <div class="card-body">
                <p><strong>Total Orders:</strong> {{ $user->orders->count() }}</p>
                <p><strong>Total Spent:</strong> ₹{{ number_format($user->orders->where('payment_status', 'paid')->sum('total_amount'), 2) }}</p>
                <p><strong>Wishlist Items:</strong> {{ $user->wishlist->count() }}</p>
                <p><strong>Reviews Written:</strong> {{ $user->reviews->count() }}</p>
                <p><strong>Joined:</strong> {{ $user->created_at->format('d M Y, h:i A') }}</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Recent Orders</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($user->orders->take(10) as $order)
                        <tr>
                            <td>{{ $order->order_number }}</td>
                            <td>₹{{ number_format($order->total_amount, 2) }}</td>
                            <td>
                                <span class="badge badge-{{ $order->order_status == 'delivered' ? 'success' : 'warning' }}">
                                    {{ ucfirst($order->order_status) }}
                                </span>
                            </td>
                            <td>{{ $order->created_at->format('d M Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center">No orders found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Addresses</h3>
            </div>
            <div class="card-body">
                @forelse($user->addresses as $address)
                    <div class="mb-3 p-3 border rounded">
                        <strong>{{ ucfirst($address->address_type) }}</strong>
                        @if($address->is_default)
                            <span class="badge badge-success ml-2">Default</span>
                        @endif
                        <p class="mt-2 mb-0">{{ $address->address_line1 }}</p>
                        @if($address->address_line2)
                            <p class="mb-0">{{ $address->address_line2 }}</p>
                        @endif
                        <p class="mb-0">{{ $address->city }}, {{ $address->state }}</p>
                        <p class="mb-0">{{ $address->postal_code }}, {{ $address->country }}</p>
                        <p class="mb-0">Phone: {{ $address->phone }}</p>
                    </div>
                @empty
                    <p>No addresses found.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection