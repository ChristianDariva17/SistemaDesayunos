<?php

declare(strict_types=1);

namespace App\Actions\Stock;

use App\Models\Pedido;
use App\Models\Producto;
use App\Models\StockMovimiento;
use App\Models\User;
use InvalidArgumentException;

final class RegisterStockMovementAction
{
    public function handle(
        Producto $producto,
        string $tipo,
        int $cantidad,
        int $stockAnterior,
        int $stockNuevo,
        ?Pedido $pedido = null,
        ?User $user = null,
        ?string $motivo = null,
    ): StockMovimiento {
        $this->validate($producto, $tipo, $cantidad, $stockAnterior, $stockNuevo);

        return StockMovimiento::create([
            'producto_id' => $producto->getKey(),
            'pedido_id' => $pedido?->getKey(),
            'user_id' => $user?->getKey(),
            'tipo' => $tipo,
            'cantidad' => $cantidad,
            'stock_anterior' => $stockAnterior,
            'stock_nuevo' => $stockNuevo,
            'motivo' => $motivo,
        ]);
    }

    private function validate(
        Producto $producto,
        string $tipo,
        int $cantidad,
        int $stockAnterior,
        int $stockNuevo,
    ): void {
        if (! $producto->exists) {
            throw new InvalidArgumentException('A persisted producto is required to register a stock movement.');
        }

        if (! in_array($tipo, StockMovimiento::TIPOS, true)) {
            throw new InvalidArgumentException('Invalid stock movement tipo.');
        }

        if ($cantidad <= 0) {
            throw new InvalidArgumentException('Stock movement cantidad must be greater than 0.');
        }

        if ($stockAnterior < 0 || $stockNuevo < 0) {
            throw new InvalidArgumentException('Stock movement stock values must be non-negative.');
        }
    }
}
