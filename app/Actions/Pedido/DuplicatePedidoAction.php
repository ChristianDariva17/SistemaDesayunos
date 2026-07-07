<?php

declare(strict_types=1);

namespace App\Actions\Pedido;

use App\Actions\Pedido\Concerns\HandlesPedidoProductStock;
use App\Actions\Stock\RegisterStockMovementAction;
use App\Models\Pedido;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class DuplicatePedidoAction
{
    use HandlesPedidoProductStock;

    public function __construct(
        private readonly RegisterStockMovementAction $registerStockMovement,
    ) {}

    public function handle(Pedido $pedido, ?User $user = null): Pedido
    {
        return DB::transaction(function () use ($pedido, $user): Pedido {
            $pedido = Pedido::query()
                ->with('productos')
                ->lockForUpdate()
                ->findOrFail($pedido->getKey());

            $this->validateActiveEntities([
                'cliente_id' => (int) $pedido->cliente_id,
                'empleado_id' => (int) $pedido->empleado_id,
            ]);

            $duplicatedPedido = $pedido->replicate(['numero_pedido']);
            $duplicatedPedido->estado = 'pendiente';
            $duplicatedPedido->save();

            $duplicatedPedido->update([
                'total' => $this->attachProductsAndReserveStock(
                    $duplicatedPedido,
                    $pedido->productos,
                    $this->registerStockMovement,
                    $user,
                ),
            ]);

            return $duplicatedPedido->load('productos');
        });
    }
}
