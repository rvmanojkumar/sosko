{{-- resources/views/admin/vendor-earnings/index.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Vendor Earnings')
@section('header', 'Vendor Earnings')

@section('content')
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>₹{{ number_format($stats['total_earnings'], 2) }}</h3>
                <p>Total Earnings</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>₹{{ number_format($stats['pending_earnings'], 2) }}</h3>
                <p>Pending</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>₹{{ number_format($stats['processed_earnings'], 2) }}</h3>
                <p>Processed</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>₹{{ number_format($stats['this_month'], 2) }}</h3>
                <p>This Month</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Monthly Earnings ({{ now()->year }})</h3>
            </div>
            <div class="card-body">
                <canvas id="earningsChart" height="300"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Top Vendors</h3>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @foreach($topVendors as $vendor)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $vendor->store_name }}</strong><br>
                                <small>{{ $vendor->user->name ?? 'N/A' }}</small>
                            </div>
                            <span class="badge badge-primary badge-lg">
                                ₹{{ number_format($vendor->earnings_sum_vendor_amount ?? 0, 2) }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col-md-6">
                <form method="GET" class="form-inline">
                    <select name="vendor_id" class="form-control">
                        <option value="">All Vendors</option>
                        @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                {{ $vendor->store_name }}
                            </option>
                        @endforeach
                    </select>
                    <select name="status" class="form-control ml-2">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="processed" {{ request('status') == 'processed' ? 'selected' : '' }}>Processed</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                    <input type="date" name="date_from" class="form-control ml-2" value="{{ request('date_from') }}" placeholder="From">
                    <input type="date" name="date_to" class="form-control ml-2" value="{{ request('date_to') }}" placeholder="To">
                    <button type="submit" class="btn btn-primary ml-2">Filter</button>
                    <a href="{{ route('admin.vendor-earnings.index') }}" class="btn btn-secondary ml-2">Reset</a>
                    <a href="{{ route('admin.vendor-earnings.export', request()->all()) }}" class="btn btn-success ml-2">
                        <i class="fas fa-download"></i> Export
                    </a>
                </form>
            </div>
            <div class="col-md-6 text-right">
                <button type="button" class="btn btn-primary" id="bulkProcessBtn" style="display: none;">
                    <i class="fas fa-check-circle"></i> Process Selected
                </button>
            </div>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover">
            <thead>
                针
                    <th width="50"><input type="checkbox" id="select-all"></th>
                    <th>Vendor</th>
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
                    <td>
                        @if($earning->status == 'pending')
                            <input type="checkbox" class="earning-checkbox" value="{{ $earning->id }}">
                        @endif
                    </td>
                    <td>
                        <strong>{{ $earning->vendorProfile->store_name ?? 'N/A' }}</strong><br>
                        <small class="text-muted">{{ $earning->vendorProfile->user->name ?? 'N/A' }}</small>
                    </td>
                    <td>{{ $earning->order->order_number ?? 'N/A' }}</td>
                    <td>₹{{ number_format($earning->order_amount, 2) }}</td>
                    <td>
                        {{ $earning->commission_rate }}%<br>
                        <small class="text-muted">₹{{ number_format($earning->commission_amount, 2) }}</small>
                    </td>
                    <td class="text-success font-weight-bold">₹{{ number_format($earning->vendor_amount, 2) }}</td>
                    <td>
                        @if($earning->status == 'pending')
                            <span class="badge badge-warning">Pending</span>
                        @elseif($earning->status == 'processed')
                            <span class="badge badge-success">Processed</span>
                        @else
                            <span class="badge badge-danger">Failed</span>
                        @endif
                    </td>
                    <td>
                        {{ $earning->created_at->format('d M Y') }}<br>
                        <small class="text-muted">{{ $earning->created_at->format('h:i A') }}</small>
                    </td>
                    <td>
                        <a href="{{ route('admin.vendor-earnings.show', $earning) }}" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i>
                        </a>
                        @if($earning->status == 'pending')
                            <form action="{{ route('admin.vendor-earnings.process', $earning) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Mark this earning as processed?')">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-4">No earnings found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $earnings->links() }}
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Earnings Chart
    const ctx = document.getElementById('earningsChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Earnings (₹)',
                data: @json($earningsData),
                borderColor: '#2980B9',
                backgroundColor: 'rgba(41, 128, 185, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top' }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₹' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Bulk process
    $('#select-all').on('change', function() {
        $('.earning-checkbox').prop('checked', $(this).prop('checked'));
        toggleBulkProcess();
    });

    $('.earning-checkbox').on('change', function() {
        toggleBulkProcess();
    });

    function toggleBulkProcess() {
        var checked = $('.earning-checkbox:checked').length;
        $('#bulkProcessBtn').toggle(checked > 0);
    }

    $('#bulkProcessBtn').on('click', function() {
        var ids = [];
        $('.earning-checkbox:checked').each(function() {
            ids.push($(this).val());
        });
        
        if (ids.length > 0 && confirm('Process ' + ids.length + ' earnings?')) {
            $.ajax({
                url: '{{ route("admin.vendor-earnings.bulk-process") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    earning_ids: ids
                },
                success: function(response) {
                    location.reload();
                },
                error: function() {
                    alert('Failed to process earnings');
                }
            });
        }
    });
</script>
@endpush
@endsection