<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class StorageHelper
{
    /**
     * Upload a file to local storage
     */
    public static function uploadFile(UploadedFile $file, $folder = 'uploads', $disk = 'public')
    {
        try {
            $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();
            $path = $folder . '/' . date('Y') . '/' . date('m') . '/' . $filename;
            
            $uploaded = Storage::disk($disk)->put($path, file_get_contents($file));
            
            if ($uploaded) {
                return [
                    'success' => true,
                    'path' => $path,
                    'url' => Storage::disk($disk)->url($path),
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Failed to upload file',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Upload multiple files
     */
    public static function uploadMultipleFiles(array $files, $folder = 'uploads', $disk = 'public')
    {
        $results = [];
        
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $results[] = self::uploadFile($file, $folder, $disk);
            }
        }
        
        return $results;
    }
    
    /**
     * Delete a file from storage
     */
    public static function deleteFile($path, $disk = 'public')
    {
        try {
            if (!$path) {
                return [
                    'success' => false,
                    'message' => 'No file path provided',
                ];
            }
            
            if (Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);
                return [
                    'success' => true,
                    'message' => 'File deleted successfully',
                ];
            }
            
            return [
                'success' => false,
                'message' => 'File does not exist',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Get file URL
     */
    public static function getFileUrl($path, $disk = 'public')
    {
        try {
            if (!$path) {
                return null;
            }
            
            if (Storage::disk($disk)->exists($path)) {
                return Storage::disk($disk)->url($path);
            }
            
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * Check if file exists
     */
    public static function fileExists($path, $disk = 'public')
    {
        try {
            if (!$path) {
                return false;
            }
            
            return Storage::disk($disk)->exists($path);
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Get file size
     */
    public static function getFileSize($path, $disk = 'public')
    {
        try {
            if (!$path) {
                return 0;
            }
            
            if (Storage::disk($disk)->exists($path)) {
                return Storage::disk($disk)->size($path);
            }
            
            return 0;
        } catch (\Exception $e) {
            return 0;
        }
    }
}