<?php

declare(strict_types=1);

namespace App\Actions\Stock;

use App\Models\Producto;
use App\Models\StockMovimiento;
use App\Models\User;
use App\Support\InventoryLimits;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class RegisterStockEntryAction
{
    public function __construct(
        private readonly RegisterStockMovementAction $registerStockMovement,
    ) {}

    public function handle(int $productoId, int $cantidad, ?User $user, ?string $motivo = null): StockMovimiento
    {
        return DB::transaction(function () use ($productoId, $cantidad, $user, $motivo): StockMovimiento {
            $producto = Producto::query()
                ->lockForUpdate()
                ->findOrFail($productoId);

            $stockAnterior = (int) $producto->stock;
            $stockNuevo = $stockAnterior + $cantidad;

            if ($cantidad > InventoryLimits::MAX_STOCK_LEVEL || $stockNuevo > InventoryLimits::MAX_STOCK_LEVEL) {
                throw ValidationException::withMessages([
                    'cantidad' => 'La cantidad supera el stock máximo permitido para este producto.',
                ]);
            }

            $producto->forceFill([
                'stock' => $stockNuevo,
            ])->save();

            return $this->registerStockMovement->handle(
                producto: $producto,
                tipo: StockMovimiento::TIPO_ENTRADA,
                cantidad: $cantidad,
                stockAnterior: $stockAnterior,
                stockNuevo: $stockNuevo,
                pedido: null,
                user: $user,
                motivo: $motivo,
            );
        });
    }
}
