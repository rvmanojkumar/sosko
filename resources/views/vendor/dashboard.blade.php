{{-- resources/views/vendor/dashboard.blade.php --}}
@extends('vendor.layouts.app')

@section('title', 'Dashboard')
@section('header', 'Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Date Range Filter -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="form-inline">
                        <label class="mr-2">Date Range:</label>
                        <select name="date_range" class="form-control mr-2" onchange="this.form.submit()">
                            <option value="today" {{ $dateRange == 'today' ? 'selected' : '' }}>Today</option>
                            <option value="yesterday" {{ $dateRange == 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                            <option value="this_week" {{ $dateRange == 'this_week' ? 'selected' : '' }}>This Week</option>
                            <option value="last_week" {{ $dateRange == 'last_week' ? 'selected' : '' }}>Last Week</option>
                            <option value="this_month" {{ $dateRange == 'this_month' ? 'selected' : '' }}>This Month</option>
                            <option value="last_month" {{ $dateRange == 'last_month' ? 'selected' : '' }}>Last Month</option>
                            <option value="this_year" {{ $dateRange == 'this_year' ? 'selected' : '' }}>This Year</option>
                            <option value="last_30_days" {{ $dateRange == 'last_30_days' ? 'selected' : '' }}>Last 30 Days</option>
                        </select>
                        <a href="{{ route('vendor.report.export', ['date_range' => $dateRange]) }}" class="btn btn-success ml-2">
                            <i class="fas fa-download"></i> Export Report
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>₹{{ number_format($stats['total_sales'], 0) }}</h3>
                    <p>Total Sales</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                @if($stats['sales_growth'] != 0)
                    <a href="#" class="small-box-footer">
                        @if($stats['sales_growth'] > 0)
                            <i class="fas fa-arrow-up text-success"></i> {{ $stats['sales_growth'] }}% from last month
                        @else
                            <i class="fas fa-arrow-down text-danger"></i> {{ abs($stats['sales_growth']) }}% from last month
                        @endif
                    </a>
                @endif
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['total_orders'] }}</h3>
                    <p>Total Orders</p>
                </div>
                <div class="icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <a href="{{ route('vendor.orders.index') }}" class="small-box-footer">
                    View Orders <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['total_products'] }}</h3>
                    <p>Total Products</p>
                </div>
                <div class="icon">
                    <i class="fas fa-box"></i>
                </div>
                <a href="{{ route('vendor.products.index') }}" class="small-box-footer">
                    Manage Products <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>₹{{ number_format($stats['pending_earnings'], 0) }}</h3>
                    <p>Pending Earnings</p>
                </div>
                <div class="icon">
                    <i class="fas fa-wallet"></i>
                </div>
                <a href="{{ route('vendor.earnings.index') }}" class="small-box-footer">
                    View Details <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Second Row Statistics -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="fas fa-chart-simple"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Avg. Order Value</span>
                    <span class="info-box-number">₹{{ number_format($stats['average_order_value'], 2) }}</span>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="fas fa-eye"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Views</span>
                    <span class="info-box-number">{{ number_format($stats['total_views']) }}</span>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="info-box">
                <span class="info-box-icon bg-warning"><i class="fas fa-chart-line"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Conversion Rate</span>
                    <span class="info-box-number">{{ $stats['conversion_rate'] }}%</span>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="info-box">
                <span class="info-box-icon bg-primary"><i class="fas fa-trophy"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">All Time Earnings</span>
                    <span class="info-box-number">₹{{ number_format($stats['total_earnings_all_time'], 0) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Tables -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Sales Overview</h3>
                </div>
                <div class="card-body">
                    <canvas id="salesChart" height="300"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Top Selling Products</h3>
                </div>
                <div class="card-body p-0">
                    <ul class="products-list product-list-in-card">
                        @foreach($topProducts as $product)
                        <li class="item">
                            <div class="product-img">
                                @if($product['image'])
                                    <img src="{{ $product['image'] }}" alt="Product Image" class="img-size-50">
                                @else
                                    <div class="bg-secondary text-white text-center" style="width: 50px; height: 50px; line-height: 50px;">
                                        <i class="fas fa-box"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="product-info">
                                <a href="{{ route('vendor.products.edit', $product['id']) }}" class="product-title">
                                    {{ $product['name'] }}
                                </a>
                                <span class="product-description">
                                    Sold: {{ $product['quantity'] }} units
                                </span>
                                <span class="product-description">
                                    Revenue: ₹{{ number_format($product['revenue'], 2) }}
                                </span>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Recent Orders</h3>
                    <div class="card-tools">
                        <a href="{{ route('vendor.orders.index') }}" class="btn btn-sm btn-primary">View All</a>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Product</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentOrders as $order)
                            <tr>
                                <td>{{ $order['order_number'] }}</td>
                                <td>{{ $order['customer_name'] }}</td>
                                <td>{{ Str::limit($order['product_name'], 30) }}</td>
                                <td>₹{{ number_format($order['total'], 2) }}</td>
                                <td>
                                    <span class="badge badge-{{ $order['status_badge'] }}">
                                        {{ ucfirst($order['status']) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Recent Earnings</h3>
                    <div class="card-tools">
                        <a href="{{ route('vendor.earnings.index') }}" class="btn btn-sm btn-primary">View All</a>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Amount</th>
                                <th>Commission</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentEarnings as $earning)
                            <tr>
                                <td>{{ $earning['order_number'] }}</td>
                                <td>₹{{ number_format($earning['amount'], 2) }}</td>
                                <td>₹{{ number_format($earning['commission'], 2) }}</td>
                                <td>
                                    <span class="badge badge-{{ $earning['status_badge'] }}">
                                        {{ ucfirst($earning['status']) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @if($lowStockProducts->count() > 0)
    <div class="row">
        <div class="col-12">
            <div class="card card-danger">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-exclamation-triangle"></i> Low Stock Alert
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($lowStockProducts as $product)
                        <div class="col-md-4">
                            <div class="alert alert-warning">
                                <strong>{{ $product['name'] }}</strong>
                                @foreach($product['variants'] as $variant)
                                    <div class="small">
                                        SKU: {{ $variant['sku'] }} - Stock: 
                                        <span class="text-danger">{{ $variant['stock'] }}</span>
                                        (Threshold: {{ $variant['threshold'] }})
                                    </div>
                                @endforeach
                                <a href="{{ route('vendor.products.edit', $product['id']) }}" class="btn btn-sm btn-primary mt-2">
                                    Update Stock
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($subscription && $plan)
    <div class="row">
        <div class="col-12">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">Subscription Status</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Plan:</strong> {{ $plan->name }}
                        </div>
                        <div class="col-md-3">
                            <strong>Status:</strong>
                            <span class="badge badge-{{ $subscription->isActive() ? 'success' : 'danger' }}">
                                {{ $subscription->status }}
                            </span>
                        </div>
                        <div class="col-md-3">
                            <strong>Expires:</strong> {{ $subscription->end_date->format('d M Y') }}
                        </div>
                        <div class="col-md-3">
                            <strong>Days Left:</strong> {{ $subscription->daysRemaining() }}
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="progress">
                            @php
                                $totalDays = $subscription->start_date->diffInDays($subscription->end_date);
                                $usedDays = $subscription->start_date->diffInDays(now());
                                $percentage = ($usedDays / $totalDays) * 100;
                            @endphp
                            <div class="progress-bar bg-info" style="width: {{ $percentage }}%">
                                {{ round($percentage) }}% Used
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Sales Chart
    const ctx = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($salesData['labels']),
            datasets: [{
                label: 'Sales (₹)',
                data: @json($salesData['values']),
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
</script>
@endpush