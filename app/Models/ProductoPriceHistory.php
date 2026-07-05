<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductoPriceHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'producto_id',
        'precio',
        'effective_from',
        'effective_to',
    ];

    protected $casts = [
        'producto_id' => 'integer',
        'precio' => 'decimal:2',
        'effective_from' => 'datetime',
        'effective_to' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }
}
