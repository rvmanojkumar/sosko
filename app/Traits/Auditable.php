<?php

namespace App\Traits;

use App\Models\AuditTrail;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Log;

trait Auditable
{
    /**
     * Boot the auditable trait
     */
    public static function bootAuditable()
    {
        // Only register events if auditing is enabled
        if (config('audit.enabled', true)) {
            static::created(function ($model) {
                $model->logAuditTrail('create', null, $model->getAttributes());
            });

            static::updated(function ($model) {
                $oldData = array_intersect_key($model->getOriginal(), $model->getDirty());
                $newData = $model->getDirty();
                
                if (!empty($newData)) {
                    $model->logAuditTrail('update', $oldData, $newData);
                }
            });

            static::deleted(function ($model) {
                $model->logAuditTrail('delete', $model->getAttributes(), null);
            });

            static::restored(function ($model) {
                $model->logAuditTrail('restore', null, $model->getAttributes());
            });

            static::forceDeleted(function ($model) {
                $model->logAuditTrail('force_delete', $model->getAttributes(), null);
            });
        }
    }

    /**
     * Log audit trail entry
     */
    public function logAuditTrail($action, $oldData = null, $newData = null)
    {
        try {
            // Skip if audit is disabled
            if (!config('audit.enabled', true)) {
                return;
            }
            
            // Skip if we're in console and not logging console events
            if (app()->runningInConsole() && !config('audit.log_console_events', false)) {
                return;
            }
            
            // Filter sensitive data
            $oldData = $this->filterSensitiveData($oldData);
            $newData = $this->filterSensitiveData($newData);
            
            // Get current user
            $user = null;
            $userId = null;
            $userType = null;
            
            if (auth()->check()) {
                $user = auth()->user();
                $userId = $user->id;
                $userType = get_class($user);
            }
            
            // Create audit trail record
            AuditTrail::create([
                'table_name' => $this->getTable(),
                'record_id' => $this->getKey(),
                'action' => $action,
                'old_data' => $oldData ? json_encode($oldData) : null,
                'new_data' => $newData ? json_encode($newData) : null,
                'user_id' => $userId,
                'user_type' => $userType,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'created_at' => now(),
            ]);
            
        } catch (\Exception $e) {
            // Log error but don't break the application
            Log::error('Failed to log audit trail: ' . $e->getMessage(), [
                'model' => get_class($this),
                'action' => $action,
                'record_id' => $this->getKey()
            ]);
        }
    }
    
    /**
     * Filter sensitive data from audit logs
     */
    protected function filterSensitiveData($data)
    {
        if (!is_array($data)) {
            return $data;
        }
        
        $sensitiveFields = config('audit.sensitive_fields', [
            'password', 'token', 'api_key', 'secret', 'credit_card', 
            'bank_account', 'cvv', 'otp', 'remember_token'
        ]);
        
        foreach ($sensitiveFields as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = '********';
            }
        }
        
        return $data;
    }
    
    /**
     * Get audit trails for this model
     */
    public function auditTrails()
    {
        return $this->morphMany(AuditTrail::class, 'subject', 'subject_type', 'subject_id');
    }
}