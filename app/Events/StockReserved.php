<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class StockReserved
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly int $productoId,
        public readonly int $pedidoId,
        public readonly int $cantidad,
        public readonly ?int $userId,
        public readonly string $operationName,
    ) {}
}
