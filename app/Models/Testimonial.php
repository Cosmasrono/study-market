<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'content',
        'position',
        'company',
        'rating',
        'status',
        'is_active',
        'admin_id',
        'admin_comment',
        'approved_at',
        'rejected_at'
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_active' => 'boolean',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime'
    ];

    /**
     * Relationship: User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: Admin who approved/rejected
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    /**
     * Scope: Only active testimonials
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                     ->where('status', 'approved');
    }

    /**
     * Scope: Pending testimonials
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Approved testimonials
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Approve testimonial
     */
    public function approve($admin)
    {
        $this->update([
            'status' => 'approved',
            'is_active' => true,
            'admin_id' => $admin->id,
            'approved_at' => now()
        ]);
    }

    /**
     * Reject testimonial
     */
    public function reject($admin, $comment = null)
    {
        $this->update([
            'status' => 'rejected',
            'is_active' => false,
            'admin_id' => $admin->id,
            'admin_comment' => $comment,
            'rejected_at' => now()
        ]);
    }
}