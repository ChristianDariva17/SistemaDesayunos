<?php

declare(strict_types=1);

namespace App\Actions\Pedido;

use App\Actions\Stock\RegisterStockMovementAction;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\StockMovimiento;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class UpdatePedidoAction
{
    public function __construct(
        private readonly RegisterStockMovementAction $registerStockMovement,
    ) {}

    /**
     * @param array{estado:string,observaciones?:string|null} $data
     */
    public function handle(array $data, Pedido $pedido, ?int $userId = null): Pedido
    {
        return DB::transaction(function () use ($data, $pedido, $userId): Pedido {
            $pedido = Pedido::query()
                ->with('productos')
                ->lockForUpdate()
                ->findOrFail($pedido->getKey());

            $estadoAnterior = $pedido->estado;
            $user = $userId === null ? null : User::find($userId);

            if ($data['estado'] === 'cancelado' && $estadoAnterior !== 'cancelado') {
                $this->restoreStock($pedido, $user);
            }

            if ($estadoAnterior === 'cancelado' && $data['estado'] !== 'cancelado') {
                $this->reserveStockAgain($pedido, $user);
            }

            $pedido->update($data);

            Log::info('Pedido actualizado', [
                'pedido_id' => $pedido->id,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => $data['estado'],
                'usuario' => $userId ?? 'Sistema',
            ]);

            return $pedido->refresh();
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
                motivo: 'Pedido cancellation stock restoration',
            );
        }
    }

    private function reserveStockAgain(Pedido $pedido, ?User $user): void
    {
        foreach ($pedido->productos as $producto) {
            $lockedProducto = Producto::query()
                ->lockForUpdate()
                ->findOrFail($producto->getKey());
            $cantidad = (int) $producto->pivot->cantidad;
            $stockAnterior = (int) $lockedProducto->stock;

            if ($stockAnterior < $cantidad) {
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
        }
    }
}
