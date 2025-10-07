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
        'phone_number',
        'reference',
        'transaction_reference',
        'payhero_reference',
        'customer_name',
        'status',
        'payment_method',
        'subscription_duration', // Will store: '3_months', '6_months', '9_months', '1_year'
        'is_renewal',
        'paid_at',
        'mpesa_receipt_number',
        'failure_reason'
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'is_renewal' => 'boolean',
        'amount' => 'decimal:2'
    ];

    /**
     * Relationship: User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if payment is completed
     */
    public function isCompleted()
    {
        return $this->status === 'completed' || $this->status === 'paid';
    }

    /**
     * Check if payment is pending
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if payment failed
     */
    public function isFailed()
    {
        return $this->status === 'failed';
    }

    /**
     * Get subscription duration in months
     */
    public function getSubscriptionMonthsAttribute()
    {
        $durations = [
            '3_months' => 3,
            '6_months' => 6,
            '9_months' => 9,
            '1_year' => 12
        ];

        return $durations[$this->subscription_duration] ?? 12;
    }
}