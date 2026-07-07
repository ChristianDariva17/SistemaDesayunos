<?php

declare(strict_types=1);

use App\Actions\Pedido\UpdatePedidoAction;
use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\StockMovimiento;
use App\Models\StockReservation;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

it('rejects completed pedido regression to pending status', function (): void {
    $user = User::factory()->create([
        'rol' => 'administrador',
    ]);
    [$pedido] = createPedidoForStatusTransitionTest('completado');

    expect(fn (): mixed => app(UpdatePedidoAction::class)->handle([
        'estado' => 'pendiente',
        'observaciones' => 'Invalid completed regression',
    ], $pedido, $user->id))->toThrow(
        ValidationException::class,
        'The pedido status cannot transition from completado to pendiente.',
    );

    $this->assertDatabaseHas('pedidos', [
        'id' => $pedido->id,
        'estado' => 'completado',
        'observaciones' => null,
    ]);
    $this->assertDatabaseCount('stock_movimientos', 0);
});

it('rejects pending pedido transition directly to completed status', function (): void {
    $user = User::factory()->create([
        'rol' => 'administrador',
    ]);
    [$pedido] = createPedidoForStatusTransitionTest('pendiente');

    expect(fn (): mixed => app(UpdatePedidoAction::class)->handle([
        'estado' => 'completado',
        'observaciones' => 'Invalid lifecycle skip',
    ], $pedido, $user->id))->toThrow(
        ValidationException::class,
        'The pedido status cannot transition from pendiente to completado.',
    );

    $this->assertDatabaseHas('pedidos', [
        'id' => $pedido->id,
        'estado' => 'pendiente',
        'observaciones' => null,
    ]);
    $this->assertDatabaseCount('stock_movimientos', 0);
});

it('allows sequential pedido lifecycle transitions', function (): void {
    $user = User::factory()->create([
        'rol' => 'administrador',
    ]);
    [$pedido] = createPedidoForStatusTransitionTest('pendiente');

    $updatedPedido = app(UpdatePedidoAction::class)->handle([
        'estado' => 'procesando',
        'observaciones' => 'Pedido is being prepared',
    ], $pedido, $user->id);

    expect($updatedPedido->estado)->toBe('procesando')
        ->and($updatedPedido->observaciones)->toBe('Pedido is being prepared');

    $completedPedido = app(UpdatePedidoAction::class)->handle([
        'estado' => 'completado',
        'observaciones' => 'Pedido completed',
    ], $updatedPedido, $user->id);

    expect($completedPedido->estado)->toBe('completado')
        ->and($completedPedido->observaciones)->toBe('Pedido completed');
    $this->assertDatabaseCount('stock_movimientos', 0);
});

it('enforces the pedido status transition matrix', function (string $currentStatus, string $nextStatus, bool $allowed): void {
    $user = User::factory()->create([
        'rol' => 'administrador',
    ]);
    [$pedido] = createPedidoForStatusTransitionTest($currentStatus);

    $payload = [
        'estado' => $nextStatus,
        'observaciones' => "Transition {$currentStatus} to {$nextStatus}",
    ];

    if (! $allowed) {
        expect(fn (): mixed => app(UpdatePedidoAction::class)->handle($payload, $pedido, $user->id))->toThrow(
            ValidationException::class,
            "The pedido status cannot transition from {$currentStatus} to {$nextStatus}.",
        );

        $this->assertDatabaseHas('pedidos', [
            'id' => $pedido->id,
            'estado' => $currentStatus,
            'observaciones' => null,
        ]);
        $this->assertDatabaseCount('stock_movimientos', 0);

        return;
    }

    app(UpdatePedidoAction::class)->handle($payload, $pedido, $user->id);

    $this->assertDatabaseHas('pedidos', [
        'id' => $pedido->id,
        'estado' => $nextStatus,
        'observaciones' => "Transition {$currentStatus} to {$nextStatus}",
    ]);

    $expectedStockMovements = $currentStatus === 'procesando' && $nextStatus === 'cancelado' ? 1 : 0;
    $this->assertDatabaseCount('stock_movimientos', $expectedStockMovements);
})->with([
    'processing to pending is rejected' => ['procesando', 'pendiente', false],
    'processing to cancelled is allowed' => ['procesando', 'cancelado', true],
    'cancelled to processing is rejected' => ['cancelado', 'procesando', false],
    'cancelled to completed is rejected' => ['cancelado', 'completado', false],
    'completed to processing is rejected' => ['completado', 'procesando', false],
    'completed to cancelled is rejected' => ['completado', 'cancelado', false],
    'pending same-status update is a no-op transition' => ['pendiente', 'pendiente', true],
    'processing same-status update is a no-op transition' => ['procesando', 'procesando', true],
    'completed same-status update is a no-op transition' => ['completado', 'completado', true],
]);

it('rejects admin ajax cambiar-estado when transition is disallowed', function (): void {
    $user = User::factory()->create([
        'rol' => 'administrador',
    ]);
    [$pedido] = createPedidoForStatusTransitionTest('pendiente');

    $response = $this->actingAs($user)
        ->patchJson(route('admin.pedidos.cambiar-estado', $pedido), [
            'estado' => 'completado',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('estado')
        ->assertJsonPath('errors.estado.0', 'The pedido status cannot transition from pendiente to completado.');

    $this->assertDatabaseHas('pedidos', [
        'id' => $pedido->id,
        'estado' => 'pendiente',
    ]);
    $this->assertDatabaseCount('stock_movimientos', 0);
});

it('redirects back with validation errors when admin update receives a disallowed transition', function (): void {
    $user = User::factory()->create([
        'rol' => 'administrador',
    ]);
    [$pedido] = createPedidoForStatusTransitionTest('procesando');

    $response = $this->actingAs($user)
        ->from(route('admin.pedidos.edit', $pedido))
        ->patch(route('admin.pedidos.update', $pedido), [
            'estado' => 'pendiente',
            'observaciones' => 'Invalid status regression through the web form',
        ]);

    $response->assertRedirect(route('admin.pedidos.edit', $pedido));
    $response->assertSessionHasErrors([
        'estado' => 'The pedido status cannot transition from procesando to pendiente.',
    ]);

    $this->assertDatabaseHas('pedidos', [
        'id' => $pedido->id,
        'estado' => 'procesando',
        'observaciones' => null,
    ]);
    $this->assertDatabaseCount('stock_movimientos', 0);
});

it('does not expose trabajador pedido update or status-change routes', function (): void {
    expect(Route::has('trabajador.pedidos.edit'))->toBeFalse()
        ->and(Route::has('trabajador.pedidos.update'))->toBeFalse()
        ->and(Route::has('trabajador.pedidos.cambiar-estado'))->toBeFalse();
});

it('updates a pedido through the admin HTTP flow and restores stock on cancel', function (): void {
    $user = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $cliente = Cliente::create([
        'nombre' => 'Laura',
        'apellido' => 'Diaz',
        'email' => 'laura.diaz.action@example.com',
        'estado' => 'activo',
    ]);

    $empleado = Empleado::create([
        'nombre' => 'Carlos Perez',
        'rol_operativo' => 'cocinero',
        'estado' => 'activo',
    ]);

    $producto = Producto::create([
        'nombre' => 'Cafe',
        'categoria' => 'bebida',
        'stock' => 4,
        'estado' => 'activo',
        'precio' => 5.00,
    ]);

    $pedido = Pedido::create([
        'numero_pedido' => 'PED-202606-ACTION',
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'metodo_pago' => 'efectivo',
        'fecha' => '2026-06-01',
        'hora' => '08:30:00',
        'total' => 10.00,
        'estado' => 'pendiente',
        'observaciones' => null,
    ]);

    $pedido->productos()->attach($producto->id, [
        'cantidad' => 2,
        'precio_unitario' => 5.00,
        'subtotal' => 10.00,
    ]);

    $response = $this->actingAs($user)
        ->from(route('admin.pedidos.edit', $pedido))
        ->patch(route('admin.pedidos.update', $pedido), [
            'estado' => 'cancelado',
            'observaciones' => 'Pedido cancelado por prueba',
        ]);

    $response->assertRedirect(route('admin.pedidos.show', $pedido));
    $response->assertSessionHas('success', '✅ Pedido actualizado exitosamente');

    $this->assertDatabaseHas('pedidos', [
        'id' => $pedido->id,
        'estado' => 'cancelado',
        'observaciones' => 'Pedido cancelado por prueba',
    ]);

    $this->assertDatabaseHas('stock_movimientos', [
        'producto_id' => $producto->id,
        'pedido_id' => $pedido->id,
        'user_id' => $user->id,
        'tipo' => StockMovimiento::TIPO_CANCELACION,
        'cantidad' => 2,
        'stock_anterior' => 4,
        'stock_nuevo' => 6,
        'motivo' => 'Pedido cancellation stock restoration',
    ]);

    expect($producto->refresh()->stock)->toBe(6);
});

it('stores whitespace-only pedido observations as null through the admin HTTP update flow', function (): void {
    $user = User::factory()->create([
        'rol' => 'administrador',
    ]);
    [$pedido] = createPedidoForStatusTransitionTest('pendiente');

    $this->actingAs($user)
        ->from(route('admin.pedidos.edit', $pedido))
        ->patch(route('admin.pedidos.update', $pedido), [
            'estado' => 'pendiente',
            'observaciones' => " \t\n ",
        ])
        ->assertRedirect(route('admin.pedidos.show', $pedido));

    $this->assertDatabaseHas('pedidos', [
        'id' => $pedido->id,
        'estado' => 'pendiente',
        'observaciones' => null,
    ]);
});

it('trims non-empty pedido observations through the admin HTTP update flow', function (): void {
    $user = User::factory()->create([
        'rol' => 'administrador',
    ]);
    [$pedido] = createPedidoForStatusTransitionTest('pendiente');

    $this->actingAs($user)
        ->from(route('admin.pedidos.edit', $pedido))
        ->patch(route('admin.pedidos.update', $pedido), [
            'estado' => 'pendiente',
            'observaciones' => "  Leave at reception \n",
        ])
        ->assertRedirect(route('admin.pedidos.show', $pedido));

    $this->assertDatabaseHas('pedidos', [
        'id' => $pedido->id,
        'estado' => 'pendiente',
        'observaciones' => 'Leave at reception',
    ]);
});

it('rejects pedido reactivation when a stale loaded product would oversell stock', function (): void {
    $user = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $cliente = Cliente::create([
        'nombre' => 'Laura',
        'apellido' => 'Diaz',
        'email' => 'laura.diaz.rollback@example.com',
        'estado' => 'activo',
    ]);

    $empleado = Empleado::create([
        'nombre' => 'Carlos Perez',
        'rol_operativo' => 'cocinero',
        'estado' => 'activo',
    ]);

    $producto = Producto::create([
        'nombre' => 'Cafe',
        'categoria' => 'bebida',
        'stock' => 1,
        'estado' => 'activo',
        'precio' => 5.00,
    ]);

    $pedido = Pedido::create([
        'numero_pedido' => 'PED-202606-ROLLBK',
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'metodo_pago' => 'efectivo',
        'fecha' => '2026-06-01',
        'hora' => '08:30:00',
        'total' => 10.00,
        'estado' => 'cancelado',
        'observaciones' => null,
    ]);

    $pedido->productos()->attach($producto->id, [
        'cantidad' => 2,
        'precio_unitario' => 5.00,
        'subtotal' => 10.00,
    ]);

    $pedido->load('productos');

    Producto::query()
        ->whereKey($producto->id)
        ->update([
            'stock' => 1,
        ]);

    expect(fn (): mixed => app(UpdatePedidoAction::class)->handle([
        'estado' => 'pendiente',
        'observaciones' => 'Intento de reactivacion',
    ], $pedido, $user->id))->toThrow(Exception::class, 'Stock insuficiente para Cafe');

    $this->assertDatabaseHas('pedidos', [
        'id' => $pedido->id,
        'estado' => 'cancelado',
        'observaciones' => null,
    ]);

    $this->assertDatabaseCount('stock_movimientos', 0);

    expect($producto->refresh()->stock)->toBe(1);
});

it('reserves stock again when a cancelled pedido is reactivated successfully', function (): void {
    $user = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $cliente = Cliente::create([
        'nombre' => 'Laura',
        'apellido' => 'Diaz',
        'email' => 'laura.diaz.reactivate@example.com',
        'estado' => 'activo',
    ]);

    $empleado = Empleado::create([
        'nombre' => 'Carlos Perez',
        'rol_operativo' => 'cocinero',
        'estado' => 'activo',
    ]);

    $producto = Producto::create([
        'nombre' => 'Cafe',
        'categoria' => 'bebida',
        'stock' => 5,
        'estado' => 'activo',
        'precio' => 5.00,
    ]);

    $pedido = Pedido::create([
        'numero_pedido' => 'PED-202606-REACT',
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'metodo_pago' => 'efectivo',
        'fecha' => '2026-06-01',
        'hora' => '08:30:00',
        'total' => 10.00,
        'estado' => 'cancelado',
        'observaciones' => null,
    ]);

    $pedido->productos()->attach($producto->id, [
        'cantidad' => 2,
        'precio_unitario' => 5.00,
        'subtotal' => 10.00,
    ]);

    app(UpdatePedidoAction::class)->handle([
        'estado' => 'pendiente',
        'observaciones' => 'Pedido reactivado',
    ], $pedido, $user->id);

    $this->assertDatabaseHas('pedidos', [
        'id' => $pedido->id,
        'estado' => 'pendiente',
        'observaciones' => 'Pedido reactivado',
    ]);

    $this->assertDatabaseCount('stock_movimientos', 1);
    $this->assertDatabaseHas('stock_movimientos', [
        'producto_id' => $producto->id,
        'pedido_id' => $pedido->id,
        'user_id' => $user->id,
        'tipo' => StockMovimiento::TIPO_SALIDA,
        'cantidad' => 2,
        'stock_anterior' => 5,
        'stock_nuevo' => 3,
        'motivo' => 'Pedido reactivation stock reservation',
    ]);

    expect($producto->refresh()->stock)->toBe(3);
});

it('rejects pedido reactivation when active reservations leave insufficient available stock', function (): void {
    $user = User::factory()->create([
        'rol' => 'administrador',
    ]);
    [$pedido, $producto] = createPedidoForStatusTransitionTest('cancelado');
    $reservedPedido = createPedidoForStatusTransitionTest('pendiente')[0];

    StockReservation::reserve($producto, $reservedPedido, 9);

    expect(fn (): mixed => app(UpdatePedidoAction::class)->handle([
        'estado' => 'pendiente',
        'observaciones' => 'Intento de reactivacion con reserva activa',
    ], $pedido, $user->id))->toThrow(Exception::class, "Stock insuficiente para {$producto->nombre}");

    $this->assertDatabaseHas('pedidos', [
        'id' => $pedido->id,
        'estado' => 'cancelado',
        'observaciones' => null,
    ]);
    $this->assertDatabaseCount('stock_movimientos', 0);

    expect($producto->refresh()->stock)->toBe(10)
        ->and($producto->availableStock())->toBe(1);
});

it('does not apply cancel stock restoration twice when the same pedido is updated again from a stale model', function (): void {
    $user = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $cliente = Cliente::create([
        'nombre' => 'Laura',
        'apellido' => 'Diaz',
        'email' => 'laura.diaz.double@example.com',
        'estado' => 'activo',
    ]);

    $empleado = Empleado::create([
        'nombre' => 'Carlos Perez',
        'rol_operativo' => 'cocinero',
        'estado' => 'activo',
    ]);

    $producto = Producto::create([
        'nombre' => 'Cafe',
        'categoria' => 'bebida',
        'stock' => 4,
        'estado' => 'activo',
        'precio' => 5.00,
    ]);

    $pedido = Pedido::create([
        'numero_pedido' => 'PED-202606-DOUBLE',
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'metodo_pago' => 'efectivo',
        'fecha' => '2026-06-01',
        'hora' => '08:30:00',
        'total' => 10.00,
        'estado' => 'pendiente',
        'observaciones' => null,
    ]);

    $pedido->productos()->attach($producto->id, [
        'cantidad' => 2,
        'precio_unitario' => 5.00,
        'subtotal' => 10.00,
    ]);

    app(UpdatePedidoAction::class)->handle([
        'estado' => 'cancelado',
        'observaciones' => 'Primera anulacion',
    ], $pedido->fresh(['productos']), $user->id);

    expect($producto->refresh()->stock)->toBe(6);

    app(UpdatePedidoAction::class)->handle([
        'estado' => 'cancelado',
        'observaciones' => 'Segunda anulacion',
    ], $pedido, $user->id);

    $pedido->refresh();

    $this->assertDatabaseCount('stock_movimientos', 1);
    $this->assertDatabaseHas('stock_movimientos', [
        'producto_id' => $producto->id,
        'pedido_id' => $pedido->id,
        'user_id' => $user->id,
        'tipo' => StockMovimiento::TIPO_CANCELACION,
        'cantidad' => 2,
        'stock_anterior' => 4,
        'stock_nuevo' => 6,
    ]);

    expect($producto->refresh()->stock)->toBe(6)
        ->and($pedido->estado)->toBe('cancelado')
        ->and($pedido->observaciones)->toBe('Segunda anulacion');
});

it('keeps pedido stock untouched when admin cambiar-estado fails during reactivation', function (): void {
    $user = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $cliente = Cliente::create([
        'nombre' => 'Laura',
        'apellido' => 'Diaz',
        'email' => 'laura.diaz.route@example.com',
        'estado' => 'activo',
    ]);

    $empleado = Empleado::create([
        'nombre' => 'Carlos Perez',
        'rol_operativo' => 'cocinero',
        'estado' => 'activo',
    ]);

    $productoA = Producto::create([
        'nombre' => 'Sandwich',
        'categoria' => 'comida',
        'stock' => 5,
        'estado' => 'activo',
        'precio' => 8.00,
    ]);

    $productoB = Producto::create([
        'nombre' => 'Jugo',
        'categoria' => 'bebida',
        'stock' => 1,
        'estado' => 'activo',
        'precio' => 4.00,
    ]);

    $pedido = Pedido::create([
        'numero_pedido' => 'PED-202606-ROUTE',
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'metodo_pago' => 'efectivo',
        'fecha' => '2026-06-01',
        'hora' => '08:30:00',
        'total' => 20.00,
        'estado' => 'cancelado',
        'observaciones' => null,
    ]);

    $pedido->productos()->attach($productoA->id, [
        'cantidad' => 2,
        'precio_unitario' => 8.00,
        'subtotal' => 16.00,
    ]);

    $pedido->productos()->attach($productoB->id, [
        'cantidad' => 2,
        'precio_unitario' => 4.00,
        'subtotal' => 8.00,
    ]);

    $initialStockA = $productoA->stock;
    $initialStockB = $productoB->stock;

    $response = $this->actingAs($user)
        ->patchJson(route('admin.pedidos.cambiar-estado', $pedido), [
            'estado' => 'pendiente',
        ]);

    $response->assertStatus(400)
        ->assertJson([
            'success' => false,
            'message' => 'Stock insuficiente para Jugo',
        ]);

    $this->assertDatabaseHas('pedidos', [
        'id' => $pedido->id,
        'estado' => 'cancelado',
    ]);

    $this->assertDatabaseCount('stock_movimientos', 0);

    expect($productoA->refresh()->stock)->toBe($initialStockA)
        ->and($productoB->refresh()->stock)->toBe($initialStockB);
});

it('records stock movements when deleting a non-cancelled pedido restores stock', function (): void {
    $user = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $cliente = Cliente::create([
        'nombre' => 'Laura',
        'apellido' => 'Diaz',
        'email' => 'laura.diaz.destroy@example.com',
        'estado' => 'activo',
    ]);

    $empleado = Empleado::create([
        'nombre' => 'Carlos Perez',
        'rol_operativo' => 'cocinero',
        'estado' => 'activo',
    ]);

    $producto = Producto::create([
        'nombre' => 'Cafe',
        'categoria' => 'bebida',
        'stock' => 4,
        'estado' => 'activo',
        'precio' => 5.00,
    ]);

    $pedido = Pedido::create([
        'numero_pedido' => 'PED-202606-DESTROY',
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'metodo_pago' => 'efectivo',
        'fecha' => '2026-06-01',
        'hora' => '08:30:00',
        'total' => 10.00,
        'estado' => 'pendiente',
        'observaciones' => null,
    ]);

    $pedido->productos()->attach($producto->id, [
        'cantidad' => 2,
        'precio_unitario' => 5.00,
        'subtotal' => 10.00,
    ]);

    $response = $this->actingAs($user)
        ->from(route('admin.pedidos.show', $pedido))
        ->delete(route('admin.pedidos.destroy', $pedido));

    $response->assertRedirect(route('admin.pedidos.index'));

    $this->assertDatabaseMissing('pedidos', [
        'id' => $pedido->id,
    ]);
    $this->assertDatabaseHas('productos', [
        'id' => $producto->id,
        'stock' => 6,
    ]);
    $this->assertDatabaseHas('stock_movimientos', [
        'producto_id' => $producto->id,
        'pedido_id' => null,
        'pedido_numero' => 'PED-202606-DESTROY',
        'user_id' => $user->id,
        'tipo' => StockMovimiento::TIPO_CANCELACION,
        'cantidad' => 2,
        'stock_anterior' => 4,
        'stock_nuevo' => 6,
        'motivo' => 'Pedido deletion stock restoration',
    ]);
});

it('records stock movements when trabajador deletes a non-cancelled pedido', function (): void {
    $user = User::factory()->create([
        'rol' => 'trabajador',
    ]);

    $cliente = Cliente::create([
        'nombre' => 'Laura',
        'apellido' => 'Diaz',
        'email' => 'laura.diaz.worker.destroy@example.com',
        'estado' => 'activo',
    ]);

    $empleado = Empleado::create([
        'nombre' => 'Carlos Perez',
        'rol_operativo' => 'cocinero',
        'estado' => 'activo',
    ]);

    $producto = Producto::create([
        'nombre' => 'Cafe Worker',
        'categoria' => 'bebida',
        'stock' => 4,
        'estado' => 'activo',
        'precio' => 5.00,
    ]);

    $pedido = Pedido::create([
        'numero_pedido' => 'PED-202606-WORKER-DESTROY',
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'metodo_pago' => 'efectivo',
        'fecha' => '2026-06-01',
        'hora' => '08:30:00',
        'total' => 10.00,
        'estado' => 'pendiente',
        'observaciones' => null,
    ]);

    $pedido->productos()->attach($producto->id, [
        'cantidad' => 2,
        'precio_unitario' => 5.00,
        'subtotal' => 10.00,
    ]);

    $response = $this->actingAs($user)
        ->from(route('trabajador.pedidos.show', $pedido))
        ->delete(route('trabajador.pedidos.destroy', $pedido));

    $response->assertRedirect(route('trabajador.pedidos.index'));

    $this->assertDatabaseMissing('pedidos', [
        'id' => $pedido->id,
    ]);
    $this->assertDatabaseHas('productos', [
        'id' => $producto->id,
        'stock' => 6,
    ]);
    $this->assertDatabaseHas('stock_movimientos', [
        'producto_id' => $producto->id,
        'pedido_id' => null,
        'pedido_numero' => 'PED-202606-WORKER-DESTROY',
        'user_id' => $user->id,
        'tipo' => StockMovimiento::TIPO_CANCELACION,
        'cantidad' => 2,
        'stock_anterior' => 4,
        'stock_nuevo' => 6,
        'motivo' => 'Pedido deletion stock restoration',
    ]);
});

it('deletes a cancelled pedido without restoring stock or recording stock movements', function (string $role, string $routePrefix): void {
    $user = User::factory()->create([
        'rol' => $role,
    ]);

    [$pedido, $producto] = createPedidoForStatusTransitionTest('cancelado');
    $initialStock = (int) $producto->stock;

    $response = $this->actingAs($user)
        ->from(route("{$routePrefix}.pedidos.show", $pedido))
        ->delete(route("{$routePrefix}.pedidos.destroy", $pedido));

    $response->assertRedirect(route("{$routePrefix}.pedidos.index"));

    $this->assertDatabaseMissing('pedidos', [
        'id' => $pedido->id,
    ]);
    $this->assertDatabaseHas('productos', [
        'id' => $producto->id,
        'stock' => $initialStock,
    ]);
    $this->assertDatabaseCount('stock_movimientos', 0);

    expect($producto->refresh()->stock)->toBe($initialStock);
})->with([
    'admin' => ['administrador', 'admin'],
    'trabajador' => ['trabajador', 'trabajador'],
]);

it('blocks completed pedido deletion in admin and trabajador flows', function (string $role, string $routePrefix): void {
    $user = User::factory()->create([
        'rol' => $role,
    ]);

    [$pedido, $producto] = createPedidoForStatusTransitionTest('completado');
    $initialStock = (int) $producto->stock;

    $response = $this->actingAs($user)
        ->from(route("{$routePrefix}.pedidos.show", $pedido))
        ->delete(route("{$routePrefix}.pedidos.destroy", $pedido));

    $response->assertRedirect(route("{$routePrefix}.pedidos.show", $pedido));
    $response->assertSessionHas('error', '❌ No se puede eliminar un pedido completado');

    $this->assertDatabaseHas('pedidos', [
        'id' => $pedido->id,
        'estado' => 'completado',
    ]);
    $this->assertDatabaseCount('stock_movimientos', 0);

    expect($producto->refresh()->stock)->toBe($initialStock);
})->with([
    'admin' => ['administrador', 'admin'],
    'trabajador' => ['trabajador', 'trabajador'],
]);

/**
 * @return array{0: Pedido, 1: Producto}
 */
function createPedidoForStatusTransitionTest(string $estado): array
{
    $suffix = str_replace('.', '', uniqid('', true));

    $cliente = Cliente::create([
        'nombre' => 'Laura',
        'apellido' => 'Diaz',
        'email' => "laura.diaz.status.{$suffix}@example.com",
        'estado' => 'activo',
    ]);

    $empleado = Empleado::create([
        'nombre' => 'Carlos Perez',
        'rol_operativo' => 'cocinero',
        'estado' => 'activo',
    ]);

    $producto = Producto::create([
        'nombre' => "Cafe {$suffix}",
        'categoria' => 'bebida',
        'stock' => 10,
        'estado' => 'activo',
        'precio' => 5.00,
    ]);

    $pedido = Pedido::create([
        'numero_pedido' => "PED-STATUS-{$suffix}",
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'metodo_pago' => 'efectivo',
        'fecha' => '2026-06-01',
        'hora' => '08:30:00',
        'total' => 10.00,
        'estado' => $estado,
        'observaciones' => null,
    ]);

    $pedido->productos()->attach($producto->id, [
        'cantidad' => 2,
        'precio_unitario' => 5.00,
        'subtotal' => 10.00,
    ]);

    return [$pedido, $producto];
}
