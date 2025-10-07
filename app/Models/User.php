<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Carbon\Carbon;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    const MEMBERSHIP_FEE = 2000; // Base annual fee

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone_number',
        'membership_status',
        'current_subscription_type',
        'subscription_end_date',
        'is_subscription_active',
        'membership_expires_at',
        'membership_fee_paid',
        'email_notifications_enabled',
        'site_notifications_enabled',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'membership_expires_at' => 'datetime',
        'subscription_end_date' => 'datetime',
        'membership_fee_paid' => 'decimal:2',
        'is_subscription_active' => 'boolean',
        'email_notifications_enabled' => 'boolean',
        'site_notifications_enabled' => 'boolean',
    ];

    // Static method to get subscription prices
    public static function getSubscriptionPrices()
    {
        return [
            '3_months' => 1,
            '6_months' => 2,
            '9_months' => 3,
            '1_year' => 4
        ];
    }

    // Relationships
    public function membershipPayments()
    {
        return $this->hasMany(MembershipPayment::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function attempts()
    {
        return $this->hasMany(Attempt::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * User testimonials relationship
     */
    public function testimonials()
    {
        return $this->hasMany(Testimonial::class);
    }
  
    // Membership status methods
    public function hasMembership()
    {
        return $this->is_subscription_active && 
               $this->subscription_end_date && 
               $this->subscription_end_date->isFuture();
    }

    public function membershipPending()
    {
        return $this->membership_status === 'pending';
    }

    public function membershipExpired()
    {
        return $this->membership_status === 'expired' || 
               ($this->subscription_end_date && $this->subscription_end_date->isPast());
    }

    public function membershipActive()
    {
        return $this->membership_status === 'active' && $this->hasMembership();
    }

    // Get days until membership expires
    public function getDaysUntilExpiryAttribute()
    {
        if (!$this->subscription_end_date) {
            return 0;
        }

        $now = Carbon::now();
        $expiry = Carbon::parse($this->subscription_end_date);

        if ($expiry->isPast()) {
            return 0;
        }

        return $now->diffInDays($expiry);
    }

    // Get membership expiry status with color coding
    public function getMembershipStatusWithDaysAttribute()
    {
        $days = $this->days_until_expiry;
        
        if ($days <= 0) {
            return [
                'status' => 'Expired',
                'days' => 0,
                'color' => 'red',
                'message' => 'Membership has expired'
            ];
        } elseif ($days <= 7) {
            return [
                'status' => 'Expiring Soon',
                'days' => $days,
                'color' => 'red',
                'message' => "Expires in {$days} day(s)"
            ];
        } elseif ($days <= 30) {
            return [
                'status' => 'Expiring',
                'days' => $days,
                'color' => 'yellow',
                'message' => "Expires in {$days} day(s)"
            ];
        } else {
            return [
                'status' => 'Active',
                'days' => $days,
                'color' => 'green',
                'message' => "Expires in {$days} day(s)"
            ];
        }
    }

    // Activate membership with subscription
    public function activateMembership($duration = '1_year', $price = null)
    {
        $durations = [
            '3_months' => 3,
            '6_months' => 6,
            '9_months' => 9,
            '1_year' => 12
        ];

        $months = $durations[$duration] ?? 12;
        $subscriptionPrice = $price ?? self::getSubscriptionPrices()[$duration] ?? self::MEMBERSHIP_FEE;

        $this->update([
            'membership_status' => 'active',
            'membership_expires_at' => now()->addMonths($months), // Keep for backward compatibility
            'subscription_end_date' => now()->addMonths($months),
            'current_subscription_type' => $duration,
            'current_subscription_price' => $subscriptionPrice,
            'is_subscription_active' => true,
        ]);

        \Log::info('Membership activated', [
            'user_id' => $this->id,
            'duration' => $duration,
            'price' => $subscriptionPrice,
            'expires_at' => $this->subscription_end_date
        ]);
    }

    // Extend membership
    public function extendMembership($months)
    {
        $currentExpiry = $this->subscription_end_date ?? now();
        
        // If membership is expired, extend from now, otherwise extend from current expiry
        if ($this->membershipExpired()) {
            $newExpiry = now()->addMonths($months);
        } else {
            $newExpiry = $currentExpiry->addMonths($months);
        }

        $this->update([
            'membership_status' => 'active',
            'membership_expires_at' => $newExpiry,
            'subscription_end_date' => $newExpiry,
            'is_subscription_active' => true,
        ]);
    }

    // Get pending membership payment
    public function getPendingMembershipPayment()
    {
        return $this->membershipPayments()
            ->where('status', 'pending')
            ->latest()
            ->first();
    }

    // Check if membership expires within given days
    public function membershipExpiresWithin($days)
    {
        if (!$this->subscription_end_date) {
            return false;
        }

        return $this->subscription_end_date->isAfter(now()) && 
               $this->subscription_end_date->isBefore(now()->addDays($days));
    }

    // Get formatted expiry date
    public function getFormattedExpiryDateAttribute()
    {
        if (!$this->subscription_end_date) {
            return 'No active subscription';
        }

        return $this->subscription_end_date->format('M j, Y \a\t g:i A');
    }

    // Scope for users with expiring memberships
    public function scopeExpiringWithin($query, $days)
    {
        return $query->where('is_subscription_active', true)
            ->where('subscription_end_date', '>', now())
            ->where('subscription_end_date', '<=', now()->addDays($days));
    }

    // Scope for expired memberships
    public function scopeExpired($query)
    {
        return $query->where('subscription_end_date', '<', now())
            ->orWhere('is_subscription_active', false);
    }

    // Scope for active memberships
    public function scopeActive($query)
    {
        return $query->where('is_subscription_active', true)
            ->where('subscription_end_date', '>', now());
    }
}