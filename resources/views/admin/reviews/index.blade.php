{{-- resources/views/admin/reviews/index.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Reviews')
@section('header', 'Reviews')

@section('content')
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $stats['total'] }}</h3>
                <p>Total Reviews</p>
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
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ $stats['avg_rating'] }}</h3>
                <p>Average Rating</p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col-md-6">
                <form method="GET" class="form-inline">
                    <input type="text" name="search" class="form-control" placeholder="Search reviews..." value="{{ request('search') }}">
                    <select name="rating" class="form-control ml-2">
                        <option value="">All Ratings</option>
                        @for($i = 1; $i <= 5; $i++)
                            <option value="{{ $i }}" {{ request('rating') == $i ? 'selected' : '' }}>{{ $i }} Star</option>
                        @endfor
                    </select>
                    <select name="is_approved" class="form-control ml-2">
                        <option value="">All Status</option>
                        <option value="1" {{ request('is_approved') == '1' ? 'selected' : '' }}>Approved</option>
                        <option value="0" {{ request('is_approved') == '0' ? 'selected' : '' }}>Pending</option>
                    </select>
                    <button type="submit" class="btn btn-primary ml-2">Filter</button>
                    <a href="{{ route('admin.reviews.index') }}" class="btn btn-secondary ml-2">Reset</a>
                </form>
            </div>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover">
            <thead>
                 <tr>
                    <th>Product</th>
                    <th>User</th>
                    <th>Rating</th>
                    <th>Review</th>
                    <th>Images</th>
                    <th>Verified</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reviews as $review)
                <tr>
                    <td>
                        <strong>{{ $review->product->name ?? 'N/A' }}</strong>
                        <br><small class="text-muted">ID: {{ $review->product_id }}</small>
                    </td>
                    <td>
                        {{ $review->user->name ?? 'Anonymous' }}<br>
                        <small class="text-muted">{{ $review->user->email ?? '' }}</small>
                    </td>
                    <td>
                        <div class="text-warning">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= $review->rating)
                                    <i class="fas fa-star"></i>
                                @else
                                    <i class="far fa-star"></i>
                                @endif
                            @endfor
                        </div>
                    </td>
                    <td>
                        {{ \Str::limit($review->review, 100) }}
                        @if(strlen($review->review) > 100)
                            <a href="#" class="text-primary" data-toggle="modal" data-target="#reviewModal{{ $review->id }}">Read more</a>
                        @endif
                    