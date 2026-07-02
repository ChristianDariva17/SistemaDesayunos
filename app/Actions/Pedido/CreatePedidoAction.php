<?php

declare(strict_types=1);

namespace App\Actions\Pedido;

use App\Actions\Stock\RegisterStockMovementAction;
use App\Models\Pedido;
use App\Models\User;
use Illuminate\Support\Facades\Log;

final class CreatePedidoAction
{
    public function __construct(
        private readonly RegisterStockMovementAction $registerStockMovement,
    ) {}

    /**
     * Create a pedido using the canonical domain flow.
     *
     * @param array{cliente_id:int,empleado_id:int,metodo_pago?:string|null,observaciones?:string|null,productos:array<int,array{id:int,cantidad:int}>} $data
     */
    public function handle(array $data, ?int $userId = null): Pedido
    {
        $pedido = Pedido::crearConProductos(
            $data,
            $this->registerStockMovement,
            $userId === null ? null : User::find($userId),
        );

        Log::info('Pedido creado', [
            'pedido_id' => $pedido->id,
            'numero_pedido' => $pedido->numero_pedido,
            'total' => $pedido->total,
            'productos_count' => count($data['productos']),
            'usuario' => $userId ?? 'Sistema',
        ]);

        return $pedido;
    }
}
