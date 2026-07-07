<?php

declare(strict_types=1);

namespace App\Actions\Pedido\Concerns;

use App\Actions\Stock\RegisterStockMovementAction;
use App\Events\StockReleased;
use App\Events\StockReserved;
use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\StockMovimiento;
use App\Models\User;
use App\Support\MoneyDecimal;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

trait HandlesPedidoProductStock
{
    /**
     * @param  array{cliente_id:int,empleado_id:int}  $data
     */
    private function validateActiveEntities(array $data): void
    {
        if (! Cliente::query()->whereKey($data['cliente_id'])->where('estado', 'activo')->exists()) {
            throw ValidationException::withMessages([
                'cliente_id' => 'The selected cliente must be active.',
            ]);
        }

        if (! Empleado::query()->whereKey($data['empleado_id'])->where('estado', 'activo')->exists()) {
            throw ValidationException::withMessages([
                'empleado_id' => 'The selected empleado must be active.',
            ]);
        }
    }

    private function attachProductsAndReserveStock(
        Pedido $pedido,
        iterable $productos,
        RegisterStockMovementAction $registerStockMovement,
        ?User $user = null,
    ): string {
        $subtotals = [];

        foreach ($productos as $productoData) {
            $productoId = $productoData instanceof Producto ? $productoData->getKey() : $productoData['id'];

            $producto = Producto::query()
                ->lockForUpdate()
                ->findOrFail($productoId);

            $cantidad = $productoData instanceof Producto
                ? (int) $productoData->pivot->cantidad
                : (int) $productoData['cantidad'];

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

            $subtotal = MoneyDecimal::multiply($precioUnitario, $cantidad);

            $stockAnterior = (int) $producto->stock;
            $availableStock = $producto->availableStock();

            if ($availableStock < $cantidad) {
                throw new Exception("Stock insuficiente para {$producto->nombre}. Disponible: {$availableStock}");
            }

            $stockNuevo = $stockAnterior - $cantidad;

            $producto->update([
                'stock' => $stockNuevo,
            ]);

            $pedido->productos()->attach($producto->id, [
                'cantidad' => $cantidad,
                'precio_unitario' => $precioUnitario,
                'subtotal' => $subtotal,
            ]);

            $registerStockMovement->handle(
                producto: $producto,
                tipo: StockMovimiento::TIPO_SALIDA,
                cantidad: $cantidad,
                stockAnterior: $stockAnterior,
                stockNuevo: $stockNuevo,
                pedido: $pedido,
                user: $user,
                motivo: 'Pedido stock reservation',
            );

            DB::afterCommit(static fn (): mixed => StockReserved::dispatch(
                (int) $producto->getKey(),
                (int) $pedido->getKey(),
                $cantidad,
                $user?->id,
                'pedido.create',
            ));

            $subtotals[] = $subtotal;
        }

        return MoneyDecimal::sum($subtotals);
    }

    private function restorePedidoStock(
        Pedido $pedido,
        RegisterStockMovementAction $registerStockMovement,
        ?User $user,
        string $motivo,
    ): void {
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

            $registerStockMovement->handle(
                producto: $lockedProducto,
                tipo: StockMovimiento::TIPO_CANCELACION,
                cantidad: $cantidad,
                stockAnterior: $stockAnterior,
                stockNuevo: $stockNuevo,
                pedido: $pedido,
                user: $user,
                motivo: $motivo,
            );

            DB::afterCommit(static fn (): mixed => StockReleased::dispatch(
                (int) $lockedProducto->getKey(),
                (int) $pedido->getKey(),
                $cantidad,
                $user?->id,
                'pedido.cancel',
            ));
        }
    }
}
