{{-- resources/views/admin/vendor-earnings/vendor.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Vendor Earnings - ' . $vendor->store_name)
@section('header', 'Earnings: ' . $vendor->store_name)

@section('content')
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>₹{{ number_format($stats['total'], 2) }}</h3>
                <p>Total Earnings</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>₹{{ number_format($stats['pending'], 2) }}</h3>
                <p>Pending</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>₹{{ number_format($stats['processed'], 2) }}</h3>
                <p>Processed</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ $stats['order_count'] }}</h3>
                <p>Orders</p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Earnings History</h3>
        <div class="card-tools">
            <a href="{{ route('admin.vendor-earnings.export', ['vendor_id' => $vendor->id]) }}" class="btn btn-sm btn-success">
                <i class="fas fa-download"></i> Export
            </a>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover">
            <thead>
                针
                    <th>Order #</th>
                    <th>Order Amount</th>
                    <th>Commission</th>
                    <th>Vendor Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </thead>
            <tbody>
                @forelse($earnings as $earning)
                <tr>
                    <td>{{ $earning->order->order_number ?? 'N/A' }}</td>
                    <td>₹{{ number_format($earning->order_amount, 2) }}</td>
                    <td>
                        {{ $earning->commission_rate }}%<br>
                        <small>₹{{ number_format($earning->commission_amount, 2) }}</small>
                    </td>
                    <td class="text-success">₹{{ number_format($earning->vendor_amount, 2) }}</td>
                    <td>
                        <span class="badge badge-{{ $earning->status == 'pending' ? 'warning' : 'success' }}">
                            {{ ucfirst($earning->status) }}
                        </span>
                    </td>
                    <td>{{ $earning->created_at->format('d M Y') }}</td>
                    <td>
                        <a href="{{ route('admin.vendor-earnings.show', $earning) }}" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center">No earnings found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $earnings->links() }}
    </div>
</div>

<div class="text-right">
    <a href="{{ route('admin.vendors.show', $vendor) }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Vendor
    </a>
</div>
@endsection