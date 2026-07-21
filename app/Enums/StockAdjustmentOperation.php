<?php

declare(strict_types=1);

namespace App\Enums;

enum StockAdjustmentOperation: string
{
    case Increment = 'increment';
    case Decrement = 'decrement';
    case Set = 'set';
}
