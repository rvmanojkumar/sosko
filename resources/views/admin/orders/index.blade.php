@extends('admin.layouts.app')

@section('title', 'Orders')
@section('header', 'Orders')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col-md-6">
                <form method="GET" class="form-inline">
                    <input type="text" name="search" class="form-control" placeholder="Search by order number..." value="{{ request('search') }}">
                    <select name="status" class="form-control ml-2">
                        <option value="">All Status</option>
                        <option value="placed" {{ request('status') == 'placed' ? 'selected' : '' }}>Placed</option>
                        <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>Shipped</option>
                        <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                    <button type="submit" class="btn btn-primary ml-2">Filter</button>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary ml-2">Reset</a>
                </form>
            </div>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                <tr>
                    <td>{{ $order->order_number }}</td>
                    <td>{{ $order->user->name ?? 'N/A' }}</td>
                    <td>₹{{ number_format($order->total_amount, 2) }}</td>
                    <td>
                        @php
                            $badge = 'secondary';
                            if($order->order_status == 'delivered') $badge = 'success';
                            elseif($order->order_status == 'cancelled') $badge = 'danger';
                            elseif(in_array($order->order_status, ['placed','confirmed','processing'])) $badge = 'warning';
                            elseif($order->order_status == 'shipped') $badge = 'info';
                        @endphp
                        <span class="badge badge-{{ $badge }}">{{ ucfirst($order->order_status) }}</span>
                    </td>
                    <td>
                        <span class="badge badge-{{ $order->payment_status == 'paid' ? 'success' : 'warning' }}">
                            {{ ucfirst($order->payment_status) }}
                        </span>
                    </td>
                    <td>{{ $order->created_at->format('d M Y') }}</td>
                    <td>
                        <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center">No orders found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $orders->links() }}
    </div>
</div>
@endsection