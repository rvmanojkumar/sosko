{{-- resources/views/admin/banners/create.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Add Banner')
@section('header', 'Add Banner')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Banner Information</h3>
            </div>
            <form action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="title">Title *</label>
                                <input type="text" 
                                       name="title" 
                                       id="title" 
                                       class="form-control @error('title') is-invalid @enderror" 
                                       value="{{ old('title') }}"
                                       required>
                                @error('title')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="type">Banner Type *</label>
                                <select name="type" id="type" class="form-control @error('type') is-invalid @enderror" required>
                                    <option value="">Select Type</option>
                                    <option value="hero_slider" {{ old('type') == 'hero_slider' ? 'selected' : '' }}>Hero Slider</option>
                                    <option value="category_banner" {{ old('type') == 'category_banner' ? 'selected' : '' }}>Category Banner</option>
                                    <option value="popup" {{ old('type') == 'popup' ? 'selected' : '' }}>Popup</option>
                                    <option value="app_notification" {{ old('type') == 'app_notification' ? 'selected' : '' }}>App Notification</option>
                                </select>
                                @error('type')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="subtitle">Subtitle</label>
                        <input type="text" 
                               name="subtitle" 
                               id="subtitle" 
                               class="form-control @error('subtitle') is-invalid @enderror" 
                               value="{{ old('subtitle') }}">
                        @error('subtitle')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="cta_text">CTA Button Text</label>
                                <input type="text" 
                                       name="cta_text" 
                                       id="cta_text" 
                                       class="form-control @error('cta_text') is-invalid @enderror" 
                                       value="{{ old('cta_text') }}">
                                @error('cta_text')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="cta_link">CTA Link (URL)</label>
                                <input type="url" 
                                       name="cta_link" 
                                       id="cta_link" 
                                       class="form-control @error('cta_link') is-invalid @enderror" 
                                       value="{{ old('cta_link') }}">
                                @error('cta_link')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="target_type">Target Type</label>
                                <select name="target_type" id="target_type" class="form-control">
                                    <option value="">All Users</option>
                                    <option value="category" {{ old('target_type') == 'category' ? 'selected' : '' }}>Specific Category</option>
                                    <option value="vendor" {{ old('target_type') == 'vendor' ? 'selected' : '' }}>Specific Vendor</option>
                                </select>
                                @error('target_type')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6" id="target_id_container" style="display: none;">
                            <div class="form-group">
                                <label for="target_id">Select Target</label>
                                <select name="target_id" id="target_id" class="form-control">
                                    <option value="">Select...</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="sort_order">Sort Order</label>
                                <input type="number" 
                                       name="sort_order" 
                                       id="sort_order" 
                                       class="form-control @error('sort_order') is-invalid @enderror" 
                                       value="{{ old('sort_order', 0) }}">
                                @error('sort_order')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                                <small class="text-muted">Lower numbers appear first</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select name="is_active" id="status" class="form-control">
                                    <option value="1" {{ old('is_active', 1) == '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="start_date">Start Date</label>
                                <input type="datetime-local" 
                                       name="start_date" 
                                       id="start_date" 
                                       class="form-control @error('start_date') is-invalid @enderror" 
                                       value="{{ old('start_date') }}">
                                @error('start_date')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                                <small class="text-muted">Leave empty for immediate start</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="end_date">End Date</label>
                                <input type="datetime-local" 
                                       name="end_date" 
                                       id="end_date" 
                                       class="form-control @error('end_date') is-invalid @enderror" 
                                       value="{{ old('end_date') }}">
                                @error('end_date')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                                <small class="text-muted">Leave empty for no expiry</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="image">Banner Image *</label>
                        <input type="file" 
                               name="image" 
                               id="image" 
                               class="form-control-file @error('image') is-invalid @enderror" 
                               accept="image/*"
                               required>
                        @error('image')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="text-muted">Recommended size: 1200x400px. Max 5MB.</small>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.banners.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary float-right">
                        <i class="fas fa-save"></i> Create Banner
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Preview</h3>
            </div>
            <div class="card-body">
                <div id="preview-container" class="text-center">
                    <div id="image-preview" class="bg-light rounded p-5 mb-3">
                        <i class="fas fa-image fa-3x text-muted"></i>
                        <p class="text-muted mt-2">No image selected</p>
                    </div>
                    <div class="text-left mt-3">
                        <h5 id="preview-title" class="mb-1">Banner Title</h5>
                        <p id="preview-subtitle" class="text-muted mb-2">Subtitle here</p>
                        <span id="preview-cta" class="badge badge-primary">No CTA</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tips</h3>
            </div>
            <div class="card-body">
                <ul class="text-muted">
                    <li>Use high-quality images for better visual appeal</li>
                    <li>Keep text concise and readable</li>
                    <li>CTA buttons should be action-oriented (Shop Now, Learn More, etc.)</li>
                    <li>Hero sliders work best with 2-5 banners</li>
                    <li>Set end dates for seasonal promotions</li>
                </ul>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Image preview on file select
    document.getElementById('image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('image-preview');
                const img = document.createElement('img');
                img.id = 'image-preview';
                img.className = 'img-fluid rounded mb-3';
                img.style = 'max-height: 200px; width: 100%; object-fit: cover';
                img.src = e.target.result;
                preview.parentNode.replaceChild(img, preview);
            }
            reader.readAsDataURL(file);
        }
    });

    // Live preview update
    function updatePreview() {
        const title = document.getElementById('title').value;
        const subtitle = document.getElementById('subtitle').value;
        const ctaText = document.getElementById('cta_text').value;
        
        document.getElementById('preview-title').textContent = title || 'Banner Title';
        document.getElementById('preview-subtitle').textContent = subtitle || 'Subtitle here';
        document.getElementById('preview-cta').textContent = ctaText || 'No CTA';
    }

    document.getElementById('title').addEventListener('input', updatePreview);
    document.getElementById('subtitle').addEventListener('input', updatePreview);
    document.getElementById('cta_text').addEventListener('input', updatePreview);

    // Target type change handler
    document.getElementById('target_type').addEventListener('change', function() {
        const container = document.getElementById('target_id_container');
        const targetId = document.getElementById('target_id');
        
        if (this.value) {
            container.style.display = 'block';
            // Load options based on type
            if (this.value === 'category') {
                targetId.innerHTML = '<option value="">Select Category...</option>';
                // You can load categories via AJAX here
            } else if (this.value === 'vendor') {
                targetId.innerHTML = '<option value="">Select Vendor...</option>';
                // You can load vendors via AJAX here
            }
        } else {
            container.style.display = 'none';
            targetId.value = '';
        }
    });
</script>
@endpush
@endsection