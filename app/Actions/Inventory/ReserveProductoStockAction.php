<?php

declare(strict_types=1);

namespace App\Actions\Inventory;

use App\Actions\Stock\RegisterStockMovementAction;
use App\Events\StockReserved;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\StockMovimiento;
use App\Models\User;
use App\Support\MoneyDecimal;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ReserveProductoStockAction
{
    public function __construct(
        private readonly RegisterStockMovementAction $registerStockMovement,
    ) {}

    /**
     * @return array{producto:Producto,precio_unitario:string,subtotal:string,stock_anterior:int,stock_nuevo:int,movimiento:StockMovimiento}
     */
    public function handle(
        int $productoId,
        int $cantidad,
        Pedido $pedido,
        ?User $user,
        string $motivo,
        string $source,
        bool $includeAvailableStockInException = true,
    ): array {
        $producto = Producto::query()
            ->lockForUpdate()
            ->findOrFail($productoId);

        if ($producto->estado !== 'activo') {
            throw ValidationException::withMessages([
                'productos' => "The producto {$producto->nombre} is not active.",
            ]);
        }

        if ($cantidad <= 0) {
            throw ValidationException::withMessages([
                'productos' => 'Pedido product cantidad must be greater than 0.',
            ]);
        }

        $precioUnitario = (string) $producto->precio;

        if (MoneyDecimal::toCents($precioUnitario) < 0) {
            throw ValidationException::withMessages([
                'productos' => "The producto {$producto->nombre} has an invalid negative price.",
            ]);
        }

        $stockAnterior = (int) $producto->stock;
        $availableStock = $producto->availableStock();

        if ($availableStock < $cantidad) {
            if (! $includeAvailableStockInException) {
                throw new Exception("Stock insuficiente para {$producto->nombre}");
            }

            throw new Exception("Stock insuficiente para {$producto->nombre}. Disponible: {$availableStock}");
        }

        $stockNuevo = $stockAnterior - $cantidad;

        $producto->update([
            'stock' => $stockNuevo,
        ]);

        $movimiento = $this->registerStockMovement->handle(
            producto: $producto,
            tipo: StockMovimiento::TIPO_SALIDA,
            cantidad: $cantidad,
            stockAnterior: $stockAnterior,
            stockNuevo: $stockNuevo,
            pedido: $pedido,
            user: $user,
            motivo: $motivo,
        );

        DB::afterCommit(static fn (): mixed => StockReserved::dispatch(
            (int) $producto->getKey(),
            (int) $pedido->getKey(),
            $cantidad,
            $user?->id,
            $source,
        ));

        return [
            'producto' => $producto,
            'precio_unitario' => $precioUnitario,
            'subtotal' => MoneyDecimal::multiply($precioUnitario, $cantidad),
            'stock_anterior' => $stockAnterior,
            'stock_nuevo' => $stockNuevo,
            'movimiento' => $movimiento,
        ];
    }
}
