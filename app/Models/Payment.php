<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'status',
        'payment_method',
        'transaction_id',
        'mpesa_receipt_number',
        'mpesa_response',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'mpesa_response' => 'array',
    ];

    /**
     * Get the user that made the payment
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get book purchases associated with this payment
     */
    public function bookPurchases()
    {
        return $this->hasMany(BookPurchase::class, 'transaction_id', 'transaction_id');
    }

    /**
     * Mark payment as completed
     */
    public function markAsCompleted($receiptNumber = null)
    {
        $this->update([
            'status' => 'completed',
            'mpesa_receipt_number' => $receiptNumber,
            'paid_at' => now(),
        ]);

        // Also mark associated book purchases as completed
        $this->bookPurchases()->update(['status' => 'completed', 'paid_at' => now()]);
    }

    /**
     * Mark payment as failed
     */
    public function markAsFailed()
    {
        $this->update([
            'status' => 'failed',
        ]);

        // Also mark associated book purchases as failed
        $this->bookPurchases()->update(['status' => 'failed']);
    }
}