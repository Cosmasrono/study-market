<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FAQ extends Model
{
    // Specify the table name explicitly (optional, but good practice)
    protected $table = 'faqs';

    // Fillable fields
    protected $fillable = [
        'category',
        'question',
        'answer',
        'order',
        'is_active'
    ];

    // Cast fields to appropriate types
    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer'
    ];

    // Scope to get only active FAQs
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope to order FAQs
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }

    // Scope to filter by category
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }
}
