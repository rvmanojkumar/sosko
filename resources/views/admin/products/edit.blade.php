{{-- resources/views/admin/products/edit.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Edit Product')
@section('header', 'Edit Product: ' . $product->name)

@section('content')
<form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Basic Information</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Product Name *</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $product->name) }}" required>
                        @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label>Short Description</label>
                        <input type="text" name="short_description" class="form-control" value="{{ old('short_description', $product->short_description) }}">
                    </div>
                    
                    <div class="form-group">
                        <label>Full Description *</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="8" required>{{ old('description', $product->description) }}</textarea>
                        @error('description')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label>Specifications (JSON)</label>
                        <textarea name="specifications" class="form-control" rows="5">{{ old('specifications', json_encode($product->specifications, JSON_PRETTY_PRINT)) }}</textarea>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Product Variant</h3>
                </div>
                <div class="card-body">
                    @php $variant = $product->variants->first(); @endphp
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>SKU *</label>
                                <input type="text" name="sku" class="form-control @error('sku') is-invalid @enderror" value="{{ old('sku', $variant->sku ?? '') }}" required>
                                @error('sku')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Brand</label>
                                <input type="text" name="brand" class="form-control" value="{{ old('brand', $product->brand) }}">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Price *</label>
                                <input type="number" name="price" step="0.01" class="form-control @error('price') is-invalid @enderror" value="{{ old('price', $variant->price ?? 0) }}" required>
                                @error('price')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Sale Price</label>
                                <input type="number" name="sale_price" step="0.01" class="form-control" value="{{ old('sale_price', $variant->sale_price ?? '') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Stock Quantity *</label>
                                <input type="number" name="stock_quantity" class="form-control @error('stock_quantity') is-invalid @enderror" value="{{ old('stock_quantity', $variant->stock_quantity ?? 0) }}" required>
                                @error('stock_quantity')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Weight (kg)</label>
                                <input type="number" name="weight" step="0.01" class="form-control" value="{{ old('weight', $product->weight) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Low Stock Threshold</label>
                                <input type="number" name="low_stock_threshold" class="form-control" value="{{ old('low_stock_threshold', $variant->low_stock_threshold ?? 5) }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Product Images</h3>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        @foreach($product->images as $image)
                            <div class="col-md-3 mb-2 position-relative" id="image-{{ $image->id }}">
                                <img src="{{ Storage::url($image->image_path) }}" class="img-fluid rounded" style="height: 100px; object-fit: cover; width: 100%;">
                                <button type="button" class="btn btn-sm btn-danger position-absolute" style="top: 5px; right: 15px;" onclick="deleteImage('{{ $image->id }}')">
                                    <i class="fas fa-times"></i>
                                </button>
                                @if($image->is_primary)
                                    <span class="badge badge-success position-absolute" style="bottom: 5px; left: 5px;">Primary</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="form-group">
                        <label>Add More Images</label>
                        <input type="file" name="images[]" class="form-control-file" multiple accept="image/*">
                        <small class="text-muted">You can upload up to 10 images total</small>
                    </div>
                    <div id="image-preview" class="row mt-3"></div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Category & Vendor</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Category *</label>
                        <select name="category_id" class="form-control @error('category_id') is-invalid @enderror" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                                @if($category->children->count())
                                    @foreach($category->children as $child)
                                        <option value="{{ $child->id }}" {{ old('category_id', $product->category_id) == $child->id ? 'selected' : '' }}>
                                            &nbsp;&nbsp;&nbsp;{{ $child->name }}
                                        </option>
                                    @endforeach
                                @endif
                            @endforeach
                        </select>
                        @error('category_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label>Vendor *</label>
                        <select name="vendor_id" class="form-control @error('vendor_id') is-invalid @enderror" required>
                            <option value="">Select Vendor</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}" {{ old('vendor_id', $product->vendor_id) == $vendor->id ? 'selected' : '' }}>
                                    {{ $vendor->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('vendor_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Status</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" name="is_active" class="custom-control-input" id="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_active">Active</label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" name="is_featured" class="custom-control-input" id="is_featured" value="1" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_featured">Featured Product</label>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">SEO</h3>
                </div>
                <div class="card-body">
                    @php $seoData = $product->seo_data ?? []; @endphp
                    <div class="form-group">
                        <label>Meta Title</label>
                        <input type="text" name="seo_data[meta_title]" class="form-control" value="{{ old('seo_data.meta_title', $seoData['meta_title'] ?? '') }}">
                    </div>
                    <div class="form-group">
                        <label>Meta Description</label>
                        <textarea name="seo_data[meta_description]" class="form-control" rows="3">{{ old('seo_data.meta_description', $seoData['meta_description'] ?? '') }}</textarea>
                    </div>
                    <div class="form-group">
                        <label>Meta Keywords</label>
                        <input type="text" name="seo_data[meta_keywords]" class="form-control" value="{{ old('seo_data.meta_keywords', $seoData['meta_keywords'] ?? '') }}">
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="form-group text-right mb-4">
        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary ml-2">Update Product</button>
    </div>
</form>

@push('scripts')
<script>
    // Image preview for new images
    $('input[name="images[]"]').on('change', function() {
        $('#image-preview').empty();
        var files = $(this)[0].files;
        for (var i = 0; i < files.length; i++) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#image-preview').append(`
                    <div class="col-md-3 mb-2">
                        <img src="${e.target.result}" class="img-fluid rounded" style="height: 100px; object-fit: cover;">
                    </div>
                `);
            }
            reader.readAsDataURL(files[i]);
        }
    });
    
    // Delete image function
    function deleteImage(imageId) {
        if (confirm('Are you sure you want to delete this image?')) {
            $.ajax({
                url: '{{ route("admin.products.delete-image", ["product" => $product->id, "image" => ":imageId"]) }}'.replace(':imageId', imageId),
                method: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    $('#image-' + imageId).remove();
                    toastr.success('Image deleted successfully');
                },
                error: function() {
                    toastr.error('Failed to delete image');
                }
            });
        }
    }
</script>
@endpush
@endsection