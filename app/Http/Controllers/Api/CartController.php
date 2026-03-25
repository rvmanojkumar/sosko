<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $cart = $request->user()->cart()->first();
        
        if (!$cart) {
            return response()->json(['items' => [], 'subtotal' => 0]);
        }
        
        $cart->load(['items.productVariant.product', 'items.productVariant.attributeValues']);
        
        $subtotal = $cart->items->sum(function ($item) {
            return $item->quantity * $item->productVariant->current_price;
        });
        
        return response()->json([
            'items' => $cart->items,
            'subtotal' => $subtotal,
            'total_items' => $cart->items->sum('quantity'),
        ]);
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $variant = ProductVariant::findOrFail($request->product_variant_id);
        
        // Check stock
        if ($variant->stock_quantity < $request->quantity) {
            return response()->json([
                'message' => 'Insufficient stock',
                'available' => $variant->stock_quantity
            ], 400);
        }
        
        // Get or create cart
        $cart = $request->user()->cart()->first();
        if (!$cart) {
            $cart = Cart::create(['user_id' => $request->user()->id]);
        }
        
        // Check if item already exists in cart
        $cartItem = $cart->items()->where('product_variant_id', $variant->id)->first();
        
        if ($cartItem) {
            $newQuantity = $cartItem->quantity + $request->quantity;
            
            // Check stock again
            if ($variant->stock_quantity < $newQuantity) {
                return response()->json([
                    'message' => 'Insufficient stock',
                    'available' => $variant->stock_quantity
                ], 400);
            }
            
            $cartItem->update(['quantity' => $newQuantity]);
        } else {
            $cartItem = $cart->items()->create([
                'product_variant_id' => $variant->id,
                'quantity' => $request->quantity,
            ]);
        }
        
        return response()->json([
            'message' => 'Item added to cart',
            'cart_item' => $cartItem
        ]);
    }

    public function update(Request $request, CartItem $cartItem)
    {
        // Check if cart item belongs to user
        if ($cartItem->cart->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);
        
        $variant = $cartItem->productVariant;
        
        // Check stock
        if ($variant->stock_quantity < $request->quantity) {
            return response()->json([
                'message' => 'Insufficient stock',
                'available' => $variant->stock_quantity
            ], 400);
        }
        
        $cartItem->update(['quantity' => $request->quantity]);
        
        return response()->json([
            'message' => 'Cart updated',
            'cart_item' => $cartItem
        ]);
    }

    public function remove(Request $request, CartItem $cartItem)
    {
        // Check if cart item belongs to user
        if ($cartItem->cart->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $cartItem->delete();
        
        return response()->json(['message' => 'Item removed from cart']);
    }

    public function clear(Request $request)
    {
        $cart = $request->user()->cart()->first();
        
        if ($cart) {
            $cart->items()->delete();
        }
        
        return response()->json(['message' => 'Cart cleared']);
    }
}