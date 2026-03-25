<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\StorageHelper;

class ReviewMedia extends Model
{
    use HasUuids;

    protected $fillable = [
        'product_review_id',
        'media_path',
        'media_url',
        'media_type'
    ];

    protected $casts = [
        'media_type' => 'string',
    ];

    /**
     * Get the review that owns this media
     */
    public function review()
    {
        return $this->belongsTo(ProductReview::class, 'product_review_id');
    }

    /**
     * Get the media URL
     */
    public function getUrlAttribute()
    {
        if ($this->media_url) {
            return $this->media_url;
        }
        
        return StorageHelper::getFileUrl($this->media_path);
    }

    /**
     * Check if media is an image
     */
    public function isImage()
    {
        return $this->media_type === 'image';
    }

    /**
     * Check if media is a video
     */
    public function isVideo()
    {
        return $this->media_type === 'video';
    }

    /**
     * Scope for images only
     */
    public function scopeImages($query)
    {
        return $query->where('media_type', 'image');
    }

    /**
     * Scope for videos only
     */
    public function scopeVideos($query)
    {
        return $query->where('media_type', 'video');
    }
}