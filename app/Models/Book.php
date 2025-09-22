<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'book_file',
        'book_url',
        'book_path',
        'is_local',
        'file_size',
        'is_available',
        'price',
        'is_free',
        'original_filename',
        'mime_type'
    ];

    protected $casts = [
        'is_local' => 'boolean',
        'file_size' => 'integer',
        'price' => 'decimal:2',
        'is_available' => 'boolean',
        'is_free' => 'boolean'
    ];

    /**
     * Get the book URL - supports multiple file types
     */
    public function getBookUrlAttribute()
    {
        // Priority 1: Direct book_url (highest priority)
        if (!empty($this->attributes['book_url'])) {
            Log::info('Using direct book_url', [
                'book_id' => $this->id,
                'url' => $this->attributes['book_url'],
                'file_type' => $this->getFileType()
            ]);
            return $this->attributes['book_url'];
        }

        // Priority 2: Cloudinary URL from book_path
        if (!$this->is_local && !empty($this->attributes['book_path'])) {
            $cloudName = env('CLOUDINARY_CLOUD_NAME', 'dscbboswt');
            $url = "https://res.cloudinary.com/{$cloudName}/raw/upload/{$this->attributes['book_path']}";
            
            Log::info('Using Cloudinary URL', [
                'book_id' => $this->id,
                'url' => $url,
                'book_path' => $this->attributes['book_path'],
                'file_type' => $this->getFileType()
            ]);
            return $url;
        }

        // Priority 3: Local file URL
        if ($this->is_local && !empty($this->attributes['book_file'])) {
            $url = asset('storage/' . $this->attributes['book_file']);
            
            Log::info('Using local file URL', [
                'book_id' => $this->id,
                'url' => $url,
                'book_file' => $this->attributes['book_file'],
                'file_type' => $this->getFileType()
            ]);
            return $url;
        }

        // Priority 4: Test files for development (different types)
        $testFiles = [
            'pdf' => 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf',
            'ppt' => 'https://view.officeapps.live.com/op/embed.aspx?src=https://archive.org/download/ExamplePowerPointPresentation/ExamplePowerPointPresentation.pptx',
            'doc' => 'https://docs.google.com/gview?url=https://filesamples.com/samples/document/doc/sample3.doc&embedded=true',
            'default' => 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf'
        ];
        
        $fileType = $this->getFileType();
        $testUrl = $testFiles[$fileType] ?? $testFiles['default'];
        
        Log::warning('No book URL found, using test file', [
            'book_id' => $this->id,
            'book_title' => $this->title,
            'detected_file_type' => $fileType,
            'test_url' => $testUrl
        ]);
        
        return $testUrl;
    }

    /**
     * Get file type based on various indicators
     */
    public function getFileType()
    {
        // Check mime_type first
        if (!empty($this->mime_type)) {
            switch ($this->mime_type) {
                case 'application/pdf':
                    return 'pdf';
                case 'application/vnd.ms-powerpoint':
                case 'application/vnd.openxmlformats-officedocument.presentationml.presentation':
                    return 'ppt';
                case 'application/msword':
                case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
                    return 'doc';
                case 'application/vnd.ms-excel':
                case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
                    return 'excel';
                case 'text/plain':
                    return 'txt';
                default:
                    // Continue to check extension
                    break;
            }
        }

        // Check file extension from various sources
        $sources = [
            $this->original_filename,
            $this->attributes['book_file'] ?? '',
            $this->attributes['book_url'] ?? '',
            $this->attributes['book_path'] ?? ''
        ];

        foreach ($sources as $source) {
            if (!empty($source)) {
                $extension = strtolower(pathinfo($source, PATHINFO_EXTENSION));
                switch ($extension) {
                    case 'pdf':
                        return 'pdf';
                    case 'ppt':
                    case 'pptx':
                        return 'ppt';
                    case 'doc':
                    case 'docx':
                        return 'doc';
                    case 'xls':
                    case 'xlsx':
                        return 'excel';
                    case 'txt':
                        return 'txt';
                    case 'jpg':
                    case 'jpeg':
                    case 'png':
                    case 'gif':
                        return 'image';
                    case 'mp4':
                    case 'webm':
                        return 'video';
                    case 'mp3':
                    case 'wav':
                        return 'audio';
                }
            }
        }

        // Default to pdf if unknown
        return 'pdf';
    }

    /**
     * Get human-readable file type
     */
    public function getFileTypeNameAttribute()
    {
        $types = [
            'pdf' => 'PDF Document',
            'ppt' => 'PowerPoint Presentation',
            'doc' => 'Word Document',
            'excel' => 'Excel Spreadsheet',
            'txt' => 'Text File',
            'image' => 'Image',
            'video' => 'Video',
            'audio' => 'Audio'
        ];

        return $types[$this->getFileType()] ?? 'Document';
    }

    /**
     * Check if file can be viewed in browser
     */
    public function canViewInBrowser()
    {
        $viewableTypes = ['pdf', 'ppt', 'doc', 'excel', 'txt', 'image'];
        return in_array($this->getFileType(), $viewableTypes);
    }

    /**
     * Get appropriate viewer type for frontend
     */
    public function getViewerType()
    {
        $fileType = $this->getFileType();
        
        switch ($fileType) {
            case 'pdf':
                return 'pdf';
            case 'ppt':
                return 'office';
            case 'doc':
                return 'office';
            case 'excel':
                return 'office';
            case 'txt':
                return 'text';
            case 'image':
                return 'image';
            case 'video':
                return 'video';
            case 'audio':
                return 'audio';
            default:
                return 'generic';
        }
    }

    // ... (rest of your existing methods remain the same)
    
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($book) {
            if ($book->is_free) {
                $book->price = 0;
            } elseif ($book->price <= 0) {
                $book->is_free = true;
                $book->price = 0;
            } else {
                $book->is_free = false;
            }
        });
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeFree($query)
    {
        return $query->where('is_free', true);
    }

    public function scopePaid($query)
    {
        return $query->where('is_free', false)->where('price', '>', 0);
    }

    public function isAvailable()
    {
        return $this->is_available === true;
    }

    public function isFree()
    {
        return $this->is_free === true;
    }

    public function isPaid()
    {
        return $this->is_free === false && $this->price > 0;
    }

    public function getFormattedPriceAttribute()
    {
        if ($this->isFree()) {
            return 'Free';
        }
        return 'KSh ' . number_format($this->price, 2);
    }

    public function transactions()
    {
        return $this->hasMany(\App\Models\Transaction::class, 'content_id')
            ->where('content_type', 'book');
    }

    public function purchases()
    {
        return $this->transactions()->where('status', 'paid');
    }
}