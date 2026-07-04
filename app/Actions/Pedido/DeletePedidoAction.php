<?php

declare(strict_types=1);

namespace App\Actions\Pedido;

use App\Actions\Stock\RegisterStockMovementAction;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\StockMovimiento;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

final readonly class DeletePedidoAction
{
    public function __construct(
        private RegisterStockMovementAction $registerStockMovement,
    ) {}

    public function handle(Pedido $pedido, ?User $user = null): string
    {
        return DB::transaction(function () use ($pedido, $user): string {
            $lockedPedido = Pedido::query()
                ->with('productos')
                ->lockForUpdate()
                ->findOrFail($pedido->getKey());

            if ($lockedPedido->estado === 'completado') {
                throw new DomainException('No se puede eliminar un pedido completado');
            }

            $numeroPedido = (string) $lockedPedido->numero_pedido;

            if ($lockedPedido->estado !== 'cancelado') {
                $this->restoreStock($lockedPedido, $user);
            }

            $lockedPedido->delete();

            return $numeroPedido;
        });
    }

    private function restoreStock(Pedido $pedido, ?User $user): void
    {
        foreach ($pedido->productos as $producto) {
            $lockedProducto = Producto::query()
                ->lockForUpdate()
                ->findOrFail($producto->getKey());

            $cantidad = (int) $producto->pivot->cantidad;
            $stockAnterior = (int) $lockedProducto->stock;
            $stockNuevo = $stockAnterior + $cantidad;

            $lockedProducto->update([
                'stock' => $stockNuevo,
            ]);

            $this->registerStockMovement->handle(
                producto: $lockedProducto,
                tipo: StockMovimiento::TIPO_CANCELACION,
                cantidad: $cantidad,
                stockAnterior: $stockAnterior,
                stockNuevo: $stockNuevo,
                pedido: $pedido,
                user: $user,
                motivo: 'Pedido deletion stock restoration',
            );
        }
    }
}
