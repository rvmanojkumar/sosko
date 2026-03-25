<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\VendorProfile;
use App\Models\VendorDocument;
use App\Models\VendorBankAccount;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    /**
     * Show vendor profile form
     */
    public function edit()
    {
        $vendor = Auth::user()->vendorProfile;
        
        if (!$vendor) {
            return redirect()->route('vendor.profile.create')
                ->with('error', 'Please create your vendor profile first.');
        }

        $documents = $vendor->documents()->get();
        $bankAccounts = $vendor->bankAccounts()->get();

        return view('vendor.profile.edit', compact('vendor', 'documents', 'bankAccounts'));
    }

    /**
     * Create vendor profile
     */
    public function create()
    {
        if (Auth::user()->vendorProfile) {
            return redirect()->route('vendor.profile.edit')
                ->with('info', 'You already have a vendor profile.');
        }

        return view('vendor.profile.create');
    }

    /**
     * Store vendor profile
     */
    public function store(Request $request)
    {
        $request->validate([
            'store_name' => 'required|string|max:255|unique:vendor_profiles',
            'description' => 'nullable|string',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'required|string|max:20',
            'address' => 'required|string',
            'logo' => 'nullable|image|max:2048',
            'banner' => 'nullable|image|max:5120',
            'gst_number' => 'nullable|string|max:15',
            'pan_number' => 'nullable|string|max:10',
        ]);

        $data = $request->except(['logo', 'banner', 'gst_number', 'pan_number']);
        $data['user_id'] = Auth::id();
        $data['store_slug'] = Str::slug($request->store_name) . '-' . Str::random(6);
        $data['status'] = 'pending';

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('vendors/logos', 'public');
            $data['logo'] = $path;
        }

        // Handle banner upload
        if ($request->hasFile('banner')) {
            $path = $request->file('banner')->store('vendors/banners', 'public');
            $data['banner'] = $path;
        }

        $vendor = VendorProfile::create($data);

        // Add documents
        if ($request->gst_number) {
            VendorDocument::create([
                'vendor_profile_id' => $vendor->id,
                'document_type' => 'gst',
                'document_number' => $request->gst_number,
                'status' => 'pending',
            ]);
        }

        if ($request->pan_number) {
            VendorDocument::create([
                'vendor_profile_id' => $vendor->id,
                'document_type' => 'pan',
                'document_number' => $request->pan_number,
                'status' => 'pending',
            ]);
        }

        return redirect()->route('vendor.profile.edit')
            ->with('success', 'Vendor profile created successfully. Please wait for admin approval.');
    }

    /**
     * Update vendor profile
     */
    public function update(Request $request)
    {
        $vendor = Auth::user()->vendorProfile;

        $request->validate([
            'store_name' => 'required|string|max:255|unique:vendor_profiles,store_name,' . $vendor->id,
            'description' => 'nullable|string',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'required|string|max:20',
            'address' => 'required|string',
            'logo' => 'nullable|image|max:2048',
            'banner' => 'nullable|image|max:5120',
        ]);

        $data = $request->except(['logo', 'banner']);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            if ($vendor->logo && Storage::disk('public')->exists($vendor->logo)) {
                Storage::disk('public')->delete($vendor->logo);
            }
            $path = $request->file('logo')->store('vendors/logos', 'public');
            $data['logo'] = $path;
        }

        // Handle banner upload
        if ($request->hasFile('banner')) {
            if ($vendor->banner && Storage::disk('public')->exists($vendor->banner)) {
                Storage::disk('public')->delete($vendor->banner);
            }
            $path = $request->file('banner')->store('vendors/banners', 'public');
            $data['banner'] = $path;
        }

        // Update slug if store name changed
        if ($request->store_name != $vendor->store_name) {
            $data['store_slug'] = Str::slug($request->store_name) . '-' . Str::random(6);
        }

        $vendor->update($data);

        return redirect()->back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Upload logo
     */
    public function uploadLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|max:2048',
        ]);

        $vendor = Auth::user()->vendorProfile;

        if ($vendor->logo && Storage::disk('public')->exists($vendor->logo)) {
            Storage::disk('public')->delete($vendor->logo);
        }

        $path = $request->file('logo')->store('vendors/logos', 'public');
        $vendor->update(['logo' => $path]);

        return response()->json([
            'success' => true,
            'url' => Storage::url($path),
            'message' => 'Logo uploaded successfully.'
        ]);
    }

    /**
     * Add bank account
     */
    public function addBankAccount(Request $request)
    {
        $request->validate([
            'account_holder_name' => 'required|string|max:255',
            'bank_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:50',
            'ifsc_code' => 'required|string|max:20',
            'branch_address' => 'nullable|string',
            'upi_id' => 'nullable|string|max:100',
            'is_default' => 'boolean',
        ]);

        $vendor = Auth::user()->vendorProfile;

        // Check if account exists
        if ($vendor->bankAccounts()->where('account_number', $request->account_number)->exists()) {
            return redirect()->back()->with('error', 'Bank account already exists.');
        }

        // Set as default if requested
        if ($request->is_default) {
            $vendor->bankAccounts()->update(['is_default' => false]);
        }

        $vendor->bankAccounts()->create($request->all());

        return redirect()->back()->with('success', 'Bank account added successfully.');
    }

    /**
     * Update bank account
     */
    public function updateBankAccount(Request $request, $id)
    {
        $vendor = Auth::user()->vendorProfile;
        $bankAccount = $vendor->bankAccounts()->findOrFail($id);

        $request->validate([
            'account_holder_name' => 'required|string|max:255',
            'bank_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:50',
            'ifsc_code' => 'required|string|max:20',
            'branch_address' => 'nullable|string',
            'upi_id' => 'nullable|string|max:100',
            'is_default' => 'boolean',
        ]);

        if ($request->is_default && !$bankAccount->is_default) {
            $vendor->bankAccounts()->update(['is_default' => false]);
        }

        $bankAccount->update($request->all());

        return redirect()->back()->with('success', 'Bank account updated successfully.');
    }

    /**
     * Delete bank account
     */
    public function deleteBankAccount($id)
    {
        $vendor = Auth::user()->vendorProfile;
        $bankAccount = $vendor->bankAccounts()->findOrFail($id);

        // Check if it's the only account
        if ($vendor->bankAccounts()->count() == 1) {
            return redirect()->back()->with('error', 'Cannot delete the only bank account.');
        }

        $bankAccount->delete();

        return redirect()->back()->with('success', 'Bank account deleted successfully.');
    }

    /**
     * Set default bank account
     */
    public function setDefaultBankAccount($id)
    {
        $vendor = Auth::user()->vendorProfile;
        $bankAccount = $vendor->bankAccounts()->findOrFail($id);

        $vendor->bankAccounts()->update(['is_default' => false]);
        $bankAccount->update(['is_default' => true]);

        return redirect()->back()->with('success', 'Default bank account updated.');
    }
}