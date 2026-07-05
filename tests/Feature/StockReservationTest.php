<?php

declare(strict_types=1);

use App\Actions\Stock\RegisterStockMovementAction;
use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\StockMovimiento;
use App\Models\StockReservation;
use App\Models\User;

function stockReservationTestProducto(array $attributes = []): Producto
{
    return Producto::create(array_merge([
        'nombre' => 'Reserved Producto',
        'categoria' => 'desayuno',
        'stock' => 10,
        'estado' => 'activo',
        'precio' => 7.50,
    ], $attributes));
}

function stockReservationTestPedido(array $attributes = []): Pedido
{
    $cliente = Cliente::create([
        'nombre' => 'Reserva',
        'apellido' => 'Cliente',
        'email' => 'reserva.cliente.'.uniqid().'@example.com',
        'estado' => 'activo',
    ]);

    $empleado = Empleado::create([
        'nombre' => 'Reserva Empleado',
        'rol_operativo' => 'mesero',
        'estado' => 'activo',
    ]);

    return Pedido::create(array_merge([
        'numero_pedido' => 'PED-'.uniqid(),
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'metodo_pago' => 'efectivo',
        'fecha' => '2026-07-04',
        'hora' => '08:30:00',
        'total' => 0,
        'estado' => 'pendiente',
        'observaciones' => null,
    ], $attributes));
}

it('creates an active stock reservation without decrementing physical stock', function (): void {
    $producto = stockReservationTestProducto(['stock' => 10]);
    $pedido = stockReservationTestPedido();

    $reservation = $producto->reserveStockForPedido($pedido, 4);

    expect($reservation)->toBeInstanceOf(StockReservation::class)
        ->and($reservation->status)->toBe(StockReservation::STATUS_ACTIVE)
        ->and($reservation->cantidad)->toBe(4)
        ->and($producto->refresh()->stock)->toBe(10)
        ->and($producto->activeReservedStock())->toBe(4)
        ->and($producto->availableStock())->toBe(6);

    $this->assertDatabaseHas('stock_reservations', [
        'producto_id' => $producto->id,
        'pedido_id' => $pedido->id,
        'cantidad' => 4,
        'status' => StockReservation::STATUS_ACTIVE,
    ]);
});

it('prevents stock over-reservation using active reservations as unavailable stock', function (): void {
    $producto = stockReservationTestProducto(['stock' => 5]);
    $firstPedido = stockReservationTestPedido();
    $secondPedido = stockReservationTestPedido();

    StockReservation::reserve($producto, $firstPedido, 3);

    expect(fn () => StockReservation::reserve($producto, $secondPedido, 3))
        ->toThrow(\DomainException::class, 'Stock insuficiente para Reserved Producto. Disponible: 2');

    $this->assertDatabaseCount('stock_reservations', 1);
    expect($producto->refresh()->availableStock())->toBe(2);
});

it('releases an active reservation and restores available stock', function (): void {
    $producto = stockReservationTestProducto(['stock' => 8]);
    $pedido = stockReservationTestPedido();

    $reservation = StockReservation::reserve($producto, $pedido, 5);

    expect($producto->refresh()->availableStock())->toBe(3);

    $released = $reservation->release();

    expect($released->status)->toBe(StockReservation::STATUS_RELEASED)
        ->and($producto->refresh()->activeReservedStock())->toBe(0)
        ->and($producto->availableStock())->toBe(8);
});

it('cancels an active reservation and restores available stock', function (): void {
    $producto = stockReservationTestProducto(['stock' => 8]);
    $pedido = stockReservationTestPedido();

    $reservation = StockReservation::reserve($producto, $pedido, 5);

    expect($producto->refresh()->availableStock())->toBe(3);

    $cancelled = $reservation->cancel();

    expect($cancelled->status)->toBe(StockReservation::STATUS_CANCELLED)
        ->and($producto->refresh()->activeReservedStock())->toBe(0)
        ->and($producto->availableStock())->toBe(8);
});

it('keeps repeated terminal lifecycle transitions idempotent', function (): void {
    $producto = stockReservationTestProducto(['stock' => 8]);
    $releasedReservation = StockReservation::reserve($producto, stockReservationTestPedido(), 2)->release();
    $cancelledReservation = StockReservation::reserve($producto, stockReservationTestPedido(), 3)->cancel();
    $consumedReservation = StockReservation::reserve($producto, stockReservationTestPedido(), 1)
        ->consume(app(RegisterStockMovementAction::class));

    expect($releasedReservation->release()->status)->toBe(StockReservation::STATUS_RELEASED)
        ->and($releasedReservation->cancel()->status)->toBe(StockReservation::STATUS_RELEASED)
        ->and($releasedReservation->consume(app(RegisterStockMovementAction::class))->status)->toBe(StockReservation::STATUS_RELEASED)
        ->and($cancelledReservation->cancel()->status)->toBe(StockReservation::STATUS_CANCELLED)
        ->and($cancelledReservation->release()->status)->toBe(StockReservation::STATUS_CANCELLED)
        ->and($cancelledReservation->consume(app(RegisterStockMovementAction::class))->status)->toBe(StockReservation::STATUS_CANCELLED)
        ->and($consumedReservation->consume(app(RegisterStockMovementAction::class))->status)->toBe(StockReservation::STATUS_CONSUMED)
        ->and($consumedReservation->release()->status)->toBe(StockReservation::STATUS_CONSUMED)
        ->and($consumedReservation->cancel()->status)->toBe(StockReservation::STATUS_CONSUMED)
        ->and($producto->refresh()->stock)->toBe(7)
        ->and($producto->activeReservedStock())->toBe(0);

    $this->assertDatabaseCount('stock_movimientos', 1);
});

it('consumes an active reservation atomically and records the final stock salida', function (): void {
    $user = User::factory()->create([
        'rol' => 'administrador',
    ]);
    $producto = stockReservationTestProducto(['stock' => 6]);
    $pedido = stockReservationTestPedido();
    $reservation = StockReservation::reserve($producto, $pedido, 4);

    expect($producto->refresh()->availableStock())->toBe(2);

    $consumed = $reservation->consume(app(RegisterStockMovementAction::class), $user);

    expect($consumed->status)->toBe(StockReservation::STATUS_CONSUMED)
        ->and($producto->refresh()->stock)->toBe(2)
        ->and($producto->activeReservedStock())->toBe(0)
        ->and($producto->availableStock())->toBe(2);

    $this->assertDatabaseHas('stock_movimientos', [
        'producto_id' => $producto->id,
        'pedido_id' => $pedido->id,
        'user_id' => $user->id,
        'tipo' => StockMovimiento::TIPO_SALIDA,
        'cantidad' => 4,
        'stock_anterior' => 6,
        'stock_nuevo' => 2,
        'motivo' => 'Stock reservation consumed',
    ]);
});

it('removes active reservation rows when deleting the related pedido', function (): void {
    $producto = stockReservationTestProducto(['stock' => 7]);
    $pedido = stockReservationTestPedido();

    StockReservation::reserve($producto, $pedido, 3);

    $pedido->delete();

    $this->assertDatabaseCount('stock_reservations', 0);
    expect($producto->refresh()->availableStock())->toBe(7);
});
