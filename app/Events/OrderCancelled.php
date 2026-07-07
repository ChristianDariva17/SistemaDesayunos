<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class OrderCancelled
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly int $pedidoId,
        public readonly ?int $userId,
        public readonly string $businessDate,
    ) {}
}
