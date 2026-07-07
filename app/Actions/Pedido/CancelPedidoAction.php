<?php

declare(strict_types=1);

namespace App\Actions\Pedido;

use App\Actions\Inventory\ReleaseProductoStockAction;
use App\Actions\Pedido\Concerns\HandlesPedidoProductStock;
use App\Events\OrderCancelled;
use App\Models\Pedido;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class CancelPedidoAction
{
    use HandlesPedidoProductStock;

    public function __construct(
        private readonly ReleaseProductoStockAction $releaseProductoStock,
    ) {}

    public function handle(Pedido $pedido, ?string $observaciones = null, ?User $user = null): Pedido
    {
        $cancelledPedido = DB::transaction(function () use ($pedido, $observaciones, $user): Pedido {
            $pedido = Pedido::query()
                ->with('productos')
                ->lockForUpdate()
                ->findOrFail($pedido->getKey());

            if ($pedido->estado !== 'cancelado') {
                $this->restorePedidoStock(
                    $pedido,
                    $this->releaseProductoStock,
                    $user,
                    'Pedido cancellation stock restoration',
                    'pedido.cancel',
                );
            }

            $pedido->update([
                'estado' => 'cancelado',
                'observaciones' => $observaciones,
            ]);

            return $pedido->refresh();
        });

        DB::afterCommit(static fn (): mixed => OrderCancelled::dispatch(
            $cancelledPedido->id,
            $user?->id,
            (string) $cancelledPedido->fecha,
        ));

        return $cancelledPedido;
    }
}
