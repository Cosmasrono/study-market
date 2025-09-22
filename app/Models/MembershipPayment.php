<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MembershipPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'payment_method',
        'transaction_id',
        'reference_id',
        'status',
        'payment_gateway',
        'payment_data',
        'phone_number',
        'paid_at',
        'failure_reason'
    ];

    protected $casts = [
        'payment_data' => 'array',
        'paid_at' => 'datetime',
        'amount' => 'decimal:2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark payment as completed
     */
    public function markAsCompleted($referenceId = null)
    {
        $this->update([
            'status' => 'completed',
            'reference_id' => $referenceId,
            'paid_at' => now()
        ]);

        // Activate user membership
        $this->user->activateMembership();
    }

    /**
     * Mark payment as failed
     */
    public function markAsFailed($reason = null)
    {
        $this->update([
            'status' => 'failed',
            'failure_reason' => $reason
        ]);
    }

    /**
     * Generate unique transaction ID
     */
    public static function generateTransactionId()
    {
        return 'MEMBERSHIP_' . time() . '_' . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    }
}