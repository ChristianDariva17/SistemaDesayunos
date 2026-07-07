<?php

declare(strict_types=1);

namespace App\Models;

use App\Events\ProductPriceChanged;
use App\Models\Concerns\Auditable;
use App\Support\InventoryLimits;
use App\Support\MoneyDecimal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Producto extends Model
{
    use Auditable;
    use HasFactory;

    /**
     * ==========================================
     * CONFIGURACIÓN DEL MODELO
     * ==========================================
     */
    protected $fillable = [
        'nombre',
        'descripcion',
        'categoria',
        'precio',
        'codigo_barras',
        'sku',
        'stock',
        'stock_minimo',
        'estado',
        'imagen',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'stock' => 'integer',
        'stock_minimo' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * @return array<int, string>
     */
    protected function auditableAttributes(): array
    {
        return [
            'precio',
            'stock',
            'estado',
        ];
    }

    /**
     * ==========================================
     * RELACIONES
     * ==========================================
     */

    /**
     * Un producto puede estar en muchos pedidos
     * Relación Many-to-Many con Pedido
     */
    public function pedidos(): BelongsToMany
    {
        return $this->belongsToMany(Pedido::class, 'pedido_producto')
            ->using(PedidoProducto::class)
            ->withPivot(Pedido::PRODUCTOS_PIVOT_COLUMNS)
            ->withTimestamps();
    }

    public function stockMovimientos(): HasMany
    {
        return $this->hasMany(StockMovimiento::class);
    }

    public function stockReservations(): HasMany
    {
        return $this->hasMany(StockReservation::class);
    }

    public function activeStockReservations(): HasMany
    {
        return $this->stockReservations()->where('status', StockReservation::STATUS_ACTIVE);
    }

    public function priceHistories(): HasMany
    {
        return $this->hasMany(ProductoPriceHistory::class);
    }

    public function save(array $options = []): bool
    {
        if (! $this->requiresAtomicPriceHistorySave()) {
            return parent::save($options);
        }

        return DB::transaction(fn (): bool => parent::save($options));
    }

    /**
     * ==========================================
     * ACCESSORS (Atributos Calculados)
     * ==========================================
     */

    /**
     * Obtener el precio formateado con símbolo de moneda
     * Uso: $producto->precio_formateado
     */
    public function getPrecioFormateadoAttribute(): string
    {
        return 'S/ '.number_format($this->precio, 2);
    }

    /**
     * Obtener el total vendido de este producto
     * Uso: $producto->total_vendido
     */
    public function getTotalVendidoAttribute(): string
    {
        $total = $this->pedidos()
            ->where('estado', 'completado')
            ->sum(\DB::raw('pedido_producto.cantidad * pedido_producto.precio_unitario'));

        return MoneyDecimal::fromCents(MoneyDecimal::toCents($total));
    }

    /**
     * Obtener la cantidad total vendida (unidades)
     * Uso: $producto->unidades_vendidas
     */
    public function getUnidadesVendidasAttribute(): int
    {
        return $this->pedidos()
            ->where('estado', 'completado')
            ->sum('pedido_producto.cantidad');
    }

    /**
     * Verificar si el producto tiene stock bajo
     * Uso: $producto->tiene_stock_bajo
     */
    public function getTieneStockBajoAttribute(): bool
    {
        return $this->stock <= InventoryLimits::LOW_STOCK_THRESHOLD;
    }

    /**
     * Obtener el estado con badge de color
     * Uso: $producto->estado_badge
     */
    public function getEstadoBadgeAttribute(): string
    {
        return $this->estado === 'activo'
            ? '<span class="badge bg-success">Activo</span>'
            : '<span class="badge bg-danger">Inactivo</span>';
    }

    /**
     * ==========================================
     * SCOPES (Consultas Reutilizables)
     * ==========================================
     */

    /**
     * Scope para filtrar solo productos activos
     * Uso: Producto::activos()->get()
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    /**
     * Scope para filtrar productos con stock bajo
     * Uso: Producto::stockBajo()->get()
     * Uso: Producto::stockBajo(5)->get()
     */
    public function scopeStockBajo($query, int $cantidad = InventoryLimits::LOW_STOCK_THRESHOLD)
    {
        return $query->where('stock', '<=', $cantidad);
    }

    public function scopeStockMinimoBajo($query)
    {
        return $query
            ->where('stock_minimo', '>', 0)
            ->whereColumn('stock', '<=', 'stock_minimo');
    }

    /**
     * Scope para filtrar por categoría
     * Uso: Producto::porCategoria('bebidas')->get()
     */
    public function scopePorCategoria($query, string $categoria)
    {
        return $query->where('categoria', $categoria);
    }

    /**
     * Scope para buscar por nombre o descripción
     * Uso: Producto::buscar('café')->get()
     */
    public function scopeBuscar($query, string $termino)
    {
        return $query->where(function ($q) use ($termino) {
            $q->where('nombre', 'like', "%{$termino}%")
                ->orWhere('descripcion', 'like', "%{$termino}%")
                ->orWhere('codigo_barras', 'like', "%{$termino}%")
                ->orWhere('sku', 'like', "%{$termino}%");
        });
    }

    /**
     * Scope para ordenar por más vendidos
     * Uso: Producto::masVendidos()->get()
     */
    public function scopeMasVendidos($query, int $limit = 10)
    {
        return $query->withCount([
            'pedidos as total_vendido' => function ($query) {
                $query->select(\DB::raw('SUM(pedido_producto.cantidad)'));
            },
        ])
            ->orderBy('total_vendido', 'desc')
            ->limit($limit);
    }

    /**
     * ==========================================
     * MÉTODOS AUXILIARES
     * ==========================================
     */

    /**
     * Verificar si el producto tiene imagen
     */
    public function tieneImagen(): bool
    {
        return ! empty($this->imagen) && \Storage::disk('public')->exists($this->imagen);
    }

    /**
     * Obtener la URL completa de la imagen
     */
    public function getImagenUrl(): string
    {
        if ($this->tieneImagen()) {
            return asset('storage/'.$this->imagen);
        }

        return asset('images/no-image.png');
    }

    /**
     * Reducir stock después de una venta
     */
    public function reducirStock(int $cantidad): bool
    {
        if ($this->stock >= $cantidad) {
            $this->decrement('stock', $cantidad);

            return true;
        }

        return false;
    }

    /**
     * Aumentar stock después de un reabastecimiento
     */
    public function aumentarStock(int $cantidad): void
    {
        $this->increment('stock', $cantidad);
    }

    public function activeReservedStock(): int
    {
        return (int) $this->activeStockReservations()->sum('cantidad');
    }

    public function availableStock(): int
    {
        return max(0, (int) $this->stock - $this->activeReservedStock());
    }

    public function reserveStockForPedido(Pedido $pedido, int $cantidad): StockReservation
    {
        return StockReservation::reserve($this, $pedido, $cantidad);
    }

    protected static function booted(): void
    {
        static::created(function (self $producto): void {
            $producto->recordInitialPriceHistory();
        });

        static::updated(function (self $producto): void {
            if (! $producto->wasChanged('precio')) {
                return;
            }

            $oldPrice = self::normalizePrice($producto->getOriginal('precio'));
            $newPrice = self::normalizePrice($producto->precio);

            if ($oldPrice === $newPrice) {
                return;
            }

            $producto->recordPriceChangeHistory($newPrice);
            DB::afterCommit(static fn (): mixed => ProductPriceChanged::dispatch((int) $producto->getKey(), $oldPrice, $newPrice));
        });
    }

    private function recordInitialPriceHistory(): void
    {
        $this->priceHistories()->create([
            'precio' => self::normalizePrice($this->precio),
            'effective_from' => $this->created_at ?? now(),
            'effective_to' => null,
        ]);
    }

    private function recordPriceChangeHistory(string $newPrice): void
    {
        $effectiveFrom = now();

        $this->priceHistories()
            ->whereNull('effective_to')
            ->update([
                'effective_to' => $effectiveFrom,
            ]);

        $this->priceHistories()->create([
            'precio' => $newPrice,
            'effective_from' => $effectiveFrom,
            'effective_to' => null,
        ]);
    }

    private function requiresAtomicPriceHistorySave(): bool
    {
        return ! $this->exists || $this->isDirty('precio');
    }

    private static function normalizePrice(mixed $price): string
    {
        return MoneyDecimal::fromCents(MoneyDecimal::toCents($price));
    }
}
