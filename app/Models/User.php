<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'membership_status',
        'membership_expires_at',
        'membership_fee_paid',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'membership_expires_at' => 'datetime',
        'membership_fee_paid' => 'decimal:2'
    ];

    // Membership constants
    const MEMBERSHIP_FEE = 1; // KES 1 for demo
    const MEMBERSHIP_DURATION_MONTHS = 12; // 1 year

    // =============================================================================
    // RELATIONSHIPS
    // =============================================================================
    
    public function membershipPayments()
    {
        return $this->hasMany(MembershipPayment::class);
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

    public function bookPurchases()
    {
        return $this->hasMany(BookPurchase::class);
    }

    public function ownedBooks()
    {
        return $this->belongsToMany(Book::class, 'book_purchases')
            ->wherePivot('status', 'completed');
    }

    // =============================================================================
    // MEMBERSHIP STATUS METHODS
    // =============================================================================
    
    /**
     * Check if user has active membership
     */
    public function hasMembership()
    {
        return $this->membership_expires_at && 
               $this->membership_expires_at->isFuture();
    }

    /**
     * Check if membership is pending payment
     */
    public function membershipPending()
    {
        return $this->membership_status === 'pending';
    }

    /**
     * Check if membership has expired
     */
    public function membershipExpired()
    {
        return $this->membership_status === 'expired' || 
               ($this->membership_expires_at && $this->membership_expires_at->isPast());
    }

    /**
     * Check if membership is suspended
     */
    public function membershipSuspended()
    {
        return $this->membership_status === 'suspended';
    }

    // =============================================================================
    // MEMBERSHIP ACTIONS
    // =============================================================================
    
    /**
     * Activate membership after payment
     */
    public function activateMembership()
    {
        $this->update([
            'membership_status' => 'active',
            'membership_expires_at' => now()->addMonths(self::MEMBERSHIP_DURATION_MONTHS),
            'membership_fee_paid' => self::MEMBERSHIP_FEE
        ]);
        
        \Log::info('Membership activated for user', [
            'user_id' => $this->id,
            'expires_at' => $this->membership_expires_at
        ]);
    }

    /**
     * Expire membership
     */
    public function expireMembership()
    {
        $this->update([
            'membership_status' => 'expired'
        ]);
    }

    /**
     * Suspend membership
     */
    public function suspendMembership($reason = null)
    {
        $this->update([
            'membership_status' => 'suspended'
        ]);
        
        \Log::info('Membership suspended for user', [
            'user_id' => $this->id,
            'reason' => $reason
        ]);
    }

    // =============================================================================
    // MEMBERSHIP ATTRIBUTES & UTILITIES
    // =============================================================================
    
    /**
     * Get membership status label
     */
    public function getMembershipStatusLabelAttribute()
    {
        return match($this->membership_status) {
            'pending' => 'Payment Pending',
            'active' => 'Active',
            'expired' => 'Expired',
            'suspended' => 'Suspended',
            default => 'Unknown'
        };
    }

    /**
     * Get days until membership expires
     */
    public function getDaysUntilExpiryAttribute()
    {
        if (!$this->membership_expires_at) {
            return null;
        }

        return now()->diffInDays($this->membership_expires_at, false);
    }

    /**
     * Get membership progress percentage (for UI)
     */
    public function getMembershipProgressAttribute()
    {
        if (!$this->hasMembership()) {
            return 0;
        }

        $startDate = $this->membership_expires_at->copy()->subMonths(self::MEMBERSHIP_DURATION_MONTHS);
        $totalDays = $startDate->diffInDays($this->membership_expires_at);
        $daysUsed = $startDate->diffInDays(now());

        return min(100, max(0, ($daysUsed / $totalDays) * 100));
    }

    // =============================================================================
    // ACCESS CONTROL METHODS
    // =============================================================================
    
    /**
     * Check if user has access to a specific book
     */
    public function hasAccess($bookId)
    {
        // Must have active membership
        if (!$this->hasMembership()) {
            return false;
        }

        return $this->bookPurchases()
            ->where('book_id', $bookId)
            ->where('status', 'completed')
            ->exists();
    }

    /**
     * Purchase a book (requires active membership)
     */
    public function purchaseBook(Book $book, $paymentMethod = 'mpesa')
    {
        if (!$this->hasMembership()) {
            throw new \Exception('Active membership required to purchase books');
        }

        return $this->bookPurchases()->create([
            'book_id' => $book->id,
            'amount' => $book->price,
            'status' => 'completed',
            'payment_method' => $paymentMethod,
            'transaction_id' => 'BOOK_' . time() . '_' . uniqid()
        ]);
    }

    // =============================================================================
    // MEMBERSHIP PAYMENT HELPERS
    // =============================================================================
    
    /**
     * Get latest membership payment
     */
    public function getLatestMembershipPayment()
    {
        return $this->membershipPayments()->latest()->first();
    }

    /**
     * Get pending membership payment
     */
    public function getPendingMembershipPayment()
    {
        return $this->membershipPayments()->where('status', 'pending')->first();
    }

    /**
     * Has any completed membership payments
     */
    public function hasCompletedMembershipPayments()
    {
        return $this->membershipPayments()->where('status', 'completed')->exists();
    }

    // =============================================================================
    // SCOPES
    // =============================================================================
    
    /**
     * Scope for users with active memberships
     */
    public function scopeWithActiveMembership($query)
    {
        return $query->where('membership_status', 'active')
                    ->where('membership_expires_at', '>', now());
    }

    /**
     * Scope for users with expired memberships
     */
    public function scopeWithExpiredMembership($query)
    {
        return $query->where(function($q) {
            $q->where('membership_status', 'expired')
              ->orWhere('membership_expires_at', '<=', now());
        });
    }

    /**
     * Scope for users with pending memberships
     */
    public function scopeWithPendingMembership($query)
    {
        return $query->where('membership_status', 'pending');
    }


    public function transactions()
    {
        return $this->hasMany(\App\Models\Transaction::class);
    }
    
  
 
    
   



}