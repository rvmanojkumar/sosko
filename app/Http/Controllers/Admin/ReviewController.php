<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductReview;
use App\Models\VendorReview;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $reviews = ProductReview::with(['product', 'user'])
            ->when($request->type == 'vendor', function($query) {
                return VendorReview::with(['vendorProfile', 'user']);
            }, function($query) {
                return $query;
            })
            ->when($request->rating, function($query, $rating) {
                $query->where('rating', $rating);
            })
            ->when($request->is_approved !== null, function($query) use ($request) {
                $query->where('is_approved', $request->is_approved);
            })
            ->when($request->search, function($query, $search) {
                $query->where('review', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total' => ProductReview::count(),
            'pending' => ProductReview::where('is_approved', false)->count(),
            'approved' => ProductReview::where('is_approved', true)->count(),
            'avg_rating' => round(ProductReview::avg('rating'), 1),
        ];

        return view('admin.reviews.index', compact('reviews', 'stats'));
    }

    public function approve(ProductReview $review)
    {
        $review->update(['is_approved' => true]);
        return redirect()->back()->with('success', 'Review approved successfully.');
    }

    public function reject(ProductReview $review)
    {
        $review->delete();
        return redirect()->back()->with('success', 'Review rejected and deleted.');
    }

    public function destroy(ProductReview $review)
    {
        $review->delete();
        return redirect()->route('admin.reviews.index')->with('success', 'Review deleted successfully.');
    }
}