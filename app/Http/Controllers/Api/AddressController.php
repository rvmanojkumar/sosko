<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use App\Http\Resources\UserAddressResource;

class AddressController extends Controller
{
    public function index(Request $request)
{
    $addresses = $request->user()->addresses()
        ->orderBy('is_default', 'desc')
        ->orderBy('created_at', 'desc')
        ->get();
        
    return response()->json([
        'success' => true,
        'data' => UserAddressResource::collection($addresses)
    ]);
}


    public function store(Request $request)
{
    $request->validate([
        'address_line1' => 'required|string',
        'address_line2' => 'nullable|string',
        'city' => 'required|string',
        'state' => 'required|string',
        'country' => 'required|string',
        'postal_code' => 'required|string',
        'phone' => 'nullable|string',
        'address_type' => 'in:home,work,other',
        'is_default' => 'boolean',
        'latitude' => 'nullable|numeric',
        'longitude' => 'nullable|numeric',
    ]);

    // If setting as default, remove default from other addresses
    if ($request->is_default) {
        $request->user()->addresses()->update(['is_default' => false]);
    }

    $address = $request->user()->addresses()->create($request->all());
    
    return response()->json([
        'success' => true,
        'message' => 'Address added successfully',
        'data' => new UserAddressResource($address)
    ], 201);
}

    public function update(Request $request, UserAddress $address)
    {
        // Check if address belongs to user
        if ($address->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'address_line1' => 'string',
            'address_line2' => 'nullable|string',
            'city' => 'string',
            'state' => 'string',
            'country' => 'string',
            'postal_code' => 'string',
            'phone' => 'nullable|string',
            'address_type' => 'in:home,work,other',
            'is_default' => 'boolean',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        // If setting as default, remove default from other addresses
        if ($request->is_default) {
            $request->user()->addresses()->where('id', '!=', $address->id)
                ->update(['is_default' => false]);
        }

        $address->update($request->all());
        
        return response()->json($address);
    }

    public function destroy(Request $request, UserAddress $address)
    {
        // Check if address belongs to user
        if ($address->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Check if it's the default address
        if ($address->is_default) {
            return response()->json([
                'message' => 'Cannot delete default address. Set another address as default first.'
            ], 400);
        }

        $address->delete();
        
        return response()->json(['message' => 'Address deleted successfully']);
    }

    public function setDefault(Request $request, UserAddress $address)
    {
        // Check if address belongs to user
        if ($address->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Remove default from all addresses
        $request->user()->addresses()->update(['is_default' => false]);
        
        // Set this as default
        $address->update(['is_default' => true]);
        
        return response()->json($address);
    }
}