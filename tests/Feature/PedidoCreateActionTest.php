<?php

declare(strict_types=1);

use App\Actions\Pedido\CreatePedidoAction;
use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;

it('creates a pedido through the shared create action and persists stock changes', function (): void {
    $user = User::factory()->create([
        'rol' => 'trabajador',
    ]);

    $cliente = Cliente::create([
        'nombre' => 'Ana',
        'apellido' => 'Paredes',
        'email' => 'ana.paredes.action@example.com',
        'estado' => 'activo',
    ]);

    $empleado = Empleado::create([
        'nombre' => 'Luis Gomez',
        'rol_operativo' => 'mesero',
        'estado' => 'activo',
    ]);

    $producto = Producto::create([
        'nombre' => 'Sandwich',
        'categoria' => 'desayuno',
        'stock' => 10,
        'estado' => 'activo',
        'precio' => 12.50,
    ]);

    $pedido = app(CreatePedidoAction::class)->handle([
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'metodo_pago' => 'efectivo',
        'observaciones' => 'Pedido de prueba',
        'productos' => [
            [
                'id' => $producto->id,
                'cantidad' => 3,
            ],
        ],
    ], $user->id);

    expect($pedido)->toBeInstanceOf(Pedido::class)
        ->and($pedido->numero_pedido)->toMatch('/^PED-\d{6}-[A-Z0-9]{6}$/')
        ->and((float) $pedido->total)->toBe(37.5)
        ->and($pedido->productos)->toHaveCount(1)
        ->and((int) $pedido->productos->first()->pivot->cantidad)->toBe(3)
        ->and((float) $pedido->productos->first()->pivot->subtotal)->toBe(37.5);

    $this->assertDatabaseHas('pedidos', [
        'id' => $pedido->id,
        'numero_pedido' => $pedido->numero_pedido,
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'estado' => 'pendiente',
        'total' => 37.5,
    ]);

    $this->assertDatabaseHas('pedido_producto', [
        'pedido_id' => $pedido->id,
        'producto_id' => $producto->id,
        'cantidad' => 3,
        'precio_unitario' => 12.5,
        'subtotal' => 37.5,
    ]);

    expect($producto->refresh()->stock)->toBe(7);
});

it('rolls back pedido creation when product stock is insufficient', function (): void {
    $user = User::factory()->create([
        'rol' => 'trabajador',
    ]);

    $cliente = Cliente::create([
        'nombre' => 'Ana',
        'apellido' => 'Paredes',
        'email' => 'ana.paredes.rollback@example.com',
        'estado' => 'activo',
    ]);

    $empleado = Empleado::create([
        'nombre' => 'Luis Gomez',
        'rol_operativo' => 'mesero',
        'estado' => 'activo',
    ]);

    $productoConStock = Producto::create([
        'nombre' => 'Sandwich',
        'categoria' => 'desayuno',
        'stock' => 10,
        'estado' => 'activo',
        'precio' => 12.50,
    ]);

    $productoSinStock = Producto::create([
        'nombre' => 'Cafe',
        'categoria' => 'bebida',
        'stock' => 1,
        'estado' => 'activo',
        'precio' => 5.00,
    ]);

    expect(fn () => app(CreatePedidoAction::class)->handle([
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'metodo_pago' => 'efectivo',
        'observaciones' => 'Pedido con stock insuficiente',
        'productos' => [
            [
                'id' => $productoConStock->id,
                'cantidad' => 3,
            ],
            [
                'id' => $productoSinStock->id,
                'cantidad' => 2,
            ],
        ],
    ], $user->id))->toThrow(Exception::class, 'Stock insuficiente para Cafe');

    $this->assertDatabaseMissing('pedidos', [
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'observaciones' => 'Pedido con stock insuficiente',
    ]);

    $this->assertDatabaseMissing('pedido_producto', [
        'producto_id' => $productoConStock->id,
        'cantidad' => 3,
    ]);

    expect($productoConStock->refresh()->stock)->toBe(10)
        ->and($productoSinStock->refresh()->stock)->toBe(1);
});

it('creates a pedido through the admin HTTP store flow', function (): void {
    $user = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $cliente = Cliente::create([
        'nombre' => 'Ana',
        'apellido' => 'Paredes',
        'email' => 'ana.paredes.admin@example.com',
        'estado' => 'activo',
    ]);

    $empleado = Empleado::create([
        'nombre' => 'Luis Gomez',
        'rol_operativo' => 'mesero',
        'estado' => 'activo',
    ]);

    $producto = Producto::create([
        'nombre' => 'Sandwich',
        'categoria' => 'desayuno',
        'stock' => 10,
        'estado' => 'activo',
        'precio' => 12.50,
    ]);

    $response = $this->actingAs($user)->post(route('admin.pedidos.store'), [
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'metodo_pago' => 'efectivo',
        'observaciones' => 'Pedido de prueba',
        'productos' => [
            [
                'producto_id' => $producto->id,
                'cantidad' => 3,
            ],
        ],
    ]);

    $pedido = Pedido::query()->with('productos')->firstOrFail();

    $response->assertRedirect(route('admin.pedidos.show', $pedido));

    expect($pedido->numero_pedido)->toMatch('/^PED-\d{6}-[A-Z0-9]{6}$/')
        ->and((float) $pedido->total)->toBe(37.5)
        ->and($pedido->productos)->toHaveCount(1)
        ->and((int) $pedido->productos->first()->pivot->cantidad)->toBe(3)
        ->and((float) $pedido->productos->first()->pivot->subtotal)->toBe(37.5);

    $this->assertDatabaseHas('pedidos', [
        'id' => $pedido->id,
        'numero_pedido' => $pedido->numero_pedido,
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'estado' => 'pendiente',
        'total' => 37.5,
    ]);

    $this->assertDatabaseHas('pedido_producto', [
        'pedido_id' => $pedido->id,
        'producto_id' => $producto->id,
        'cantidad' => 3,
        'precio_unitario' => 12.5,
        'subtotal' => 37.5,
    ]);

    expect($producto->refresh()->stock)->toBe(7);
});

it('creates a pedido through the trabajador HTTP store flow', function (): void {
    $user = User::factory()->create([
        'rol' => 'trabajador',
    ]);

    $cliente = Cliente::create([
        'nombre' => 'Ana',
        'apellido' => 'Paredes',
        'email' => 'ana.paredes.worker@example.com',
        'estado' => 'activo',
    ]);

    $empleado = Empleado::create([
        'nombre' => 'Luis Gomez',
        'rol_operativo' => 'mesero',
        'estado' => 'activo',
    ]);

    $producto = Producto::create([
        'nombre' => 'Sandwich',
        'categoria' => 'desayuno',
        'stock' => 10,
        'estado' => 'activo',
        'precio' => 12.50,
    ]);

    $response = $this->actingAs($user)->post(route('trabajador.pedidos.store'), [
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'metodo_pago' => 'efectivo',
        'observaciones' => 'Pedido de prueba',
        'productos' => [
            [
                'producto_id' => $producto->id,
                'cantidad' => 3,
            ],
        ],
    ]);

    $pedido = Pedido::query()->with('productos')->firstOrFail();

    $response->assertRedirect(route('trabajador.pedidos.show', $pedido));

    expect($pedido->numero_pedido)->toMatch('/^PED-\d{6}-[A-Z0-9]{6}$/')
        ->and((float) $pedido->total)->toBe(37.5)
        ->and($pedido->productos)->toHaveCount(1)
        ->and((int) $pedido->productos->first()->pivot->cantidad)->toBe(3)
        ->and((float) $pedido->productos->first()->pivot->subtotal)->toBe(37.5);

    $this->assertDatabaseHas('pedidos', [
        'id' => $pedido->id,
        'numero_pedido' => $pedido->numero_pedido,
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'estado' => 'pendiente',
        'total' => 37.5,
    ]);

    $this->assertDatabaseHas('pedido_producto', [
        'pedido_id' => $pedido->id,
        'producto_id' => $producto->id,
        'cantidad' => 3,
        'precio_unitario' => 12.5,
        'subtotal' => 37.5,
    ]);

    expect($producto->refresh()->stock)->toBe(7);
});
