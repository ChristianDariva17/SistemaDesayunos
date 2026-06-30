<?php

declare(strict_types=1);

namespace App\Actions\Pedido;

use App\Models\Pedido;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class UpdatePedidoAction
{
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

            if ($data['estado'] === 'cancelado' && $estadoAnterior !== 'cancelado') {
                $this->restoreStock($pedido);
            }

            if ($estadoAnterior === 'cancelado' && $data['estado'] !== 'cancelado') {
                $this->reserveStockAgain($pedido);
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

    private function restoreStock(Pedido $pedido): void
    {
        foreach ($pedido->productos as $producto) {
            $producto->increment('stock', $producto->pivot->cantidad);
        }
    }

    private function reserveStockAgain(Pedido $pedido): void
    {
        foreach ($pedido->productos as $producto) {
            $stockActualizado = $producto->newQuery()
                ->whereKey($producto->getKey())
                ->where('stock', '>=', $producto->pivot->cantidad)
                ->decrement('stock', $producto->pivot->cantidad);

            if ($stockActualizado === 0) {
                throw new Exception("Stock insuficiente para {$producto->nombre}");
            }
        }
    }
}
