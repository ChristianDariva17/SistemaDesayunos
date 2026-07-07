<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class ProductPriceChanged
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly int $productoId,
        public readonly string $oldPrice,
        public readonly string $newPrice,
    ) {}
}
