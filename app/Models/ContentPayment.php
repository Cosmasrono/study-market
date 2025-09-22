<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'content_type',
        'content_id',
        'amount',
        'phone_number',
        'reference',
        'checkout_request_id',
        'transaction_id',
        'status',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'amount' => 'float'
    ];

    // Relationship with user (optional)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Dynamic relationship with content
    public function content()
    {
        return $this->morphTo();
    }

    // Scope for completed payments
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Generate a unique payment reference
    public static function generateReference()
    {
        return 'PAY-' . strtoupper(uniqid());
    }
}
