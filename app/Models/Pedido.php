<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class Pedido extends Model
{
    use HasFactory;

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

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'pedido_producto')
                    ->withPivot('cantidad', 'precio_unitario', 'subtotal')
                    ->withTimestamps();
    }


    // Relación con Empleado (User)
    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
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

    public static function crearConProductos(array $data): self
    {
        return DB::transaction(function () use ($data): self {
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
                'total' => static::registrarProductos($pedido, $data['productos']),
            ]);

            return $pedido->load('productos');
        });
    }

    public function duplicarConProductos(): self
    {
        return DB::transaction(function (): self {
            $this->loadMissing('productos');

            $nuevoPedido = $this->replicate(['numero_pedido']);
            $nuevoPedido->estado = 'pendiente';
            $nuevoPedido->save();

            $nuevoPedido->update([
                'total' => static::registrarProductos($nuevoPedido, $this->productos),
            ]);

            return $nuevoPedido->load('productos');
        });
    }

    protected static function registrarProductos(self $pedido, iterable $productos): float
    {
        $total = 0;

        foreach ($productos as $productoData) {
            $producto = Producto::findOrFail(
                $productoData instanceof Producto ? $productoData->getKey() : $productoData['id']
            );

            $cantidad = $productoData instanceof Producto
                ? (int) $productoData->pivot->cantidad
                : (int) $productoData['cantidad'];

            $precioUnitario = (float) $producto->precio;
            $subtotal = $cantidad * $precioUnitario;

            $stockActualizado = Producto::query()
                ->whereKey($producto->id)
                ->where('stock', '>=', $cantidad)
                ->decrement('stock', $cantidad);

            if ($stockActualizado === 0) {
                $stockDisponible = (int) Producto::query()
                    ->whereKey($producto->id)
                    ->value('stock');

                throw new Exception("Stock insuficiente para {$producto->nombre}. Disponible: {$stockDisponible}");
            }

            $pedido->productos()->attach($producto->id, [
                'cantidad' => $cantidad,
                'precio_unitario' => $precioUnitario,
                'subtotal' => $subtotal,
            ]);

            $total += $subtotal;
        }

        return $total;
    }
}
