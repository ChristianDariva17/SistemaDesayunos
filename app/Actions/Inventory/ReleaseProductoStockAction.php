<?php

declare(strict_types=1);

namespace App\Actions\Inventory;

use App\Actions\Stock\RegisterStockMovementAction;
use App\Events\StockReleased;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\StockMovimiento;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ReleaseProductoStockAction
{
    public function __construct(
        private readonly RegisterStockMovementAction $registerStockMovement,
    ) {}

    public function handle(
        int $productoId,
        int $cantidad,
        Pedido $pedido,
        ?User $user,
        string $motivo,
        ?string $source,
    ): StockMovimiento {
        $producto = Producto::query()
            ->lockForUpdate()
            ->findOrFail($productoId);

        if ($cantidad <= 0) {
            throw ValidationException::withMessages([
                'productos' => 'Pedido product cantidad must be greater than 0.',
            ]);
        }

        $stockAnterior = (int) $producto->stock;
        $stockNuevo = $stockAnterior + $cantidad;

        $producto->update([
            'stock' => $stockNuevo,
        ]);

        $movimiento = $this->registerStockMovement->handle(
            producto: $producto,
            tipo: StockMovimiento::TIPO_CANCELACION,
            cantidad: $cantidad,
            stockAnterior: $stockAnterior,
            stockNuevo: $stockNuevo,
            pedido: $pedido,
            user: $user,
            motivo: $motivo,
        );

        if ($source !== null) {
            DB::afterCommit(static fn (): mixed => StockReleased::dispatch(
                (int) $producto->getKey(),
                (int) $pedido->getKey(),
                $cantidad,
                $user?->id,
                $source,
            ));
        }

        return $movimiento;
    }
}
