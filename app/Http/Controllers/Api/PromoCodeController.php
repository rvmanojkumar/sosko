<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use App\Models\PromoCodeUsage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PromoCodeController extends Controller
{
    /**
     * Get all promo codes (Admin only)
     */
    public function index(Request $request)
    {
        $this->authorizeAdmin($request);
        
        $promoCodes = PromoCode::with(['user', 'vendor'])
            ->when($request->search, function ($query, $search) {
                $query->where('code', 'LIKE', "%{$search}%")
                    ->orWhere('name', 'LIKE', "%{$search}%");
            })
            ->when($request->type, function ($query, $type) {
                $query->where('type', $type);
            })
            ->when($request->is_active !== null, function ($query) use ($request) {
                $query->where('is_active', $request->is_active);
            })
            ->when($request->date_from, function ($query, $dateFrom) {
                $query->whereDate('start_date', '>=', $dateFrom);
            })
            ->when($request->date_to, function ($query, $dateTo) {
                $query->whereDate('end_date', '<=', $dateTo);
            })
            ->orderBy($request->sort_by ?? 'created_at', $request->sort_order ?? 'desc')
            ->paginate($request->per_page ?? 20);
            
        return response()->json([
            'success' => true,
            'data' => $promoCodes
        ]);
    }

    /**
     * Create a new promo code (Admin only)
     */
    public function store(Request $request)
    {
        $this->authorizeAdmin($request);
        
        $validated = $request->validate([
            'code' => 'nullable|string|max:50|unique:promo_codes,code',
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:flat,percentage',
            'value' => 'required|numeric|min:0',
            'min_order_value' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'usage_type' => 'required|in:single,multi',
            'usage_limit' => 'nullable|integer|min:1',
            'per_user_limit' => 'nullable|integer|min:1',
            'user_id' => 'nullable|exists:users,id',
            'vendor_id' => 'nullable|exists:users,id',
            'applicable_products' => 'nullable|array',
            'applicable_categories' => 'nullable|array',
            'excluded_products' => 'nullable|array',
            'excluded_categories' => 'nullable|array',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'boolean',
            'is_first_order_only' => 'boolean',
            'stackable' => 'boolean',
        ]);

        // Generate unique code if not provided
        if (empty($validated['code'])) {
            $validated['code'] = strtoupper(Str::random(8));
            // Ensure code is unique
            while (PromoCode::where('code', $validated['code'])->exists()) {
                $validated['code'] = strtoupper(Str::random(8));
            }
        }
        
        // For single-use codes, set usage_limit to 1
        if ($validated['usage_type'] === 'single') {
            $validated['usage_limit'] = 1;
        }
        
        $promoCode = PromoCode::create($validated);
        
        return response()->json([
            'success' => true,
            'message' => 'Promo code created successfully',
            'data' => $promoCode
        ], 201);
    }

    /**
     * Get a specific promo code
     */
    public function show(Request $request, $id)
    {
        $this->authorizeAdmin($request);
        
        $promoCode = PromoCode::with(['user', 'vendor', 'usages.user', 'usages.order'])
            ->findOrFail($id);
            
        return response()->json([
            'success' => true,
            'data' => $promoCode
        ]);
    }

    /**
     * Update a promo code (Admin only)
     */
    public function update(Request $request, $id)
    {
        $this->authorizeAdmin($request);
        
        $promoCode = PromoCode::findOrFail($id);
        
        $validated = $request->validate([
            'code' => 'string|max:50|unique:promo_codes,code,' . $promoCode->id,
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'type' => 'in:flat,percentage',
            'value' => 'numeric|min:0',
            'min_order_value' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'usage_type' => 'in:single,multi',
            'usage_limit' => 'nullable|integer|min:1',
            'per_user_limit' => 'nullable|integer|min:1',
            'user_id' => 'nullable|exists:users,id',
            'vendor_id' => 'nullable|exists:users,id',
            'applicable_products' => 'nullable|array',
            'applicable_categories' => 'nullable|array',
            'excluded_products' => 'nullable|array',
            'excluded_categories' => 'nullable|array',
            'start_date' => 'date',
            'end_date' => 'date|after:start_date',
            'is_active' => 'boolean',
            'is_first_order_only' => 'boolean',
            'stackable' => 'boolean',
        ]);
        
        // If changing to single-use, ensure usage_limit is 1
        if (isset($validated['usage_type']) && $validated['usage_type'] === 'single') {
            $validated['usage_limit'] = 1;
        }
        
        $promoCode->update($validated);
        
        return response()->json([
            'success' => true,
            'message' => 'Promo code updated successfully',
            'data' => $promoCode
        ]);
    }

    /**
     * Delete a promo code (Admin only)
     */
    public function destroy(Request $request, $id)
    {
        $this->authorizeAdmin($request);
        
        $promoCode = PromoCode::findOrFail($id);
        
        // Check if promo code has been used
        if ($promoCode->usages()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete promo code that has been used'
            ], 400);
        }
        
        $promoCode->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Promo code deleted successfully'
        ]);
    }

    /**
     * Validate a promo code (Public endpoint)
     * Renamed from validate() to validatePromoCode() to avoid conflict
     */
    public function validatePromoCode(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string',
            'subtotal' => 'required|numeric|min:0',
            'user_id' => 'nullable|exists:users,id',
            'vendor_id' => 'nullable|exists:users,id',
            'products' => 'nullable|array',
        ]);

        $userId = $validated['user_id'] ?? $request->user()?->id;
        
        $promoCode = PromoCode::where('code', $validated['code'])->first();
        
        if (!$promoCode) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid promo code'
            ], 404);
        }
        
        // Validate the promo code
        $validation = $promoCode->isValid(
            $userId,
            $validated['subtotal'],
            $validated['products'] ?? []
        );
        
        if (!$validation['valid']) {
            return response()->json([
                'valid' => false,
                'message' => $validation['message']
            ], 400);
        }
        
        // Calculate discount
        $discount = $promoCode->calculateDiscount(
            $validated['subtotal'],
            $validated['products'] ?? []
        );
        
        return response()->json([
            'valid' => true,
            'message' => 'Promo code is valid',
            'data' => [
                'promo_code' => [
                    'id' => $promoCode->id,
                    'code' => $promoCode->code,
                    'name' => $promoCode->name,
                    'type' => $promoCode->type,
                    'value' => $promoCode->value,
                    'min_order_value' => $promoCode->min_order_value,
                    'max_discount_amount' => $promoCode->max_discount_amount,
                ],
                'discount_amount' => $discount,
                'discounted_total' => $validated['subtotal'] - $discount,
            ]
        ]);
    }

    /**
     * Apply a promo code to order
     */
    public function apply(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string',
            'order_id' => 'required|exists:orders,id',
        ]);
        
        $user = $request->user();
        $order = $user->orders()->findOrFail($validated['order_id']);
        
        // Check if order already has a promo code applied
        if ($order->promo_code_id) {
            return response()->json([
                'success' => false,
                'message' => 'A promo code has already been applied to this order'
            ], 400);
        }
        
        $promoCode = PromoCode::where('code', $validated['code'])->first();
        
        if (!$promoCode) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid promo code'
            ], 404);
        }
        
        // Get cart items for validation
        $cartItems = $order->items()->with('productVariant.product')->get();
        $products = $cartItems->map(function($item) {
            return [
                'id' => $item->productVariant->product_id,
                'price' => $item->unit_price,
                'quantity' => $item->quantity,
                'total' => $item->total_price,
            ];
        })->toArray();
        
        // Validate the promo code
        $validation = $promoCode->isValid(
            $user->id,
            $order->subtotal,
            $products
        );
        
        if (!$validation['valid']) {
            return response()->json([
                'success' => false,
                'message' => $validation['message']
            ], 400);
        }
        
        // Calculate discount
        $discount = $promoCode->calculateDiscount($order->subtotal, $products);
        
        DB::beginTransaction();
        
        try {
            // Update order with promo code and discount
            $order->update([
                'promo_code_id' => $promoCode->id,
                'promo_code_discount' => $discount,
                'total_amount' => $order->subtotal + $order->tax_amount + $order->shipping_amount - $discount,
            ]);
            
            // Record usage (will be completed after payment)
            $promoCode->recordUsage(
                $user->id,
                $order->id,
                $discount,
                ['applied_at' => now(), 'status' => 'pending']
            );
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Promo code applied successfully',
                'data' => [
                    'discount_amount' => $discount,
                    'total_amount' => $order->total_amount,
                ]
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error applying promo code: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to apply promo code'
            ], 500);
        }
    }

    /**
     * Get user's available promo codes
     */
    public function getUserPromoCodes(Request $request)
    {
        $user = $request->user();
        
        $promoCodes = PromoCode::valid()
            ->forUser($user->id)
            ->where(function($q) use ($user) {
                $q->whereNull('user_id')
                  ->orWhere('user_id', $user->id);
            })
            ->where(function($q) use ($user) {
                // Check per-user limit
                $q->whereNull('per_user_limit')
                  ->orWhereRaw('per_user_limit > (SELECT COUNT(*) FROM promo_code_usages WHERE promo_code_id = promo_codes.id AND user_id = ?)', [$user->id]);
            })
            ->orderBy('start_date')
            ->get();
        
        // Transform promo codes with additional info
        $promoCodes = $promoCodes->map(function($promoCode) use ($user) {
            return [
                'id' => $promoCode->id,
                'code' => $promoCode->code,
                'name' => $promoCode->name,
                'description' => $promoCode->description,
                'type' => $promoCode->type,
                'value' => $promoCode->value,
                'min_order_value' => $promoCode->min_order_value,
                'max_discount_amount' => $promoCode->max_discount_amount,
                'expires_at' => $promoCode->end_date,
                'days_remaining' => now()->diffInDays($promoCode->end_date, false),
                'is_expiring_soon' => now()->diffInDays($promoCode->end_date) <= 3,
                'used_count' => $promoCode->getUserUsageCount($user->id),
                'remaining_uses' => $promoCode->per_user_limit ? $promoCode->per_user_limit - $promoCode->getUserUsageCount($user->id) : null,
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => $promoCodes
        ]);
    }

    /**
     * Get promo code statistics (Admin only)
     */
    public function getStatistics(Request $request)
    {
        $this->authorizeAdmin($request);
        
        $stats = [
            'total_promo_codes' => PromoCode::count(),
            'active_promo_codes' => PromoCode::active()->count(),
            'expired_promo_codes' => PromoCode::where('end_date', '<', now())->count(),
            'total_usage_count' => PromoCodeUsage::count(),
            'total_discount_given' => PromoCodeUsage::sum('discount_amount'),
            'most_used_promo_codes' => PromoCode::withCount('usages')
                ->orderBy('usages_count', 'desc')
                ->limit(5)
                ->get(),
            'usage_by_type' => PromoCode::select('type', DB::raw('COUNT(*) as count'))
                ->groupBy('type')
                ->get(),
            'usage_by_month' => PromoCodeUsage::select(
                    DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                    DB::raw('COUNT(*) as count'),
                    DB::raw('SUM(discount_amount) as total_discount')
                )
                ->groupBy('month')
                ->orderBy('month', 'desc')
                ->limit(12)
                ->get(),
        ];
        
        return response()->json([
            'success' => true,
            'data' => $stats
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