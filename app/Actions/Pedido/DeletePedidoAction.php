<?php

declare(strict_types=1);

namespace App\Actions\Pedido;

use App\Actions\Inventory\ReleaseProductoStockAction;
use App\Models\Pedido;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

final readonly class DeletePedidoAction
{
    public function __construct(
        private ReleaseProductoStockAction $releaseProductoStock,
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
            $cantidad = (int) $producto->pivot->cantidad;

            $this->releaseProductoStock->handle(
                productoId: (int) $producto->getKey(),
                cantidad: $cantidad,
                pedido: $pedido,
                user: $user,
                motivo: 'Pedido deletion stock restoration',
                source: null,
            );
        }
    }
}
