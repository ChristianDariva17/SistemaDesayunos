<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyCashClosure extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_date',
        'total_orders',
        'total_revenue',
        'settled_order_count',
        'pending_order_count',
        'cancelled_order_count',
        'payment_method_totals',
        'closed_by_user_id',
        'closed_at',
    ];

    protected $casts = [
        'business_date' => 'date',
        'total_orders' => 'integer',
        'total_revenue' => 'decimal:2',
        'settled_order_count' => 'integer',
        'pending_order_count' => 'integer',
        'cancelled_order_count' => 'integer',
        'payment_method_totals' => 'array',
        'closed_by_user_id' => 'integer',
        'closed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by_user_id');
    }

    public function scopeForBusinessDate(Builder $query, string $businessDate): Builder
    {
        return $query->whereDate('business_date', $businessDate);
    }
}
