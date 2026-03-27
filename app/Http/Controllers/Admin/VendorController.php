<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VendorProfile;
use App\Models\User;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    /**
     * Display a listing of vendors
     */
    public function index(Request $request)
    {
        $vendors = VendorProfile::with('user')
            ->when($request->search, function($query, $search) {
                $query->where('store_name', 'like', "%{$search}%")
                    ->orWhereHas('user', function($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                    });
            })
            ->when($request->status, function($query, $status) {
                $query->where('status', $status);
            })
            ->orderBy($request->sort ?? 'created_at', $request->order ?? 'desc')
            ->paginate(20);

        // Statistics
        $stats = [
            'total' => VendorProfile::count(),
            'pending' => VendorProfile::where('status', 'pending')->count(),
            'approved' => VendorProfile::where('status', 'approved')->count(),
            'rejected' => VendorProfile::where('status', 'rejected')->count(),
            'suspended' => VendorProfile::where('status', 'suspended')->count(),
        ];

        return view('admin.vendors.index', compact('vendors', 'stats'));
    }

    /**
     * Display the specified vendor
     */
    public function show(VendorProfile $vendor)
    {
        $vendor->load(['user', 'documents', 'subscriptions.plan', 'products']);
        return view('admin.vendors.show', compact('vendor'));
    }

    /**
     * Approve a vendor
     */
    public function approve(Request $request, VendorProfile $vendor)
    {
        $vendor->update(['status' => 'approved']);
        
        return redirect()->back()->with('success', 'Vendor approved successfully.');
    }

    /**
     * Reject a vendor
     */
    public function reject(Request $request, VendorProfile $vendor)
    {
        $request->validate([
            'rejection_reason' => 'required|string'
        ]);

        $vendor->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason
        ]);

        return redirect()->back()->with('success', 'Vendor rejected.');
    }

    /**
     * Suspend a vendor
     */
    public function suspend(Request $request, VendorProfile $vendor)
    {
        $request->validate([
            'suspension_reason' => 'required|string'
        ]);

        $vendor->update([
            'status' => 'suspended',
            'rejection_reason' => $request->suspension_reason
        ]);

        return redirect()->back()->with('success', 'Vendor suspended.');
    }

    /**
     * Activate a suspended vendor
     */
    public function activate(VendorProfile $vendor)
    {
        $vendor->update([
            'status' => 'approved',
            'rejection_reason' => null
        ]);

        return redirect()->back()->with('success', 'Vendor activated successfully.');
    }

    /**
     * Delete a vendor
     */
    public function destroy(VendorProfile $vendor)
    {
        // Delete associated user if needed
        // $vendor->user->delete();
        
        $vendor->delete();
        
        return redirect()->route('admin.vendors.index')->with('success', 'Vendor deleted successfully.');
    }
}