<?php

declare(strict_types=1);

namespace App\Actions\Stock;

use App\Enums\StockAdjustmentOperation;
use App\Enums\StockMovimientoTipo;
use App\Exceptions\InsufficientStockException;
use App\Models\Producto;
use App\Models\User;
use App\Support\InventoryLimits;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class RegisterStockAdjustmentAction
{
    public function __construct(
        private readonly RegisterStockMovementAction $registerStockMovement,
    ) {}

    public function handle(
        int $productoId,
        StockAdjustmentOperation $operation,
        int $quantity,
        ?User $user,
        ?string $motivo = null,
    ): StockAdjustmentResult {
        return DB::transaction(function () use ($productoId, $operation, $quantity, $user, $motivo): StockAdjustmentResult {
            $producto = Producto::query()
                ->lockForUpdate()
                ->findOrFail($productoId);

            $stockAnterior = (int) $producto->stock;
            $validationField = $operation === StockAdjustmentOperation::Set ? 'stock_nuevo' : 'cantidad';

            if ($quantity < 0 || $quantity > InventoryLimits::MAX_STOCK_LEVEL) {
                throw ValidationException::withMessages([
                    $validationField => 'El stock está fuera del rango permitido.',
                ]);
            }

            if ($stockAnterior < 0 || $stockAnterior > InventoryLimits::MAX_STOCK_LEVEL) {
                throw ValidationException::withMessages([
                    $validationField => 'El stock actual está fuera del rango permitido.',
                ]);
            }

            $stockNuevo = match ($operation) {
                StockAdjustmentOperation::Increment => $quantity > InventoryLimits::MAX_STOCK_LEVEL - $stockAnterior
                    ? InventoryLimits::MAX_STOCK_LEVEL + 1
                    : $stockAnterior + $quantity,
                StockAdjustmentOperation::Decrement => $this->decrement($stockAnterior, $quantity),
                StockAdjustmentOperation::Set => $quantity,
            };

            if ($stockNuevo < 0 || $stockNuevo > InventoryLimits::MAX_STOCK_LEVEL) {
                throw ValidationException::withMessages([
                    $validationField => 'El stock resultante está fuera del rango permitido.',
                ]);
            }

            if ($stockAnterior === $stockNuevo) {
                return new StockAdjustmentResult(
                    producto: $producto,
                    stockAnterior: $stockAnterior,
                    stockNuevo: $stockNuevo,
                    movimiento: null,
                );
            }

            if ($stockNuevo < $producto->activeReservedStock()) {
                throw new InsufficientStockException;
            }

            $producto->forceFill([
                'stock' => $stockNuevo,
            ])->save();

            $movimiento = $this->registerStockMovement->handle(
                producto: $producto,
                tipo: StockMovimientoTipo::Adjustment->value,
                cantidad: abs($stockNuevo - $stockAnterior),
                stockAnterior: $stockAnterior,
                stockNuevo: $stockNuevo,
                pedido: null,
                user: $user,
                motivo: $motivo,
            );

            return new StockAdjustmentResult(
                producto: $producto,
                stockAnterior: $stockAnterior,
                stockNuevo: $stockNuevo,
                movimiento: $movimiento,
            );
        });
    }

    private function decrement(int $stockAnterior, int $quantity): int
    {
        if ($quantity > $stockAnterior) {
            throw new InsufficientStockException;
        }

        return $stockAnterior - $quantity;
    }
}
