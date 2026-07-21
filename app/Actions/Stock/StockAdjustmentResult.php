<?php

declare(strict_types=1);

namespace App\Actions\Stock;

use App\Models\Producto;
use App\Models\StockMovimiento;

final readonly class StockAdjustmentResult
{
    public function __construct(
        public Producto $producto,
        public int $stockAnterior,
        public int $stockNuevo,
        public ?StockMovimiento $movimiento,
    ) {}
}
