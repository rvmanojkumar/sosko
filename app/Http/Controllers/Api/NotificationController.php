<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationPreference;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);
            
        return response()->json($notifications);
    }

    public function unreadCount(Request $request)
    {
        $count = $request->user()->unreadNotifications()->count();
        
        return response()->json(['count' => $count]);
    }

    public function markAsRead(Request $request, $id)
    {
        $notification = $request->user()->notifications()->find($id);
        
        if ($notification) {
            $notification->markAsRead();
        }
        
        return response()->json(['message' => 'Notification marked as read']);
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();
        
        return response()->json(['message' => 'All notifications marked as read']);
    }

    public function delete(Request $request, $id)
    {
        $notification = $request->user()->notifications()->find($id);
        
        if ($notification) {
            $notification->delete();
        }
        
        return response()->json(['message' => 'Notification deleted']);
    }

    public function preferences(Request $request)
    {
        $preferences = $request->user()->notificationPreferences;
        
        if (!$preferences) {
            $preferences = NotificationPreference::create([
                'user_id' => $request->user()->id
            ]);
        }
        
        return response()->json($preferences);
    }

    public function updatePreferences(Request $request)
    {
        $request->validate([
            'order_updates' => 'boolean',
            'promo_alerts' => 'boolean',
            'vendor_alerts' => 'boolean',
            'flash_sales' => 'boolean',
            'newsletters' => 'boolean',
        ]);
        
        $preferences = $request->user()->notificationPreferences;
        
        if (!$preferences) {
            $preferences = NotificationPreference::create([
                'user_id' => $request->user()->id
            ]);
        }
        
        $preferences->update($request->all());
        
        return response()->json([
            'message' => 'Preferences updated',
            'preferences' => $preferences
        ]);
    }

    public function test(Request $request)
    {
        $request->validate([
            'type' => 'required|in:order,promo,vendor,flash,system',
            'title' => 'required|string',
            'body' => 'required|string',
        ]);
        
        // Send test notification via FCM
        // This would integrate with your FCM service
        
        return response()->json(['message' => 'Test notification sent']);
    }
}