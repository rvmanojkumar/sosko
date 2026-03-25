<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Http\Resources\SubscriptionPlanResource;
use App\Http\Resources\SubscriptionPlanCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubscriptionPlanController extends Controller
{
    /**
     * Get all subscription plans
     */
    public function index(Request $request)
    {
        $plans = SubscriptionPlan::withCount(['subscriptions', 'activeSubscriptions'])
            ->when($request->is_active !== null, function ($query) use ($request) {
                $query->where('is_active', $request->is_active);
            })
            ->when($request->type === 'free', function ($query) {
                $query->where('price', 0);
            })
            ->when($request->type === 'paid', function ($query) {
                $query->where('price', '>', 0);
            })
            ->orderBy('sort_order')
            ->orderBy('price')
            ->paginate($request->per_page ?? 20);
            
        return response()->json([
            'success' => true,
            'data' => new SubscriptionPlanCollection($plans)
        ]);
    }

    /**
     * Create a new subscription plan
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:subscription_plans,name',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'billing_period' => 'required|in:monthly,yearly',
            'max_products' => 'required|integer|min:-1',
            'max_images_per_product' => 'required|integer|min:1',
            'featured_listing' => 'boolean',
            'priority_support' => 'boolean',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'features' => 'nullable|array',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        $data = $request->all();
        $data['slug'] = Str::slug($request->name);
        
        $plan = SubscriptionPlan::create($data);
        
        return response()->json([
            'success' => true,
            'message' => 'Subscription plan created successfully',
            'data' => new SubscriptionPlanResource($plan)
        ], 201);
    }

    /**
     * Get a specific subscription plan
     */
    public function show(SubscriptionPlan $subscriptionPlan)
    {
        $subscriptionPlan->loadCount(['subscriptions', 'activeSubscriptions']);
        
        return response()->json([
            'success' => true,
            'data' => new SubscriptionPlanResource($subscriptionPlan)
        ]);
    }

    /**
     * Update a subscription plan
     */
    public function update(Request $request, SubscriptionPlan $subscriptionPlan)
    {
        $request->validate([
            'name' => 'string|max:255|unique:subscription_plans,name,' . $subscriptionPlan->id,
            'description' => 'nullable|string',
            'price' => 'numeric|min:0',
            'billing_period' => 'in:monthly,yearly',
            'max_products' => 'integer|min:-1',
            'max_images_per_product' => 'integer|min:1',
            'featured_listing' => 'boolean',
            'priority_support' => 'boolean',
            'commission_rate' => 'numeric|min:0|max:100',
            'features' => 'nullable|array',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        $data = $request->all();
        
        if ($request->has('name') && $request->name !== $subscriptionPlan->name) {
            $data['slug'] = Str::slug($request->name);
        }
        
        $subscriptionPlan->update($data);
        
        return response()->json([
            'success' => true,
            'message' => 'Subscription plan updated successfully',
            'data' => new SubscriptionPlanResource($subscriptionPlan)
        ]);
    }

    /**
     * Delete a subscription plan
     */
    public function destroy(SubscriptionPlan $subscriptionPlan)
    {
        // Check if plan has active subscriptions
        if ($subscriptionPlan->subscriptions()->where('status', 'active')->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete plan that has active subscriptions'
            ], 400);
        }
        
        $subscriptionPlan->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Subscription plan deleted successfully'
        ]);
    }

    /**
     * Get plan statistics
     */
    public function statistics()
    {
        $stats = [
            'total_plans' => SubscriptionPlan::count(),
            'active_plans' => SubscriptionPlan::where('is_active', true)->count(),
            'free_plans' => SubscriptionPlan::where('price', 0)->count(),
            'paid_plans' => SubscriptionPlan::where('price', '>', 0)->count(),
            'plans_by_period' => [
                'monthly' => SubscriptionPlan::where('billing_period', 'monthly')->count(),
                'yearly' => SubscriptionPlan::where('billing_period', 'yearly')->count(),
            ],
            'most_popular_plan' => SubscriptionPlan::withCount('subscriptions')
                ->orderBy('subscriptions_count', 'desc')
                ->first(),
            'total_subscriptions' => \App\Models\VendorSubscription::count(),
            'active_subscriptions' => \App\Models\VendorSubscription::where('status', 'active')
                ->where('end_date', '>=', now())
                ->count(),
        ];
        
        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Reorder plans
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'plans' => 'required|array',
            'plans.*.id' => 'required|exists:subscription_plans,id',
            'plans.*.sort_order' => 'required|integer|min:0',
        ]);
        
        foreach ($request->plans as $planData) {
            SubscriptionPlan::where('id', $planData['id'])->update([
                'sort_order' => $planData['sort_order']
            ]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Plans reordered successfully'
        ]);
    }
}