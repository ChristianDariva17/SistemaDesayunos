<?php

declare(strict_types=1);

namespace App\Actions\Stock;

use App\Models\Producto;
use App\Models\StockMovimiento;
use App\Models\User;
use App\Support\InventoryLimits;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class RegisterStockAdjustmentAction
{
    public function __construct(
        private readonly RegisterStockMovementAction $registerStockMovement,
    ) {}

    public function handle(int $productoId, int $stockNuevo, User $user, string $motivo): StockMovimiento
    {
        return DB::transaction(function () use ($productoId, $stockNuevo, $user, $motivo): StockMovimiento {
            $producto = Producto::query()
                ->lockForUpdate()
                ->findOrFail($productoId);

            $stockAnterior = (int) $producto->stock;

            if ($stockNuevo < 0 || $stockNuevo > InventoryLimits::MAX_STOCK_LEVEL) {
                throw ValidationException::withMessages([
                    'stock_nuevo' => 'El nuevo stock está fuera del rango permitido.',
                ]);
            }

            if ($stockAnterior === $stockNuevo) {
                throw ValidationException::withMessages([
                    'stock_nuevo' => 'El nuevo stock debe ser diferente al stock actual.',
                ]);
            }

            $producto->forceFill([
                'stock' => $stockNuevo,
            ])->save();

            return $this->registerStockMovement->handle(
                producto: $producto,
                tipo: StockMovimiento::TIPO_AJUSTE,
                cantidad: abs($stockNuevo - $stockAnterior),
                stockAnterior: $stockAnterior,
                stockNuevo: $stockNuevo,
                pedido: null,
                user: $user,
                motivo: $motivo,
            );
        });
    }
}
