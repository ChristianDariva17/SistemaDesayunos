<?php

declare(strict_types=1);

use App\Actions\Cash\CloseDailyCashRegisterAction;
use App\Actions\Pedido\CreatePedidoAction;
use App\Actions\Pedido\UpdatePedidoAction;
use App\Actions\Stock\RegisterStockMovementAction;
use App\Events\DailyCashClosureCreated;
use App\Events\OrderCancelled;
use App\Events\OrderCompleted;
use App\Events\OrderCreated;
use App\Events\ProductPriceChanged;
use App\Events\StockConsumed;
use App\Events\StockReleased;
use App\Events\StockReserved;
use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\StockReservation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

it('emits domain events when an order is created and stock is reserved', function (): void {
    Event::fake([OrderCreated::class, StockReserved::class]);

    $user = User::factory()->create(['rol' => 'trabajador']);
    [$cliente, $empleado] = observabilityBusinessActorFixture();
    $producto = observabilityBusinessProductoFixture(['stock' => 10, 'precio' => '6.50']);

    $pedido = app(CreatePedidoAction::class)->handle([
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'metodo_pago' => 'efectivo',
        'productos' => [
            ['id' => $producto->id, 'cantidad' => 2],
        ],
    ], $user->id);

    Event::assertDispatched(
        OrderCreated::class,
        fn (OrderCreated $event): bool => $event->pedidoId === $pedido->id
            && $event->userId === $user->id
            && $event->businessDate === (string) $pedido->fecha,
    );
    Event::assertDispatched(
        StockReserved::class,
        fn (StockReserved $event): bool => $event->productoId === $producto->id
            && $event->pedidoId === $pedido->id
            && $event->cantidad === 2
            && $event->userId === $user->id
            && $event->operationName === 'pedido.create',
    );
});

it('emits domain events when orders are completed or cancelled', function (): void {
    Event::fake([OrderCancelled::class, OrderCompleted::class, StockReleased::class]);

    $user = User::factory()->create(['rol' => 'administrador']);
    [$completedPedido] = observabilityBusinessPedidoFixture('procesando');
    [$cancelledPedido, $cancelledProducto] = observabilityBusinessPedidoFixture('pendiente');

    app(UpdatePedidoAction::class)->handle([
        'estado' => 'completado',
        'observaciones' => 'Ready for delivery',
    ], $completedPedido, $user->id);

    app(UpdatePedidoAction::class)->handle([
        'estado' => 'cancelado',
        'observaciones' => 'Customer cancelled',
    ], $cancelledPedido, $user->id);

    Event::assertDispatched(
        OrderCompleted::class,
        fn (OrderCompleted $event): bool => $event->pedidoId === $completedPedido->id
            && $event->userId === $user->id,
    );
    Event::assertDispatched(
        OrderCancelled::class,
        fn (OrderCancelled $event): bool => $event->pedidoId === $cancelledPedido->id
            && $event->userId === $user->id,
    );
    Event::assertDispatched(
        StockReleased::class,
        fn (StockReleased $event): bool => $event->productoId === $cancelledProducto->id
            && $event->pedidoId === $cancelledPedido->id
            && $event->cantidad === 2
            && $event->operationName === 'pedido.cancel',
    );
});

it('emits domain events for stock reservation lifecycle operations', function (): void {
    Event::fake([StockConsumed::class, StockReleased::class, StockReserved::class]);

    $user = User::factory()->create(['rol' => 'administrador']);
    [$pedido, $producto] = observabilityBusinessPedidoFixture('pendiente');

    $reservation = StockReservation::reserve($producto, $pedido, 3);
    $reservation->release();
    $reservation->release();

    $consumedReservation = StockReservation::reserve($producto, $pedido, 2);
    $consumedReservation->consume(app(RegisterStockMovementAction::class), $user);
    $consumedReservation->consume(app(RegisterStockMovementAction::class), $user);

    Event::assertDispatched(
        StockReserved::class,
        fn (StockReserved $event): bool => $event->productoId === $producto->id
            && $event->pedidoId === $pedido->id
            && $event->cantidad === 3
            && $event->operationName === 'stock.reserve',
    );
    Event::assertDispatched(
        StockReleased::class,
        fn (StockReleased $event): bool => $event->productoId === $producto->id
            && $event->pedidoId === $pedido->id
            && $event->cantidad === 3
            && $event->operationName === 'stock.release',
    );
    Event::assertDispatched(
        StockConsumed::class,
        fn (StockConsumed $event): bool => $event->productoId === $producto->id
            && $event->pedidoId === $pedido->id
            && $event->cantidad === 2
            && $event->userId === $user->id
            && $event->operationName === 'stock.consume',
    );
    Event::assertDispatchedTimes(StockReleased::class, 1);
    Event::assertDispatchedTimes(StockConsumed::class, 1);
});

it('emits domain events when product prices change and daily cash closures are created', function (): void {
    Event::fake([DailyCashClosureCreated::class, ProductPriceChanged::class]);

    $user = User::factory()->create(['rol' => 'administrador']);
    $producto = observabilityBusinessProductoFixture(['precio' => '10.00']);
    observabilityBusinessClosurePedido('PED-OBS-CLOSE', '2026-07-04', 'completado', '25.00');

    $producto->update(['precio' => '12.50']);
    $closure = app(CloseDailyCashRegisterAction::class)->handle('2026-07-04', $user->id);

    Event::assertDispatched(
        ProductPriceChanged::class,
        fn (ProductPriceChanged $event): bool => $event->productoId === $producto->id
            && $event->oldPrice === '10.00'
            && $event->newPrice === '12.50',
    );
    Event::assertDispatched(
        DailyCashClosureCreated::class,
        fn (DailyCashClosureCreated $event): bool => $event->dailyCashClosureId === $closure->id
            && $event->userId === $user->id
            && $event->businessDate === '2026-07-04',
    );
});

it('writes structured logs for failed business operations without sensitive request data', function (): void {
    $user = User::factory()->create(['rol' => 'trabajador']);
    [$cliente, $empleado] = observabilityBusinessActorFixture();
    $producto = observabilityBusinessProductoFixture(['stock' => 1, 'precio' => '5.00']);

    Log::shouldReceive('error')
        ->once()
        ->with('Business operation failed.', Mockery::on(
            fn (array $context): bool => $context['operation_name'] === 'pedido.create'
                && $context['model_id'] === null
                && $context['user_id'] === $user->id
                && is_string($context['business_date'])
                && $context['exception_class'] === Exception::class
                && $context['exception_message'] === 'Stock insuficiente para Observability Product. Disponible: 1'
                && ! array_key_exists('observaciones', $context)
                && ! array_key_exists('productos', $context),
        ));

    expect(fn () => app(CreatePedidoAction::class)->handle([
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'metodo_pago' => 'efectivo',
        'observaciones' => 'Do not log this free-text note',
        'productos' => [
            ['id' => $producto->id, 'cantidad' => 2],
        ],
    ], $user->id))->toThrow(Exception::class, 'Stock insuficiente para Observability Product. Disponible: 1');
});

it('does not emit stock reservation events for rolled back pedido creation', function (): void {
    Event::fake([StockReserved::class]);

    $user = User::factory()->create(['rol' => 'trabajador']);
    [$cliente, $empleado] = observabilityBusinessActorFixture();
    $reservedProducto = observabilityBusinessProductoFixture([
        'nombre' => 'Rollback Reserved Product',
        'stock' => 10,
        'precio' => '6.50',
    ]);
    $insufficientProducto = observabilityBusinessProductoFixture([
        'nombre' => 'Rollback Insufficient Product',
        'stock' => 1,
        'precio' => '4.00',
    ]);

    expect(fn () => app(CreatePedidoAction::class)->handle([
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'metodo_pago' => 'efectivo',
        'productos' => [
            ['id' => $reservedProducto->id, 'cantidad' => 2],
            ['id' => $insufficientProducto->id, 'cantidad' => 2],
        ],
    ], $user->id))->toThrow(Exception::class, 'Stock insuficiente para Rollback Insufficient Product');

    Event::assertNotDispatched(StockReserved::class);
});

it('does not emit stock release events when an outer transaction rolls back', function (): void {
    Event::fake([StockReleased::class]);

    [$pedido, $producto] = observabilityBusinessPedidoFixture('pendiente');
    $reservation = StockReservation::reserve($producto, $pedido, 3);

    expect(fn () => DB::transaction(function () use ($reservation): void {
        $reservation->release();

        throw new RuntimeException('Force rollback after stock release.');
    }))->toThrow(RuntimeException::class, 'Force rollback after stock release.');

    Event::assertNotDispatched(StockReleased::class);
});

it('does not emit order lifecycle events when an outer transaction rolls back', function (): void {
    Event::fake([OrderCancelled::class, OrderCompleted::class, OrderCreated::class]);

    $user = User::factory()->create(['rol' => 'administrador']);
    [$cliente, $empleado] = observabilityBusinessActorFixture();
    $producto = observabilityBusinessProductoFixture(['stock' => 10, 'precio' => '6.50']);
    [$completedPedido] = observabilityBusinessPedidoFixture('procesando');
    [$cancelledPedido] = observabilityBusinessPedidoFixture('pendiente');

    observabilityBusinessExpectRollbackWithoutEvents(function () use ($cliente, $empleado, $producto, $user): void {
        app(CreatePedidoAction::class)->handle([
            'cliente_id' => $cliente->id,
            'empleado_id' => $empleado->id,
            'metodo_pago' => 'efectivo',
            'productos' => [
                ['id' => $producto->id, 'cantidad' => 2],
            ],
        ], $user->id);
    });

    observabilityBusinessExpectRollbackWithoutEvents(function () use ($completedPedido, $user): void {
        app(UpdatePedidoAction::class)->handle([
            'estado' => 'completado',
            'observaciones' => 'Completed inside rolled back outer transaction',
        ], $completedPedido, $user->id);
    });

    observabilityBusinessExpectRollbackWithoutEvents(function () use ($cancelledPedido, $user): void {
        app(UpdatePedidoAction::class)->handle([
            'estado' => 'cancelado',
            'observaciones' => 'Cancelled inside rolled back outer transaction',
        ], $cancelledPedido, $user->id);
    });

    Event::assertNotDispatched(OrderCreated::class);
    Event::assertNotDispatched(OrderCompleted::class);
    Event::assertNotDispatched(OrderCancelled::class);
});

it('does not emit cash closure, price change, or stock consume events when an outer transaction rolls back', function (): void {
    Event::fake([DailyCashClosureCreated::class, ProductPriceChanged::class, StockConsumed::class]);

    $user = User::factory()->create(['rol' => 'administrador']);
    $producto = observabilityBusinessProductoFixture(['precio' => '10.00', 'stock' => 10]);
    observabilityBusinessClosurePedido('PED-OBS-ROLLBACK-CLOSE-'.str_replace('.', '', uniqid('', true)), '2026-07-05', 'completado', '25.00');
    [$pedido, $stockProducto] = observabilityBusinessPedidoFixture('pendiente');
    $reservation = StockReservation::reserve($stockProducto, $pedido, 2);

    observabilityBusinessExpectRollbackWithoutEvents(function () use ($user): void {
        app(CloseDailyCashRegisterAction::class)->handle('2026-07-05', $user->id);
    });

    observabilityBusinessExpectRollbackWithoutEvents(function () use ($producto): void {
        $producto->update(['precio' => '12.50']);
    });

    observabilityBusinessExpectRollbackWithoutEvents(function () use ($reservation, $user): void {
        $reservation->consume(app(RegisterStockMovementAction::class), $user);
    });

    Event::assertNotDispatched(DailyCashClosureCreated::class);
    Event::assertNotDispatched(ProductPriceChanged::class);
    Event::assertNotDispatched(StockConsumed::class);
});

it('does not emit stock reserved events for reactivated orders when an outer transaction rolls back', function (): void {
    Event::fake([StockReserved::class]);

    $user = User::factory()->create(['rol' => 'administrador']);
    [$pedido] = observabilityBusinessPedidoFixture('cancelado');

    observabilityBusinessExpectRollbackWithoutEvents(function () use ($pedido, $user): void {
        app(UpdatePedidoAction::class)->handle([
            'estado' => 'pendiente',
            'observaciones' => 'Reactivated inside rolled back outer transaction',
        ], $pedido, $user->id);
    });

    Event::assertNotDispatched(StockReserved::class);
});

/**
 * @return array{0: Cliente, 1: Empleado}
 */
function observabilityBusinessActorFixture(): array
{
    $suffix = str_replace('.', '', uniqid('', true));

    return [
        Cliente::create([
            'nombre' => 'Observability',
            'apellido' => 'Customer',
            'email' => "observability.customer.{$suffix}@example.com",
            'estado' => 'activo',
        ]),
        Empleado::create([
            'nombre' => "Observability Employee {$suffix}",
            'rol_operativo' => 'mesero',
            'estado' => 'activo',
        ]),
    ];
}

function observabilityBusinessProductoFixture(array $attributes = []): Producto
{
    return Producto::create(array_merge([
        'nombre' => 'Observability Product',
        'categoria' => 'desayuno',
        'precio' => '5.00',
        'stock' => 10,
        'estado' => 'activo',
    ], $attributes));
}

/**
 * @return array{0: Pedido, 1: Producto}
 */
function observabilityBusinessPedidoFixture(string $estado): array
{
    [$cliente, $empleado] = observabilityBusinessActorFixture();
    $producto = observabilityBusinessProductoFixture(['stock' => 10]);
    $suffix = str_replace('.', '', uniqid('', true));

    $pedido = Pedido::create([
        'numero_pedido' => "PED-OBS-{$suffix}",
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'metodo_pago' => 'efectivo',
        'fecha' => '2026-07-04',
        'hora' => '08:30:00',
        'total' => '10.00',
        'estado' => $estado,
        'observaciones' => null,
    ]);

    $pedido->productos()->attach($producto->id, [
        'cantidad' => 2,
        'precio_unitario' => '5.00',
        'subtotal' => '10.00',
    ]);

    return [$pedido, $producto];
}

function observabilityBusinessClosurePedido(
    string $numeroPedido,
    string $businessDate,
    string $estado,
    string $total,
): Pedido {
    [$cliente, $empleado] = observabilityBusinessActorFixture();

    return Pedido::create([
        'numero_pedido' => $numeroPedido,
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'metodo_pago' => 'efectivo',
        'fecha' => $businessDate,
        'hora' => '08:30:00',
        'total' => $total,
        'estado' => $estado,
        'observaciones' => null,
    ]);
}

function observabilityBusinessExpectRollbackWithoutEvents(Closure $operation): void
{
    expect(fn () => DB::transaction(function () use ($operation): void {
        $operation();

        throw new RuntimeException('Force rollback after business event registration.');
    }))->toThrow(RuntimeException::class, 'Force rollback after business event registration.');
}
