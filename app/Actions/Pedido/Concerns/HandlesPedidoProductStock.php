<?php

declare(strict_types=1);

namespace App\Actions\Pedido\Concerns;

use App\Actions\Inventory\ReleaseProductoStockAction;
use App\Actions\Inventory\ReserveProductoStockAction;
use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use App\Support\MoneyDecimal;
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
        ReserveProductoStockAction $reserveProductoStock,
        ?User $user = null,
    ): string {
        $subtotals = [];

        $productos = collect($productos)->sortBy(
            static fn (mixed $productoData): int => (int) ($productoData instanceof Producto
                ? $productoData->getKey()
                : $productoData['id']),
        );

        foreach ($productos as $productoData) {
            $productoId = $productoData instanceof Producto ? $productoData->getKey() : $productoData['id'];

            $cantidad = $productoData instanceof Producto
                ? (int) $productoData->pivot->cantidad
                : (int) $productoData['cantidad'];

            $reservation = $reserveProductoStock->handle(
                productoId: (int) $productoId,
                cantidad: $cantidad,
                pedido: $pedido,
                user: $user,
                motivo: 'Pedido stock reservation',
                source: 'pedido.create',
            );

            $pedido->productos()->attach($reservation['producto']->id, [
                'cantidad' => $cantidad,
                'precio_unitario' => $reservation['precio_unitario'],
                'subtotal' => $reservation['subtotal'],
            ]);

            $subtotals[] = $reservation['subtotal'];
        }

        return MoneyDecimal::sum($subtotals);
    }

    private function restorePedidoStock(
        Pedido $pedido,
        ReleaseProductoStockAction $releaseProductoStock,
        ?User $user,
        string $motivo,
        string $source,
    ): void {
        foreach ($pedido->productos as $producto) {
            $cantidad = (int) $producto->pivot->cantidad;

            $releaseProductoStock->handle(
                productoId: (int) $producto->getKey(),
                cantidad: $cantidad,
                pedido: $pedido,
                user: $user,
                motivo: $motivo,
                source: $source,
            );
        }
    }
}
