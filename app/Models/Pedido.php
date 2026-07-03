<?php

declare(strict_types=1);

namespace App\Models;

use App\Actions\Stock\RegisterStockMovementAction;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Pedido extends Model
{
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
        'observaciones'
    ];

    protected $casts = [
        'fecha' => 'date',
        'hora' => 'string',
    ];

    public function productos(): BelongsToMany
    {
        return $this->belongsToMany(Producto::class, 'pedido_producto')
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

    public static function crearConProductos(
        array $data,
        ?RegisterStockMovementAction $registerStockMovement = null,
        ?User $user = null,
    ): self
    {
        $registerStockMovement ??= app(RegisterStockMovementAction::class);

        return DB::transaction(function () use ($data, $registerStockMovement, $user): self {
            $pedido = static::create([
                'cliente_id' => $data['cliente_id'],
                'empleado_id' => $data['empleado_id'],
                'metodo_pago' => $data['metodo_pago'] ?? null,
                'fecha' => now()->toDateString(),
                'hora' => now()->format('H:i:s'),
                'total' => 0,
                'estado' => 'pendiente',
                'observaciones' => $data['observaciones'] ?? null,
            ]);

            $pedido->update([
                'total' => static::registrarProductos($pedido, $data['productos'], $registerStockMovement, $user),
            ]);

            return $pedido->load('productos');
        });
    }

    public function duplicarConProductos(
        ?RegisterStockMovementAction $registerStockMovement = null,
        ?User $user = null,
    ): self
    {
        $registerStockMovement ??= app(RegisterStockMovementAction::class);

        return DB::transaction(function () use ($registerStockMovement, $user): self {
            $this->loadMissing('productos');

            $nuevoPedido = $this->replicate(['numero_pedido']);
            $nuevoPedido->estado = 'pendiente';
            $nuevoPedido->save();

            $nuevoPedido->update([
                'total' => static::registrarProductos($nuevoPedido, $this->productos, $registerStockMovement, $user),
            ]);

            return $nuevoPedido->load('productos');
        });
    }

    protected static function registrarProductos(
        self $pedido,
        iterable $productos,
        ?RegisterStockMovementAction $registerStockMovement = null,
        ?User $user = null,
    ): float
    {
        $total = 0;

        foreach ($productos as $productoData) {
            $productoId = $productoData instanceof Producto ? $productoData->getKey() : $productoData['id'];

            $producto = Producto::query()
                ->lockForUpdate()
                ->findOrFail($productoId);

            $cantidad = $productoData instanceof Producto
                ? (int) $productoData->pivot->cantidad
                : (int) $productoData['cantidad'];

            $precioUnitario = (float) $producto->precio;
            $subtotal = $cantidad * $precioUnitario;

            $stockAnterior = (int) $producto->stock;

            if ($stockAnterior < $cantidad) {
                throw new Exception("Stock insuficiente para {$producto->nombre}. Disponible: {$stockAnterior}");
            }

            $stockNuevo = $stockAnterior - $cantidad;

            $producto->update([
                'stock' => $stockNuevo,
            ]);

            $pedido->productos()->attach($producto->id, [
                'cantidad' => $cantidad,
                'precio_unitario' => $precioUnitario,
                'subtotal' => $subtotal,
            ]);

            $registerStockMovement?->handle(
                producto: $producto,
                tipo: StockMovimiento::TIPO_SALIDA,
                cantidad: $cantidad,
                stockAnterior: $stockAnterior,
                stockNuevo: $stockNuevo,
                pedido: $pedido,
                user: $user,
                motivo: 'Pedido stock reservation',
            );

            $total += $subtotal;
        }

        return $total;
    }
}
