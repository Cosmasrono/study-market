<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Video extends Model
{
    protected $fillable = [
        'title',
        'description',
        'embed_url',
        'video_path',
        'video_url',
        'thumbnail',
        'price',
        'is_free',
        'is_active',
        'duration_minutes',
        'category',
        'is_local',
        'file_size',
        'status'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_free' => 'boolean',
        'is_local' => 'boolean',
        'is_active' => 'boolean',
        'duration_minutes' => 'integer',
        'file_size' => 'integer',
        'status' => 'string'
    ];

    /**
     * Get the proper video URL for playback
     */
    public function getVideoUrlAttribute()
    {
        // If video_url is already set (from Cloudinary), return it
        if (!empty($this->attributes['video_url'])) {
            return $this->attributes['video_url'];
        }

        // If it's a Cloudinary video (has public_id in video_path)
        if (!$this->is_local && !empty($this->attributes['video_path'])) {
            $cloudName = env('CLOUDINARY_CLOUD_NAME');
            if ($cloudName) {
                return "https://res.cloudinary.com/{$cloudName}/video/upload/{$this->attributes['video_path']}";
            }
        }

        // For local videos, use video_path
        if ($this->is_local && !empty($this->attributes['video_path'])) {
            $path = $this->attributes['video_path'];
            
            // Clean up path prefixes
            $path = $this->cleanupPath($path);
            
            return asset('storage/' . $path);
        }

        return null;
    }

    /**
     * Clean up file path by removing common prefixes
     */
    private function cleanupPath($path)
    {
        // Remove 'storage/' prefix if present
        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, 8);
        }
        
        // Remove 'public/' prefix if present
        if (str_starts_with($path, 'public/')) {
            $path = substr($path, 7);
        }
        
        return $path;
    }

    /**
     * Get the embed URL (for backward compatibility)
     */
    public function getEmbedUrlAttribute()
    {
        return $this->getVideoUrlAttribute();
    }

    /**
     * Check if video file exists (for local videos)
     */
    public function videoFileExists()
    {
        if (!$this->is_local || empty($this->attributes['video_path'])) {
            return true; // External/Cloudinary videos don't need file existence check
        }

        $path = $this->cleanupPath($this->attributes['video_path']);
        return Storage::disk('public')->exists($path);
    }

    /**
     * Get the full file path for local videos
     */
    public function getFullFilePath()
    {
        if (!$this->is_local || empty($this->attributes['video_path'])) {
            return null;
        }

        $path = $this->cleanupPath($this->attributes['video_path']);
        return storage_path('app/public/' . $path);
    }

    /**
     * Get video thumbnail URL
     */
    public function getThumbnailUrlAttribute()
    {
        if (!empty($this->thumbnail)) {
            // If it's already a full URL, return as is
            if (filter_var($this->thumbnail, FILTER_VALIDATE_URL)) {
                return $this->thumbnail;
            }
            
            // For local thumbnails
            return asset('storage/' . $this->cleanupPath($this->thumbnail));
        }

        // Generate Cloudinary thumbnail if it's a Cloudinary video
        if (!$this->is_local && !empty($this->attributes['video_path'])) {
            $cloudName = env('CLOUDINARY_CLOUD_NAME');
            if ($cloudName) {
                return "https://res.cloudinary.com/{$cloudName}/video/upload/so_0/{$this->attributes['video_path']}.jpg";
            }
        }

        return null;
    }
}