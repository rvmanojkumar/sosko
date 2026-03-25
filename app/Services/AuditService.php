<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\AuditTrail;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Log;

class AuditService
{
    /**
     * Log custom activity
     */
    public function log($action, $description, $subject = null, $properties = [])
    {
        try {
            $log = [
                'log_name' => $action,
                'description' => $description,
                'properties' => $properties,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'session_id' => Request::session()->getId(),
                'created_at' => now(),
            ];

            if ($subject) {
                $log['subject_type'] = get_class($subject);
                $log['subject_id'] = $subject->getKey();
            }

            if (auth()->check()) {
                $log['causer_type'] = get_class(auth()->user());
                $log['causer_id'] = auth()->id();
            }

            return ActivityLog::create($log);
            
        } catch (\Exception $e) {
            Log::error('Failed to log activity: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Log user login
     */
    public function logLogin($user, $success = true)
    {
        return $this->log(
            'auth',
            $success ? 'User logged in successfully' : 'Failed login attempt',
            $user,
            [
                'success' => $success,
                'email' => $user->email ?? null,
                'method' => 'password',
                'ip' => Request::ip()
            ]
        );
    }

    /**
     * Log user logout
     */
    public function logLogout($user)
    {
        return $this->log(
            'auth',
            'User logged out',
            $user
        );
    }

    /**
     * Log payment activity
     */
    public function logPayment($order, $status, $paymentData = [])
    {
        return $this->log(
            'payment',
            "Payment {$status} for order #{$order->order_number}",
            $order,
            [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'amount' => $order->total_amount,
                'payment_method' => $order->payment_method,
                'status' => $status,
                'payment_data' => $paymentData
            ]
        );
    }

    /**
     * Log order status change
     */
    public function logOrderStatus($order, $oldStatus, $newStatus, $notes = null)
    {
        return $this->log(
            'order',
            "Order status changed from {$oldStatus} to {$newStatus}",
            $order,
            [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'notes' => $notes
            ]
        );
    }

    /**
     * Log vendor activity
     */
    public function logVendorActivity($vendor, $action, $details = [])
    {
        return $this->log(
            'vendor',
            "Vendor {$action}: {$vendor->store_name}",
            $vendor,
            array_merge([
                'vendor_id' => $vendor->id,
                'vendor_name' => $vendor->store_name,
                'action' => $action
            ], $details)
        );
    }

    /**
     * Log admin activity
     */
    public function logAdminActivity($admin, $action, $target = null, $details = [])
    {
        return $this->log(
            'admin',
            "Admin {$action}",
            $target,
            array_merge([
                'admin_id' => $admin->id,
                'admin_name' => $admin->name,
                'action' => $action
            ], $details)
        );
    }

    /**
     * Log system events
     */
    public function logSystem($event, $details = [])
    {
        return $this->log(
            'system',
            "System event: {$event}",
            null,
            $details
        );
    }

    /**
     * Log security events
     */
    public function logSecurity($event, $details = [], $user = null)
    {
        return $this->log(
            'security',
            "Security event: {$event}",
            $user,
            array_merge([
                'event' => $event,
                'ip' => Request::ip()
            ], $details)
        );
    }

    /**
     * Log data export
     */
    public function logExport($user, $type, $filters = [], $recordCount = null)
    {
        return $this->log(
            'export',
            "User exported {$type} data",
            $user,
            [
                'export_type' => $type,
                'filters' => $filters,
                'record_count' => $recordCount,
                'timestamp' => now()
            ]
        );
    }

    /**
     * Clean old audit logs
     */
    public function cleanOldLogs($days = null)
    {
        $days = $days ?? config('audit.retention_days', 90);
        
        if ($days) {
            $cutoffDate = now()->subDays($days);
            
            $deletedActivity = ActivityLog::where('created_at', '<', $cutoffDate)->delete();
            $deletedAudit = AuditTrail::where('created_at', '<', $cutoffDate)->delete();
            
            Log::info("Cleaned up old audit logs", [
                'activity_logs' => $deletedActivity,
                'audit_trails' => $deletedAudit,
                'cutoff_date' => $cutoffDate
            ]);
        }
    }
}