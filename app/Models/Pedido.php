<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Pedido extends Model
{
    use Auditable;
    use HasFactory;

    public const PRODUCTOS_PIVOT_COLUMNS = ['cantidad', 'precio_unitario', 'subtotal'];

    protected $fillable = [
        'numero_pedido',
        'cliente_id',
        'empleado_id',
        'metodo_pago',
        'fecha',
        'hora',
        'impuesto',
        'total',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'fecha' => 'date',
        'hora' => 'string',
        'impuesto' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * @return array<int, string>
     */
    protected function auditableAttributes(): array
    {
        return [
            'cliente_id',
            'empleado_id',
            'metodo_pago',
            'total',
            'estado',
        ];
    }

    public function productos(): BelongsToMany
    {
        return $this->belongsToMany(Producto::class, 'pedido_producto')
            ->using(PedidoProducto::class)
            ->withPivot(self::PRODUCTOS_PIVOT_COLUMNS)
            ->withTimestamps();
    }

    public function scopeWithDetails(Builder $query): Builder
    {
        return $query->with(['cliente', 'empleado', 'productos']);
    }

    public function loadDetails(): self
    {
        return $this->load(['cliente', 'empleado', 'productos']);
    }

    // Relación con Empleado (User)
    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function stockMovimientos(): HasMany
    {
        return $this->hasMany(StockMovimiento::class);
    }

    public function stockReservations(): HasMany
    {
        return $this->hasMany(StockReservation::class);
    }

    public static function generarNumeroPedido(): string
    {
        do {
            $numero_pedido = sprintf('PED-%s-%s', now()->format('Ym'), Str::upper(Str::random(6)));
        } while (static::where('numero_pedido', $numero_pedido)->exists());

        return $numero_pedido;
    }

    protected static function booted(): void
    {
        static::creating(function (self $pedido): void {
            if (blank($pedido->numero_pedido)) {
                $pedido->numero_pedido = static::generarNumeroPedido();
            }
        });
    }
}
