<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasUuids;

    protected $fillable = [
        'title', 'subtitle', 'cta_text', 'cta_link', 'image_path', 'image_url',
        'type', 'target_type', 'target_id', 'sort_order', 'is_active',
        'start_date', 'end_date', 'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_active' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];
}