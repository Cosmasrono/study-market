<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    // Transaction status constants
    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'user_id',
        'checkout_request_id',
        'merchant_request_id',
        'content_type',     // 'book' or 'video'
        'content_id',       // ID of the book or video
        'book_id',          // Keep for backward compatibility
        'phone',
        'amount',
        'status',
        'mpesa_receipt_number',
        'response_data',
        'result_desc',
        'completed_at'
    ];

    protected $casts = [
        'response_data' => 'array',
        'amount' => 'decimal:2',
        'completed_at' => 'datetime'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the associated content (book or video)
     */
    public function content()
    {
        return match($this->content_type) {
            'book' => $this->belongsTo(Book::class, 'content_id'),
            'video' => $this->belongsTo(Video::class, 'content_id'),
            default => null
        };
    }

    /**
     * Get the actual content model instance
     */
    public function getContentAttribute()
    {
        if ($this->content_type === 'book') {
            return Book::find($this->content_id);
        } elseif ($this->content_type === 'video') {
            return Video::find($this->content_id);
        }
        
        // Fallback for old transactions with book_id
        if ($this->book_id) {
            return Book::find($this->book_id);
        }
        
        return null;
    }

    /**
     * Get the book associated with the transaction
     * Kept for backward compatibility
     */
    public function book()
    {
        if ($this->content_type === 'book') {
            return $this->belongsTo(Book::class, 'content_id');
        }
        
        // Fallback for old transactions
        return $this->belongsTo(Book::class, 'book_id');
    }

    /**
     * Get the video associated with the transaction
     */
    public function video()
    {
        if ($this->content_type === 'video') {
            return $this->belongsTo(Video::class, 'content_id');
        }
        
        return null;
    }

    // Scopes
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForContent($query, $type, $id)
    {
        return $query->where('content_type', $type)
                     ->where('content_id', $id);
    }

    public function scopePaid($query)
    {
        return $query->whereIn('status', [self::STATUS_PAID, self::STATUS_COMPLETED]);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    // Status check methods
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isCompleted()
    {
        return in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_PAID]);
    }

    public function isFailed()
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function isCancelled()
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Mark transaction as completed
     */
    public function markAsCompleted($mpesaReceipt = null)
    {
        $this->update([
            'status' => self::STATUS_PAID,
            'mpesa_receipt_number' => $mpesaReceipt,
            'completed_at' => now()
        ]);
    }

    /**
     * Mark transaction as failed
     */
    public function markAsFailed($resultDesc = null)
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'result_desc' => $resultDesc
        ]);
    }

    /**
     * Mark transaction as cancelled
     */
    public function markAsCancelled($reason = null)
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'result_desc' => $reason
        ]);
    }

    /**
     * Check if transaction is recent
     */
    public function isRecent($days = 30)
    {
        return $this->created_at->isAfter(now()->subDays($days));
    }
}