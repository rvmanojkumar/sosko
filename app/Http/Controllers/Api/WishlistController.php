<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\Cart;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $wishlist = $request->user()->wishlist()
            ->with(['variants', 'images', 'category'])
            ->paginate($request->per_page ?? 20);
            
        return response()->json($wishlist);
    }

    public function add(Request $request, Product $product)
    {
        $user = $request->user();
        
        // Check if already in wishlist
        if ($user->wishlist()->where('product_id', $product->id)->exists()) {
            return response()->json([
                'message' => 'Product already in wishlist'
            ], 400);
        }
        
        $user->wishlist()->attach($product->id);
        
        return response()->json([
            'message' => 'Product added to wishlist'
        ]);
    }

    public function remove(Request $request, Product $product)
    {
        $request->user()->wishlist()->detach($product->id);
        
        return response()->json([
            'message' => 'Product removed from wishlist'
        ]);
    }

    public function moveToCart(Request $request, Product $product)
    {
        $user = $request->user();
        
        // Check if product is in wishlist
        if (!$user->wishlist()->where('product_id', $product->id)->exists()) {
            return response()->json([
                'message' => 'Product not in wishlist'
            ], 404);
        }
        
        // Get default variant
        $variant = $product->default_variant;
        
        if (!$variant) {
            return response()->json([
                'message' => 'Product has no variants'
            ], 400);
        }
        
        // Add to cart
        $cart = $user->cart()->first();
        if (!$cart) {
            $cart = Cart::create(['user_id' => $user->id]);
        }
        
        $cartItem = $cart->items()->where('product_variant_id', $variant->id)->first();
        
        if ($cartItem) {
            $cartItem->increment('quantity');
        } else {
            $cart->items()->create([
                'product_variant_id' => $variant->id,
                'quantity' => 1,
            ]);
        }
        
        // Remove from wishlist
        $user->wishlist()->detach($product->id);
        
        return response()->json([
            'message' => 'Product moved to cart'
        ]);
    }

    public function check(Request $request, Product $product)
    {
        $exists = $request->user()->wishlist()
            ->where('product_id', $product->id)
            ->exists();
            
        return response()->json(['in_wishlist' => $exists]);
    }
}