{{-- resources/views/admin/vendors/show.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Vendor Details')
@section('header', 'Vendor: ' . $vendor->store_name)

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Store Information</h3>
            </div>
            <div class="card-body text-center">
                @if($vendor->logo)
                    <img src="{{ Storage::url($vendor->logo) }}" class="img-fluid mb-3" style="max-width: 150px; max-height: 150px;">
                @else
                    <div class="bg-secondary d-inline-flex align-items-center justify-content-center mb-3" style="width: 150px; height: 150px; border-radius: 8px;">
                        <i class="fas fa-store fa-4x text-white"></i>
                    </div>
                @endif
                <h4>{{ $vendor->store_name }}</h4>
                <p class="text-muted">{{ $vendor->store_slug }}</p>
                <p>
                    <span class="badge badge-{{ $vendor->status == 'approved' ? 'success' : ($vendor->status == 'pending' ? 'warning' : 'danger') }} badge-lg">
                        {{ ucfirst($vendor->status) }}
                    </span>
                </p>
                @if($vendor->rating)
                    <p>
                        @for($i = 1; $i <= 5; $i++)
                            @if($i <= floor($vendor->rating))
                                <i class="fas fa-star text-warning"></i>
                            @elseif($i - 0.5 <= $vendor->rating)
                                <i class="fas fa-star-half-alt text-warning"></i>
                            @else
                                <i class="far fa-star text-warning"></i>
                            @endif
                        @endfor
                        <span class="ml-1">({{ $vendor->rating }})</span>
                    </p>
                @endif
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Contact Information</h3>
            </div>
            <div class="card-body">
                <p><strong>Owner:</strong> {{ $vendor->user->name ?? 'N/A' }}</p>
                <p><strong>Email:</strong> {{ $vendor->contact_email ?? $vendor->user->email ?? 'N/A' }}</p>
                <p><strong>Phone:</strong> {{ $vendor->contact_phone ?? $vendor->user->phone ?? 'N/A' }}</p>
                <p><strong>Address:</strong> {{ $vendor->address ?? 'N/A' }}</p>
                @if($vendor->latitude && $vendor->longitude)
                    <p><strong>Location:</strong> {{ $vendor->latitude }}, {{ $vendor->longitude }}</p>
                @endif
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Statistics</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <div class="info-box bg-light">
                            <div class="info-box-content">
                                <span class="info-box-text text-center">Products</span>
                                <span class="info-box-number text-center">{{ $vendor->products()->count() }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="info-box bg-light">
                            <div class="info-box-content">
                                <span class="info-box-text text-center">Orders</span>
                                <span class="info-box-number text-center">{{ $vendor->orders()->count() }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-6">
                        <div class="info-box bg-light">
                            <div class="info-box-content">
                                <span class="info-box-text text-center">Total Earnings</span>
                                <span class="info-box-number text-center">₹{{ number_format($vendor->totalEarnings(), 2) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="info-box bg-light">
                            <div class="info-box-content">
                                <span class="info-box-text text-center">Pending Earnings</span>
                                <span class="info-box-number text-center">₹{{ number_format($vendor->pendingEarnings(), 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-6">
                        <div class="info-box bg-light">
                            <div class="info-box-content">
                                <span class="info-box-text text-center">Followers</span>
                                <span class="info-box-number text-center">{{ $vendor->follower_count ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="info-box bg-light">
                            <div class="info-box-content">
                                <span class="info-box-text text-center">Reviews</span>
                                <span class="info-box-number text-center">{{ $vendor->reviews()->count() }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        @if($vendor->currentSubscription)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Subscription</h3>
            </div>
            <div class="card-body">
                <p><strong>Plan:</strong> {{ $vendor->currentSubscription->plan->name ?? 'N/A' }}</p>
                <p><strong>Status:</strong>
                    <span class="badge badge-{{ $vendor->currentSubscription->isActive() ? 'success' : 'danger' }}">
                        {{ $vendor->currentSubscription->status }}
                    </span>
                </p>
                <p><strong>Start Date:</strong> {{ $vendor->currentSubscription->start_date->format('d M Y') }}</p>
                <p><strong>End Date:</strong> {{ $vendor->currentSubscription->end_date->format('d M Y') }}</p>
                <p><strong>Days Remaining:</strong> {{ $vendor->currentSubscription->daysRemaining() }}</p>
                @if($vendor->currentSubscription->razorpay_subscription_id)
                    <p><strong>Razorpay ID:</strong> {{ $vendor->currentSubscription->razorpay_subscription_id }}</p>
                @endif
            </div>
        </div>
        @endif
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Actions</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    @if($vendor->status == 'pending')
                        <div class="col-md-6">
                            <form action="{{ route('admin.vendors.approve', $vendor) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-success btn-block">
                                    <i class="fas fa-check"></i> Approve Vendor
                                </button>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <button type="button" class="btn btn-danger btn-block" data-toggle="modal" data-target="#rejectModal">
                                <i class="fas fa-times"></i> Reject Vendor
                            </button>
                        </div>
                    @elseif($vendor->status == 'approved')
                        <div class="col-md-4">
                            <button type="button" class="btn btn-warning btn-block" data-toggle="modal" data-target="#suspendModal">
                                <i class="fas fa-pause"></i> Suspend
                            </button>
                        </div>
                        <div class="col-md-4">
                            <form action="{{ route('admin.vendors.destroy', $vendor) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-block" onclick="return confirm('Are you sure you want to delete this vendor?')">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('admin.products.index', ['vendor' => $vendor->user_id]) }}" class="btn btn-info btn-block">
                                <i class="fas fa-box"></i> View Products
                            </a>
                        </div>
                    @elseif($vendor->status == 'suspended')
                        <div class="col-md-6">
                            <form action="{{ route('admin.vendors.activate', $vendor) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-success btn-block">
                                    <i class="fas fa-play"></i> Activate Vendor
                                </button>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <form action="{{ route('admin.vendors.destroy', $vendor) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-block" onclick="return confirm('Are you sure?')">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Products ({{ $vendor->products->count() }})</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.products.index', ['vendor' => $vendor->user_id]) }}" class="btn btn-sm btn-primary">View All</a>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Sales</th>
                            <th>Status</th>
                        </thead>
                    <tbody>
                        @forelse($vendor->products->take(5) as $product)
                         <tr>
                            <td>
                                <strong>{{ $product->name }}</strong><br>
                                <small class="text-muted">SKU: {{ $product->variants->first()->sku ?? 'N/A' }}</small>
                            </td>
                            <td>₹{{ number_format($product->variants->first()->price ?? 0, 2) }}</td>
                            <td>
                                <span class="badge badge-{{ ($product->variants->first()->stock_quantity ?? 0) > 10 ? 'success' : 'warning' }}">
                                    {{ $product->variants->first()->stock_quantity ?? 0 }}
                                </span>
                            </td>
                            <td>{{ $product->sold_count ?? 0 }}</td>
                            <td>
                                <span class="badge badge-{{ $product->is_active ? 'success' : 'danger' }}">
                                    {{ $product->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                         </tr>
                        @empty
                         <tr>
                            <td colspan="5" class="text-center">No products found.</td>
                         </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Recent Orders</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.orders.index', ['vendor' => $vendor->id]) }}" class="btn btn-sm btn-primary">View All</a>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover">
                    <thead>
                         <tr>
                            <th>Order #</th>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                         </tr>
                    </thead>
                    <tbody>
                        @forelse($vendor->orders()->with('order')->latest()->take(5)->get() as $item)
                         <tr>
                            <td>{{ $item->order->order_number ?? 'N/A' }}</td>
                            <td>{{ $item->product_name }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>₹{{ number_format($item->total_price, 2) }}</td>
                            <td>
                                <span class="badge badge-{{ $item->status == 'delivered' ? 'success' : ($item->status == 'cancelled' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>
                            <td>{{ $item->created_at->format('d M Y') }}</td>
                         </tr>
                        @empty
                         <tr>
                            <td colspan="6" class="text-center">No orders found.</td>
                         </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Documents</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    @forelse($vendor->documents as $document)
                        <div class="col-md-6 mb-3">
                            <div class="card card-outline card-{{ $document->status == 'verified' ? 'success' : ($document->status == 'rejected' ? 'danger' : 'warning') }}">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-file-alt"></i> {{ ucfirst(str_replace('_', ' ', $document->document_type)) }}
                                    </h5>
                                </div>
                                <div class="card-body">
                                    @if($document->document_number)
                                        <p><strong>Number:</strong> {{ $document->document_number }}</p>
                                    @endif
                                    <p><strong>Status:</strong>
                                        <span class="badge badge-{{ $document->status == 'verified' ? 'success' : ($document->status == 'rejected' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($document->status) }}
                                        </span>
                                    </p>
                                    @if($document->document_url)
                                        <a href="{{ $document->document_url }}" target="_blank" class="btn btn-sm btn-info">
                                            <i class="fas fa-download"></i> View Document
                                        </a>
                                    @endif
                                    @if($document->remarks)
                                        <p class="mt-2"><small class="text-muted">Remarks: {{ $document->remarks }}</small></p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <p class="text-center text-muted">No documents uploaded.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Recent Earnings</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.vendor-earnings.index', $vendor) }}" class="btn btn-sm btn-primary">View All</a>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover">
                    <thead>
                         <tr>
                            <th>Order #</th>
                            <th>Order Amount</th>
                            <th>Commission</th>
                            <th>Vendor Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                         </tr>
                    </thead>
                    <tbody>
                        @forelse($vendor->earnings()->with('order')->latest()->take(5)->get() as $earning)
                         <tr>
                            <td>{{ $earning->order->order_number ?? 'N/A' }}</td>
                            <td>₹{{ number_format($earning->order_amount, 2) }}</td>
                            <td>₹{{ number_format($earning->commission_amount, 2) }}</td>
                            <td>₹{{ number_format($earning->vendor_amount, 2) }}</td>
                            <td>
                                <span class="badge badge-{{ $earning->status == 'processed' ? 'success' : 'warning' }}">
                                    {{ ucfirst($earning->status) }}
                                </span>
                            </td>
                            <td>{{ $earning->created_at->format('d M Y') }}</td>
                         </tr>
                        @empty
                         <tr>
                            <td colspan="6" class="text-center">No earnings found.</td>
                         </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Bank Accounts</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    @forelse($vendor->bankAccounts as $account)
                        <div class="col-md-6 mb-3">
                            <div class="card card-outline card-{{ $account->is_default ? 'primary' : 'secondary' }}">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-university"></i> {{ $account->bank_name }}
                                        @if($account->is_default)
                                            <span class="badge badge-primary ml-2">Default</span>
                                        @endif
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p><strong>Account Holder:</strong> {{ $account->account_holder_name }}</p>
                                    <p><strong>Account Number:</strong> {{ $account->masked_account_number }}</p>
                                    <p><strong>IFSC Code:</strong> {{ $account->ifsc_code }}</p>
                                    @if($account->upi_id)
                                        <p><strong>UPI ID:</strong> {{ $account->upi_id }}</p>
                                    @endif
                                    <p><strong>Status:</strong>
                                        <span class="badge badge-{{ $account->is_verified ? 'success' : 'warning' }}">
                                            {{ $account->is_verified ? 'Verified' : 'Pending Verification' }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <p class="text-center text-muted">No bank accounts added.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.vendors.reject', $vendor) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Reject Vendor</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Reason for rejection</label>
                        <textarea name="rejection_reason" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Vendor</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Suspend Modal -->
<div class="modal fade" id="suspendModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.vendors.suspend', $vendor) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Suspend Vendor</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Reason for suspension</label>
                        <textarea name="suspension_reason" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Suspend Vendor</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection