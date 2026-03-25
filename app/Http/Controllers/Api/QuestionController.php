<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductQuestion;
use App\Models\ProductAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QuestionController extends Controller
{
    /**
     * Get questions for a product
     */
    public function index(Product $product, Request $request)
    {
        $questions = $product->questions()
            ->with(['user', 'answers.user', 'answers.vendor'])
            ->when($request->is_answered !== null, function ($query) use ($request) {
                $query->where('is_answered', $request->is_answered);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);
            
        return response()->json([
            'success' => true,
            'data' => $questions,
            'total_questions' => $product->questions()->count(),
            'answered_count' => $product->questions()->where('is_answered', true)->count(),
            'unanswered_count' => $product->questions()->where('is_answered', false)->count(),
        ]);
    }

    /**
     * Ask a question about a product
     */
    public function store(Request $request, Product $product)
    {
        $request->validate([
            'question' => 'required|string|min:5|max:1000',
        ]);
        
        $user = $request->user();
        
        // Check if user has purchased this product (optional - can ask without purchase)
        $hasPurchased = false;
        if ($user) {
            $hasPurchased = $user->orders()
                ->whereHas('items', function ($query) use ($product) {
                    $query->whereHas('productVariant', function ($q) use ($product) {
                        $q->where('product_id', $product->id);
                    });
                })
                ->where('order_status', 'delivered')
                ->exists();
        }
        
        $question = $product->questions()->create([
            'user_id' => $user ? $user->id : null,
            'question' => $request->question,
            'is_answered' => false,
        ]);
        
        // Send notification to vendor about new question
        if ($product->vendor) {
            // Send push notification to vendor
            // event(new NewQuestionAsked($product->vendor, $question));
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Question submitted successfully',
            'data' => [
                'question' => $question->load('user'),
                'is_verified_purchase' => $hasPurchased,
            ]
        ], 201);
    }

    /**
     * Get a specific question
     */
    public function show(ProductQuestion $question)
    {
        $question->load(['user', 'answers.user', 'answers.vendor']);
        
        return response()->json([
            'success' => true,
            'data' => $question
        ]);
    }

    /**
     * Update a question (only the asker can update)
     */
    public function update(Request $request, ProductQuestion $question)
    {
        $user = $request->user();
        
        // Check if user owns the question
        if ($question->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this question'
            ], 403);
        }
        
        $request->validate([
            'question' => 'required|string|min:5|max:1000',
        ]);
        
        $question->update([
            'question' => $request->question,
            'is_answered' => false, // Reset answered status when question is edited
        ]);
        
        // Delete existing answers when question is edited
        $question->answers()->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Question updated successfully',
            'data' => $question
        ]);
    }

    /**
     * Delete a question
     */
    public function destroy(Request $request, ProductQuestion $question)
    {
        $user = $request->user();
        $product = $question->product;
        
        // Check if user owns the question or is vendor/admin
        if ($question->user_id !== $user->id && 
            $product->vendor_id !== $user->id && 
            !$user->hasRole(['admin', 'super-admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to delete this question'
            ], 403);
        }
        
        // Delete all answers first
        $question->answers()->delete();
        
        $question->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Question deleted successfully'
        ]);
    }

    /**
     * Answer a question
     */
    public function answer(Request $request, ProductQuestion $question)
    {
        $request->validate([
            'answer' => 'required|string|min:2|max:5000',
        ]);
        
        $user = $request->user();
        $product = $question->product;
        
        // Check if user is vendor of this product or admin
        $isVendor = $user->id === $product->vendor_id;
        $isAdmin = $user->hasRole(['admin', 'super-admin']);
        
        if (!$isVendor && !$isAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'Only the vendor or admin can answer questions'
            ], 403);
        }
        
        DB::beginTransaction();
        
        try {
            // Create answer
            $answer = $question->answers()->create([
                'user_id' => $isAdmin ? $user->id : null,
                'vendor_profile_id' => $isVendor ? $user->vendorProfile->id : null,
                'answer' => $request->answer,
            ]);
            
            // Mark question as answered
            $question->markAsAnswered();
            
            // Send notification to customer
            if ($question->user) {
                // Send push notification about answer
                // event(new QuestionAnswered($question->user, $question, $answer));
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Answer submitted successfully',
                'data' => [
                    'answer' => $answer->load(['user', 'vendor']),
                    'question' => $question,
                ]
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to answer question: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit answer: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an answer
     */
    public function updateAnswer(Request $request, ProductAnswer $answer)
    {
        $request->validate([
            'answer' => 'required|string|min:2|max:5000',
        ]);
        
        $user = $request->user();
        
        // Check if user owns the answer or is admin
        $isAnswerOwner = ($answer->user_id && $answer->user_id === $user->id) ||
                         ($answer->vendor_profile_id && $answer->vendor_profile_id === $user->vendorProfile?->id);
        $isAdmin = $user->hasRole(['admin', 'super-admin']);
        
        if (!$isAnswerOwner && !$isAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this answer'
            ], 403);
        }
        
        $answer->update(['answer' => $request->answer]);
        
        return response()->json([
            'success' => true,
            'message' => 'Answer updated successfully',
            'data' => $answer
        ]);
    }

    /**
     * Delete an answer
     */
    public function deleteAnswer(Request $request, ProductAnswer $answer)
    {
        $user = $request->user();
        $question = $answer->question;
        $product = $question->product;
        
        // Check if user owns the answer or is vendor/admin
        $isAnswerOwner = ($answer->user_id && $answer->user_id === $user->id) ||
                         ($answer->vendor_profile_id && $answer->vendor_profile_id === $user->vendorProfile?->id);
        $isVendor = $user->id === $product->vendor_id;
        $isAdmin = $user->hasRole(['admin', 'super-admin']);
        
        if (!$isAnswerOwner && !$isVendor && !$isAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to delete this answer'
            ], 403);
        }
        
        $answer->delete();
        
        // If no more answers, mark question as unanswered
        if ($question->answers()->count() === 0) {
            $question->markAsUnanswered();
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Answer deleted successfully'
        ]);
    }

    /**
     * Get all questions asked by the authenticated user
     */
    public function myQuestions(Request $request)
    {
        $user = $request->user();
        
        $questions = ProductQuestion::where('user_id', $user->id)
            ->with(['product', 'answers', 'product.vendor'])
            ->when($request->is_answered !== null, function ($query) use ($request) {
                $query->where('is_answered', $request->is_answered);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);
            
        return response()->json([
            'success' => true,
            'data' => $questions
        ]);
    }

    /**
     * Get all questions for vendor's products
     */
    public function vendorQuestions(Request $request)
    {
        $user = $request->user();
        
        if (!$user->isVendor()) {
            return response()->json([
                'success' => false,
                'message' => 'Only vendors can access this endpoint'
            ], 403);
        }
        
        $vendorId = $user->id;
        
        $questions = ProductQuestion::whereHas('product', function ($query) use ($vendorId) {
                $query->where('vendor_id', $vendorId);
            })
            ->with(['product', 'user', 'answers'])
            ->when($request->is_answered !== null, function ($query) use ($request) {
                $query->where('is_answered', $request->is_answered);
            })
            ->when($request->product_id, function ($query, $productId) {
                $query->whereHas('product', function ($q) use ($productId) {
                    $q->where('id', $productId);
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);
            
        // Add stats
        $stats = [
            'total_questions' => ProductQuestion::whereHas('product', function ($query) use ($vendorId) {
                $query->where('vendor_id', $vendorId);
            })->count(),
            'answered_questions' => ProductQuestion::whereHas('product', function ($query) use ($vendorId) {
                $query->where('vendor_id', $vendorId);
            })->where('is_answered', true)->count(),
            'unanswered_questions' => ProductQuestion::whereHas('product', function ($query) use ($vendorId) {
                $query->where('vendor_id', $vendorId);
            })->where('is_answered', false)->count(),
        ];
            
        return response()->json([
            'success' => true,
            'data' => $questions,
            'stats' => $stats,
        ]);
    }

    /**
     * Get unanswered questions count for vendor
     */
    public function unansweredCount(Request $request)
    {
        $user = $request->user();
        
        if (!$user->isVendor()) {
            return response()->json([
                'success' => false,
                'message' => 'Only vendors can access this endpoint'
            ], 403);
        }
        
        $count = ProductQuestion::whereHas('product', function ($query) use ($user) {
                $query->where('vendor_id', $user->id);
            })
            ->where('is_answered', false)
            ->count();
            
        return response()->json([
            'success' => true,
            'data' => ['unanswered_count' => $count]
        ]);
    }
}