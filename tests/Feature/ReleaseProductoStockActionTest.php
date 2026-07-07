<?php

declare(strict_types=1);

use App\Actions\Inventory\ReleaseProductoStockAction;
use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Validation\ValidationException;

it('rejects non-positive stock release quantities before mutating producto stock', function (int $cantidad): void {
    [$pedido, $producto] = releaseProductoStockActionFixture();

    expect(fn (): mixed => app(ReleaseProductoStockAction::class)->handle(
        productoId: $producto->id,
        cantidad: $cantidad,
        pedido: $pedido,
        user: null,
        motivo: 'Invalid stock release regression',
        source: 'test.release',
    ))->toThrow(ValidationException::class, 'Pedido product cantidad must be greater than 0.');

    expect($producto->refresh()->stock)->toBe(10);
    $this->assertDatabaseCount('stock_movimientos', 0);
})->with([
    'zero quantity' => 0,
    'negative quantity' => -1,
]);

/**
 * @return array{0: Pedido, 1: Producto}
 */
function releaseProductoStockActionFixture(): array
{
    $suffix = str_replace('.', '', uniqid('', true));

    $cliente = Cliente::create([
        'nombre' => 'Release',
        'apellido' => 'Action',
        'email' => "release.action.{$suffix}@example.com",
        'estado' => 'activo',
    ]);

    $empleado = Empleado::create([
        'nombre' => 'Inventory Tester',
        'rol_operativo' => 'cocinero',
        'estado' => 'activo',
    ]);

    $producto = Producto::create([
        'nombre' => "Release Product {$suffix}",
        'categoria' => 'desayuno',
        'stock' => 10,
        'estado' => 'activo',
        'precio' => 5.00,
    ]);

    $pedido = Pedido::create([
        'numero_pedido' => "PED-RELEASE-{$suffix}",
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'metodo_pago' => 'efectivo',
        'fecha' => '2026-07-07',
        'hora' => '08:30:00',
        'total' => 10.00,
        'estado' => 'pendiente',
        'observaciones' => null,
    ]);

    return [$pedido, $producto];
}
