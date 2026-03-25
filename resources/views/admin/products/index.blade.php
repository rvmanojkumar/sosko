{{-- resources/views/admin/products/index.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Products')
@section('header', 'Products')
@section('breadcrumb')
    <li class="breadcrumb-item active">Products</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col-md-6">
                <form method="GET" class="form-inline">
                    <input type="text" name="search" class="form-control" placeholder="Search products..." value="{{ request('search') }}">
                    <select name="category" class="form-control ml-2">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    <select name="vendor" class="form-control ml-2">
                        <option value="">All Vendors</option>
                        @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}" {{ request('vendor') == $vendor->id ? 'selected' : '' }}>
                                {{ $vendor->name }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary ml-2">Filter</button>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary ml-2">Reset</a>
                </form>
            </div>
            <div class="col-md-6 text-right">
                <a href="{{ route('admin.products.create') }}" class="btn btn-success">
                    <i class="fas fa-plus"></i> Add Product
                </a>
            </div>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th width="50"><input type="checkbox" id="select-all"></th>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Vendor</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th width="150">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr>
                    <td><input type="checkbox" class="product-checkbox" value="{{ $product->id }}"></td>
                    <td>{{ $product->id }}</td>
                    <td>
                        @if($product->images->first())
                            <img src="{{ Storage::url($product->images->first()->image_path) }}" width="50" height="50" style="object-fit: cover; border-radius: 4px;">
                        @else
                            <div class="bg-secondary text-white text-center" style="width: 50px; height: 50px; line-height: 50px; border-radius: 4px;">
                                <i class="fas fa-image"></i>
                            </div>
                        @endif
                    </td>
                    <td>
                        <strong>{{ $product->name }}</strong><br>
                        <small class="text-muted">SKU: {{ $product->default_variant->sku ?? 'N/A' }}</small>
                    </td>
                    <td>{{ $product->vendor->name ?? 'N/A' }}</td>
                    <td>{{ $product->category->name ?? 'N/A' }}</td>
                    <td>
                        @if($product->default_variant)
                            @if($product->default_variant->sale_price)
                                <del class="text-muted">₹{{ number_format($product->default_variant->price, 2) }}</del><br>
                                <span class="text-success">₹{{ number_format($product->default_variant->sale_price, 2) }}</span>
                            @else
                                ₹{{ number_format($product->default_variant->price, 2) }}
                            @endif
                        @else
                            N/A
                        @endif
                    </td>
                    <td>
                        @php $stock = $product->default_variant->stock_quantity ?? 0; @endphp
                        <span class="badge badge-{{ $stock > 10 ? 'success' : ($stock > 0 ? 'warning' : 'danger') }}">
                            {{ $stock }}
                        </span>
                    </td>
                    <td>
                        <span class="badge badge-{{ $product->is_active ? 'success' : 'danger' }}">
                            {{ $product->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('admin.products.show', $product) }}" class="btn btn-sm btn-info" title="View">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-primary" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center py-4">No products found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        <div class="row">
            <div class="col-md-6">
                <button id="bulk-delete" class="btn btn-danger" style="display: none;">
                    <i class="fas fa-trash"></i> Delete Selected
                </button>
            </div>
            <div class="col-md-6 text-right">
                {{ $products->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Select all checkbox
    $('#select-all').on('change', function() {
        $('.product-checkbox').prop('checked', $(this).prop('checked'));
        toggleBulkDelete();
    });

    $('.product-checkbox').on('change', function() {
        toggleBulkDelete();
    });

    function toggleBulkDelete() {
        var checked = $('.product-checkbox:checked').length;
        if (checked > 0) {
            $('#bulk-delete').show();
        } else {
            $('#bulk-delete').hide();
        }
    }

    // Bulk delete
    $('#bulk-delete').on('click', function() {
        var ids = [];
        $('.product-checkbox:checked').each(function() {
            ids.push($(this).val());
        });
        
        if (ids.length > 0 && confirm('Delete ' + ids.length + ' products?')) {
            $.ajax({
                url: '{{ route("admin.products.bulk-delete") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    ids: ids
                },
                success: function() {
                    location.reload();
                }
            });
        }
    });
</script>
@endpush
@endsection