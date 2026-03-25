<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;

trait HasMedia
{
    /**
     * Get the full URL for a media file
     */
    public function getMediaUrl($field)
    {
        if ($this->$field && Storage::disk('s3')->exists($this->$field)) {
            return Storage::disk('s3')->url($this->$field);
        }
        
        return null;
    }
    
    /**
     * Delete media files
     */
    public function deleteMedia($field)
    {
        if ($this->$field && Storage::disk('s3')->exists($this->$field)) {
            Storage::disk('s3')->delete($this->$field);
        }
    }
    
    /**
     * Delete multiple media files
     */
    public function deleteMultipleMedia(array $fields)
    {
        foreach ($fields as $field) {
            $this->deleteMedia($field);
        }
    }
}