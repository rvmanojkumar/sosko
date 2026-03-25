<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditTrail extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'table_name', 'record_id', 'action', 'old_data', 'new_data',
        'user_id', 'user_type', 'ip_address', 'user_agent', 'created_at'
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
        'created_at' => 'datetime',
    ];

    public $timestamps = false; // We only use created_at

    /**
     * Get the user who performed the action
     */
    public function user()
    {
        return $this->morphTo('user', 'user_type', 'user_id');
    }

    /**
     * Get the subject/model that was changed
     */
    public function subject()
    {
        return $this->morphTo('subject', 'subject_type', 'subject_id');
    }

    /**
     * Scope for specific table
     */
    public function scopeForTable($query, $tableName)
    {
        return $query->where('table_name', $tableName);
    }

    /**
     * Scope for specific record
     */
    public function scopeForRecord($query, $recordId)
    {
        return $query->where('record_id', $recordId);
    }

    /**
     * Scope for specific action
     */
    public function scopeForAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope for date range
     */
    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope for specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get formatted change summary
     */
    public function getChangeSummaryAttribute()
    {
        if ($this->action === 'create') {
            return 'Created new record';
        }
        
        if ($this->action === 'delete') {
            return 'Deleted record';
        }
        
        if ($this->action === 'update' && $this->old_data && $this->new_data) {
            $changes = [];
            foreach ($this->new_data as $field => $newValue) {
                $oldValue = $this->old_data[$field] ?? null;
                if ($oldValue != $newValue) {
                    $changes[] = "{$field}: '{$oldValue}' → '{$newValue}'";
                }
            }
            return implode(', ', $changes);
        }
        
        return $this->action;
    }
}