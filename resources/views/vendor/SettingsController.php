<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\VendorProfile;
use App\Models\NotificationPreference;

class SettingsController extends Controller
{
    /**
     * Display settings page
     */
    public function index()
    {
        $vendor = Auth::user()->vendorProfile;
        $notificationPrefs = Auth::user()->notificationPreferences;

        if (!$notificationPrefs) {
            $notificationPrefs = NotificationPreference::create([
                'user_id' => Auth::id(),
            ]);
        }

        return view('vendor.settings.index', compact('vendor', 'notificationPrefs'));
    }

    /**
     * Update general settings
     */
    public function update(Request $request)
    {
        $vendor = Auth::user()->vendorProfile;

        $request->validate([
            'store_name' => 'sometimes|string|max:255|unique:vendor_profiles,store_name,' . $vendor->id,
            'description' => 'nullable|string',
            'contact_email' => 'sometimes|email|max:255',
            'contact_phone' => 'sometimes|string|max:20',
            'address' => 'sometimes|string',
            'order_processing_time' => 'nullable|integer|min:1|max:30',
            'shipping_policy' => 'nullable|string',
            'return_policy' => 'nullable|string',
        ]);

        $vendor->update($request->only([
            'store_name', 'description', 'contact_email', 'contact_phone', 'address'
        ]));

        // Update settings JSON
        $settings = $vendor->settings ?? [];
        $settings['order_processing_time'] = $request->order_processing_time;
        $settings['shipping_policy'] = $request->shipping_policy;
        $settings['return_policy'] = $request->return_policy;
        
        $vendor->update(['settings' => $settings]);

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }

    /**
     * Update notification preferences
     */
    public function updateNotifications(Request $request)
    {
        $prefs = Auth::user()->notificationPreferences;

        $request->validate([
            'order_updates' => 'boolean',
            'promo_alerts' => 'boolean',
            'vendor_alerts' => 'boolean',
            'flash_sales' => 'boolean',
            'newsletters' => 'boolean',
        ]);

        $prefs->update($request->all());

        return redirect()->back()->with('success', 'Notification preferences updated.');
    }

    /**
     * Update payment settings
     */
    public function updatePaymentSettings(Request $request)
    {
        $vendor = Auth::user()->vendorProfile;

        $request->validate([
            'auto_payout_enabled' => 'boolean',
            'payout_threshold' => 'nullable|numeric|min:100',
            'payout_frequency' => 'nullable|in:daily,weekly,monthly',
        ]);

        $settings = $vendor->settings ?? [];
        $settings['auto_payout_enabled'] = $request->auto_payout_enabled ?? false;
        $settings['payout_threshold'] = $request->payout_threshold ?? 1000;
        $settings['payout_frequency'] = $request->payout_frequency ?? 'weekly';
        
        $vendor->update(['settings' => $settings]);

        return redirect()->back()->with('success', 'Payment settings updated.');
    }

    /**
     * Update shipping settings
     */
    public function updateShippingSettings(Request $request)
    {
        $vendor = Auth::user()->vendorProfile;

        $request->validate([
            'default_shipping_method' => 'nullable|string',
            'shipping_charge' => 'nullable|numeric|min:0',
            'free_shipping_threshold' => 'nullable|numeric|min:0',
        ]);

        $settings = $vendor->settings ?? [];
        $settings['default_shipping_method'] = $request->default_shipping_method;
        $settings['shipping_charge'] = $request->shipping_charge;
        $settings['free_shipping_threshold'] = $request->free_shipping_threshold;
        
        $vendor->update(['settings' => $settings]);

        return redirect()->back()->with('success', 'Shipping settings updated.');
    }

    /**
     * Get settings
     */
    public function getSettings()
    {
        $vendor = Auth::user()->vendorProfile;
        
        return response()->json([
            'settings' => $vendor->settings,
            'notification_preferences' => Auth::user()->notificationPreferences,
        ]);
    }
}