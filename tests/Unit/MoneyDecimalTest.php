<?php

declare(strict_types=1);

use App\Support\MoneyDecimal;

it('rounds decimal strings to cents without binary floating point drift', function (): void {
    expect(MoneyDecimal::toCents('1.005'))->toBe(101)
        ->and(MoneyDecimal::fromCents(101))->toBe('1.01')
        ->and(MoneyDecimal::multiply('0.10', 3))->toBe('0.30')
        ->and(MoneyDecimal::sum(['0.10', '0.20', '0.30']))->toBe('0.60');
});
