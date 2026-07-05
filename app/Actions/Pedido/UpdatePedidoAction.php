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
use Illuminate\Validation\ValidationException;

final class UpdatePedidoAction
{
    /**
     * @var array<string, array<int, string>>
     */
    private const ALLOWED_STATUS_TRANSITIONS = [
        'pendiente' => ['procesando', 'cancelado'],
        'procesando' => ['completado', 'cancelado'],
        'completado' => [],
        'cancelado' => ['pendiente'],
    ];

    public function __construct(
        private readonly RegisterStockMovementAction $registerStockMovement,
    ) {}

    /**
     * @param  array{estado:string,observaciones?:string|null}  $data
     */
    public function handle(array $data, Pedido $pedido, ?int $userId = null): Pedido
    {
        return DB::transaction(function () use ($data, $pedido, $userId): Pedido {
            $pedido = Pedido::query()
                ->with('productos')
                ->lockForUpdate()
                ->findOrFail($pedido->getKey());

            $estadoAnterior = $pedido->estado;
            $this->validateStatusTransition($estadoAnterior, $data['estado']);

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

    /**
     * @throws ValidationException
     */
    private function validateStatusTransition(string $currentStatus, string $nextStatus): void
    {
        if ($currentStatus === $nextStatus) {
            return;
        }

        $allowedNextStatuses = self::ALLOWED_STATUS_TRANSITIONS[$currentStatus] ?? [];

        if (in_array($nextStatus, $allowedNextStatuses, true)) {
            return;
        }

        throw ValidationException::withMessages([
            'estado' => "The pedido status cannot transition from {$currentStatus} to {$nextStatus}.",
        ]);
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
        }
    }
}
