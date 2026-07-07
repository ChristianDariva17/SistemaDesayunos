<?php

declare(strict_types=1);

namespace App\Actions\Pedido;

use App\Actions\Inventory\ReserveProductoStockAction;
use App\Actions\Pedido\Concerns\HandlesPedidoProductStock;
use App\Models\Pedido;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class ReactivatePedidoAction
{
    use HandlesPedidoProductStock;

    public function __construct(
        private readonly ReserveProductoStockAction $reserveProductoStock,
    ) {}

    public function handle(Pedido $pedido, string $estado = 'pendiente', ?string $observaciones = null, ?User $user = null): Pedido
    {
        return DB::transaction(function () use ($pedido, $estado, $observaciones, $user): Pedido {
            $pedido = Pedido::query()
                ->with('productos')
                ->lockForUpdate()
                ->findOrFail($pedido->getKey());

            foreach ($pedido->productos as $producto) {
                $cantidad = (int) $producto->pivot->cantidad;

                $this->reserveProductoStock->handle(
                    productoId: (int) $producto->getKey(),
                    cantidad: $cantidad,
                    pedido: $pedido,
                    user: $user,
                    motivo: 'Pedido reactivation stock reservation',
                    source: 'pedido.reactivate',
                    includeAvailableStockInException: false,
                );
            }

            $pedido->update([
                'estado' => $estado,
                'observaciones' => $observaciones,
            ]);

            return $pedido->refresh();
        });
    }
}
