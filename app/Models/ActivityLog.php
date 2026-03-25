<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ActivityLog extends Model
{
    use HasUuids;

    protected $table = 'activity_log';
    
    protected $fillable = [
        'log_name', 'description', 'subject_type', 'subject_id',
        'causer_type', 'causer_id', 'properties', 'ip_address',
        'user_agent', 'session_id'
    ];

    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
    ];

    public $timestamps = false;

    /**
     * Get the subject of the activity
     */
    public function subject()
    {
        return $this->morphTo();
    }

    /**
     * Get the causer of the activity
     */
    public function causer()
    {
        return $this->morphTo();
    }

    /**
     * Scope for specific log type
     */
    public function scopeLogName($query, $logName)
    {
        return $query->where('log_name', $logName);
    }

    /**
     * Scope for specific causer
     */
    public function scopeCauser($query, $causer)
    {
        return $query->where('causer_type', get_class($causer))
                     ->where('causer_id', $causer->id);
    }
}