<?php

declare(strict_types=1);

namespace App\Support;

final class InventoryLimits
{
    public const LOW_STOCK_THRESHOLD = 10;
    public const MAX_STOCK_LEVEL = 2_147_483_647;

    private function __construct()
    {
    }
}
