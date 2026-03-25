<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\VendorProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;


class ReviewController extends Controller
{
    public function index(Product $product, Request $request)
    {
        $reviews = $product->reviews()
            ->with(['user', 'media'])
            ->where('is_approved', true)
            ->when($request->rating, function ($query, $rating) {
                $query->where('rating', $rating);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);
            
        return response()->json([
            'reviews' => $reviews,
            'average_rating' => $product->average_rating,
            'total_reviews' => $product->reviews()->where('is_approved', true)->count(),
            'rating_distribution' => $this->getRatingDistribution($product)
        ]);
    }

    public function store(Request $request, Product $product)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000',
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|max:5120',
        ]);

        // Check if user has purchased this product
        $hasPurchased = $request->user()->orders()
            ->whereHas('items', function ($query) use ($product) {
                $query->whereHas('productVariant', function ($q) use ($product) {
                    $q->where('product_id', $product->id);
                });
            })
            ->where('order_status', 'delivered')
            ->exists();

        // Check if already reviewed
        $existingReview = $product->reviews()
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existingReview) {
            return response()->json([
                'message' => 'You have already reviewed this product'
            ], 400);
        }

        DB::beginTransaction();
        
        try {
            $review = ProductReview::create([
                'product_id' => $product->id,
                'user_id' => $request->user()->id,
                'rating' => $request->rating,
                'review' => $request->review,
                'is_verified_purchase' => $hasPurchased,
                'is_approved' => false, // Requires admin approval
            ]);
            
            // Handle images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('reviews/' . $review->id, 's3');
                    $review->media()->create([
                        'media_path' => $path,
                        'media_url' => Storage::disk('s3')->url($path),
                        'media_type' => 'image',
                    ]);
                }
            }
            
            DB::commit();
            
            return response()->json([
                'message' => 'Review submitted successfully and pending approval',
                'review' => $review
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to submit review',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, ProductReview $review)
    {
        // Check if review belongs to user
        if ($review->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $request->validate([
            'rating' => 'integer|min:1|max:5',
            'review' => 'nullable|string|max:1000',
        ]);
        
        $review->update($request->only(['rating', 'review']));
        $review->update(['is_approved' => false]); // Requires re-approval
        
        return response()->json([
            'message' => 'Review updated and pending approval',
            'review' => $review
        ]);
    }

    public function destroy(Request $request, ProductReview $review)
    {
        // Check if review belongs to user or user is admin
        if ($review->user_id !== $request->user()->id && !$request->user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $review->delete();
        
        return response()->json(['message' => 'Review deleted successfully']);
    }

    public function helpful(Request $request, ProductReview $review)
    {
        $user = $request->user();
        
        // Check if user already marked as helpful
        if ($review->helpfulVotes()->where('user_id', $user->id)->exists()) {
            return response()->json([
                'message' => 'You have already marked this review as helpful'
            ], 400);
        }
        
        $review->helpfulVotes()->create(['user_id' => $user->id]);
        $review->increment('helpful_count');
        
        return response()->json([
            'message' => 'Review marked as helpful',
            'helpful_count' => $review->helpful_count
        ]);
    }

    

    protected function getRatingDistribution($product)
    {
        $distribution = [];
        
        for ($i = 1; $i <= 5; $i++) {
            $distribution[$i] = $product->reviews()
                ->where('rating', $i)
                ->where('is_approved', true)
                ->count();
        }
        
        return $distribution;
    }
    public function vendorReviews($vendorId, Request $request)
{
    $vendor = VendorProfile::findOrFail($vendorId);
    
    $reviews = $vendor->approvedReviews()
        ->with(['user'])
        ->when($request->rating, function ($query, $rating) {
            $query->where('rating', $rating);
        })
        ->orderBy('created_at', 'desc')
        ->paginate($request->per_page ?? 20);
        
    return response()->json([
        'success' => true,
        'data' => [
            'reviews' => $reviews,
            'average_rating' => $vendor->average_rating,
            'total_reviews' => $vendor->review_count,
            'rating_distribution' => $vendor->rating_distribution,
        ]
    ]);
}

/**
 * Get product review statistics
 */
public function reviewStats(Product $product)
{
    $reviews = $product->approvedReviews();
    
    return response()->json([
        'success' => true,
        'data' => [
            'average_rating' => $product->average_rating,
            'total_reviews' => $product->review_count,
            'rating_distribution' => $product->rating_distribution,
            'with_images_count' => $reviews->has('media')->count(),
            'verified_purchases_count' => $reviews->where('is_verified_purchase', true)->count(),
            'helpful_count' => $reviews->sum('helpful_count'),
        ]
    ]);
}

/**
 * Get all reviews for admin approval
 */
public function pendingReviews(Request $request)
{
    $this->authorizeAdmin($request);
    
    $reviews = ProductReview::with(['product', 'user'])
        ->where('is_approved', false)
        ->orderBy('created_at', 'desc')
        ->paginate($request->per_page ?? 20);
        
    return response()->json([
        'success' => true,
        'data' => $reviews
    ]);
}

/**
 * Approve a review (admin only)
 */
public function approveReview(Request $request, $id)
{
    $this->authorizeAdmin($request);
    
    $review = ProductReview::findOrFail($id);
    $review->approve();
    
    return response()->json([
        'success' => true,
        'message' => 'Review approved successfully'
    ]);
}

/**
 * Reject a review (admin only)
 */
public function rejectReview(Request $request, $id)
{
    $this->authorizeAdmin($request);
    
    $review = ProductReview::findOrFail($id);
    $review->reject();
    
    return response()->json([
        'success' => true,
        'message' => 'Review rejected and deleted'
    ]);
}

/**
 * Authorize admin access
 */
private function authorizeAdmin(Request $request)
{
    $user = $request->user();
    if (!$user || !$user->hasRole(['admin', 'super-admin'])) {
        abort(403, 'Unauthorized access');
    }
}
}