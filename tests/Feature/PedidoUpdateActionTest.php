<?php

declare(strict_types=1);

use App\Actions\Pedido\UpdatePedidoAction;
use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;

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

    expect($producto->refresh()->stock)->toBe(6);
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

    expect($producto->refresh()->stock)->toBe(3);
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

    expect($productoA->refresh()->stock)->toBe($initialStockA)
        ->and($productoB->refresh()->stock)->toBe($initialStockB);
});
