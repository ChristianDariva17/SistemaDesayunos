<?php

declare(strict_types=1);

namespace App\Models;

use App\Actions\Stock\RegisterStockMovementAction;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class StockReservation extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_RELEASED = 'released';

    public const STATUS_CONSUMED = 'consumed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_RELEASED,
        self::STATUS_CONSUMED,
        self::STATUS_CANCELLED,
    ];

    protected $fillable = [
        'producto_id',
        'pedido_id',
        'cantidad',
        'status',
        'status_changed_at',
    ];

    protected $casts = [
        'producto_id' => 'integer',
        'pedido_id' => 'integer',
        'cantidad' => 'integer',
        'status_changed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public static function reserve(Producto $producto, Pedido $pedido, int $cantidad): self
    {
        return DB::transaction(function () use ($producto, $pedido, $cantidad): self {
            if ($cantidad <= 0) {
                throw new DomainException('Stock reservation cantidad must be greater than 0.');
            }

            $lockedProducto = Producto::query()
                ->lockForUpdate()
                ->findOrFail($producto->getKey());

            $availableStock = $lockedProducto->availableStock();

            if ($availableStock < $cantidad) {
                throw new DomainException("Stock insuficiente para {$lockedProducto->nombre}. Disponible: {$availableStock}");
            }

            return self::create([
                'producto_id' => $lockedProducto->getKey(),
                'pedido_id' => $pedido->getKey(),
                'cantidad' => $cantidad,
                'status' => self::STATUS_ACTIVE,
                'status_changed_at' => now(),
            ]);
        });
    }

    public function release(): self
    {
        return $this->transitionFromActive(self::STATUS_RELEASED);
    }

    public function cancel(): self
    {
        return $this->transitionFromActive(self::STATUS_CANCELLED);
    }

    public function consume(
        ?RegisterStockMovementAction $registerStockMovement = null,
        ?User $user = null,
    ): self {
        return DB::transaction(function () use ($registerStockMovement, $user): self {
            $reservation = self::query()
                ->lockForUpdate()
                ->findOrFail($this->getKey());

            if ($reservation->status !== self::STATUS_ACTIVE) {
                return $reservation->refresh();
            }

            $producto = Producto::query()
                ->lockForUpdate()
                ->findOrFail($reservation->producto_id);

            $stockAnterior = (int) $producto->stock;
            $stockNuevo = $stockAnterior - (int) $reservation->cantidad;

            if ($stockNuevo < 0) {
                throw new DomainException("Stock insuficiente para {$producto->nombre}. Disponible: {$stockAnterior}");
            }

            $producto->update([
                'stock' => $stockNuevo,
            ]);

            $reservation->update([
                'status' => self::STATUS_CONSUMED,
                'status_changed_at' => now(),
            ]);

            $registerStockMovement?->handle(
                producto: $producto,
                tipo: StockMovimiento::TIPO_SALIDA,
                cantidad: (int) $reservation->cantidad,
                stockAnterior: $stockAnterior,
                stockNuevo: $stockNuevo,
                pedido: $reservation->pedido,
                user: $user,
                motivo: 'Stock reservation consumed',
            );

            return $reservation->refresh();
        });
    }

    private function transitionFromActive(string $status): self
    {
        return DB::transaction(function () use ($status): self {
            $reservation = self::query()
                ->lockForUpdate()
                ->findOrFail($this->getKey());

            if ($reservation->status !== self::STATUS_ACTIVE) {
                return $reservation->refresh();
            }

            $reservation->update([
                'status' => $status,
                'status_changed_at' => now(),
            ]);

            return $reservation->refresh();
        });
    }
}
