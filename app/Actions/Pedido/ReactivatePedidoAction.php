<?php

declare(strict_types=1);

namespace App\Actions\Pedido;

use App\Actions\Pedido\Concerns\HandlesPedidoProductStock;
use App\Actions\Stock\RegisterStockMovementAction;
use App\Events\StockReserved;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\StockMovimiento;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

final class ReactivatePedidoAction
{
    use HandlesPedidoProductStock;

    public function __construct(
        private readonly RegisterStockMovementAction $registerStockMovement,
    ) {}

    public function handle(Pedido $pedido, string $estado = 'pendiente', ?string $observaciones = null, ?User $user = null): Pedido
    {
        return DB::transaction(function () use ($pedido, $estado, $observaciones, $user): Pedido {
            $pedido = Pedido::query()
                ->with('productos')
                ->lockForUpdate()
                ->findOrFail($pedido->getKey());

            foreach ($pedido->productos as $producto) {
                $lockedProducto = Producto::query()
                    ->lockForUpdate()
                    ->findOrFail($producto->getKey());
                $cantidad = (int) $producto->pivot->cantidad;
                $stockAnterior = (int) $lockedProducto->stock;
                $availableStock = $lockedProducto->availableStock();

                if ($availableStock < $cantidad) {
                    throw new Exception("Stock insuficiente para {$producto->nombre}");
                }

                $stockNuevo = $stockAnterior - $cantidad;

                $lockedProducto->update([
                    'stock' => $stockNuevo,
                ]);

                $this->registerStockMovement->handle(
                    producto: $lockedProducto,
                    tipo: StockMovimiento::TIPO_SALIDA,
                    cantidad: $cantidad,
                    stockAnterior: $stockAnterior,
                    stockNuevo: $stockNuevo,
                    pedido: $pedido,
                    user: $user,
                    motivo: 'Pedido reactivation stock reservation',
                );

                DB::afterCommit(static fn (): mixed => StockReserved::dispatch(
                    (int) $lockedProducto->getKey(),
                    (int) $pedido->getKey(),
                    $cantidad,
                    $user?->id,
                    'pedido.reactivate',
                ));
            }

            $pedido->update([
                'estado' => $estado,
                'observaciones' => $observaciones,
            ]);

            return $pedido->refresh();
        });
    }
}
