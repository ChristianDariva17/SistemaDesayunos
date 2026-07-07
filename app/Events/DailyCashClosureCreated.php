<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class DailyCashClosureCreated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly int $dailyCashClosureId,
        public readonly ?int $userId,
        public readonly string $businessDate,
    ) {}
}
