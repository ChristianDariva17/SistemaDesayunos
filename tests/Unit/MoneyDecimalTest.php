<?php

declare(strict_types=1);

use App\Support\MoneyDecimal;

it('rounds decimal strings to cents without binary floating point drift', function (): void {
    expect(MoneyDecimal::toCents('1.005'))->toBe(101)
        ->and(MoneyDecimal::fromCents(101))->toBe('1.01')
        ->and(MoneyDecimal::multiply('0.10', 3))->toBe('0.30')
        ->and(MoneyDecimal::sum(['0.10', '0.20', '0.30']))->toBe('0.60')
        ->and(MoneyDecimal::sum([]))->toBe('0.00')
        ->and(MoneyDecimal::toCents(''))->toBe(0)
        ->and(MoneyDecimal::divide('0.01', 2))->toBe('0.01')
        ->and(MoneyDecimal::divide('0.02', 4))->toBe('0.01');
});

it('splits inclusive tax while preserving gross cents', function (): void {
    $split = MoneyDecimal::splitInclusiveTax('118.01', 18);

    expect($split)->toBe(['net' => '100.01', 'tax' => '18.00', 'gross' => '118.01'])
        ->and(MoneyDecimal::sum([$split['net'], $split['tax']]))->toBe($split['gross']);
});

it('keeps report blades free of business money float arithmetic', function (): void {
    foreach (['index', 'inventario', 'stock-bajo', 'ventas'] as $view) {
        $source = file_get_contents(dirname(__DIR__, 2)."/resources/views/admin/reportes/{$view}.blade.php");

        expect($source)->not->toMatch('/floatval|\(float\)|number_format\([^\n]*(precio|valorInventario|totalVentas|ticketPromedio|promedioDiario|subtotal|igv|costo|report_)|precio\s*\*|->sum\([^\n]*(total|precio)/i');
    }
});
