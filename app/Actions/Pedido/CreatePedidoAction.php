<?php

declare(strict_types=1);

namespace App\Actions\Pedido;

use App\Actions\Inventory\ReserveProductoStockAction;
use App\Actions\Pedido\Concerns\HandlesPedidoProductStock;
use App\Events\OrderCreated;
use App\Models\Pedido;
use App\Models\User;
use App\Support\BusinessOperationLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

final class CreatePedidoAction
{
    use HandlesPedidoProductStock;

    public function __construct(
        private readonly ReserveProductoStockAction $reserveProductoStock,
    ) {}

    /**
     * Create a pedido using the canonical domain flow.
     *
     * @param  array{cliente_id:int,empleado_id:int,metodo_pago?:string|null,observaciones?:string|null,productos:array<int,array{id:int,cantidad:int}>}  $data
     */
    public function handle(array $data, ?int $userId = null): Pedido
    {
        $user = $userId === null ? null : User::find($userId);

        try {
            $pedido = DB::transaction(function () use ($data, $user): Pedido {
                $this->validateActiveEntities($data);

                $pedido = Pedido::create([
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
                    'total' => $this->attachProductsAndReserveStock(
                        $pedido,
                        $data['productos'],
                        $this->reserveProductoStock,
                        $user,
                    ),
                ]);

                return $pedido->load('productos');
            });
        } catch (Throwable $exception) {
            BusinessOperationLogger::failure('pedido.create', $exception, [
                'model_id' => null,
                'user_id' => $userId,
                'business_date' => now()->toDateString(),
            ]);

            throw $exception;
        }

        Log::info('Pedido creado', [
            'pedido_id' => $pedido->id,
            'numero_pedido' => $pedido->numero_pedido,
            'total' => $pedido->total,
            'productos_count' => count($data['productos']),
            'usuario' => $userId ?? 'Sistema',
        ]);

        DB::afterCommit(static fn (): mixed => OrderCreated::dispatch($pedido->id, $userId, (string) $pedido->fecha));

        return $pedido;
    }
}
