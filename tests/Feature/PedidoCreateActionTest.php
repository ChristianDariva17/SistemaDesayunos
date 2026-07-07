<?php

declare(strict_types=1);

use App\Actions\Pedido\CreatePedidoAction;
use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\StockMovimiento;
use App\Models\StockReservation;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Validation\ValidationException;

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

    $this->assertDatabaseHas('stock_movimientos', [
        'producto_id' => $producto->id,
        'pedido_id' => $pedido->id,
        'user_id' => $user->id,
        'tipo' => StockMovimiento::TIPO_SALIDA,
        'cantidad' => 3,
        'stock_anterior' => 10,
        'stock_nuevo' => 7,
        'motivo' => 'Pedido stock reservation',
    ]);

    expect($producto->refresh()->stock)->toBe(7);
});

it('creates salida stock movements when creating a pedido directly through the action', function (): void {
    $cliente = Cliente::create([
        'nombre' => 'Ana',
        'apellido' => 'Paredes',
        'email' => 'ana.paredes.model@example.com',
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
        'observaciones' => 'Pedido directo de prueba',
        'productos' => [
            [
                'id' => $producto->id,
                'cantidad' => 3,
            ],
        ],
    ]);

    $this->assertDatabaseHas('stock_movimientos', [
        'producto_id' => $producto->id,
        'pedido_id' => $pedido->id,
        'user_id' => null,
        'tipo' => StockMovimiento::TIPO_SALIDA,
        'cantidad' => 3,
        'stock_anterior' => 10,
        'stock_nuevo' => 7,
        'motivo' => 'Pedido stock reservation',
    ]);

    expect($producto->refresh()->stock)->toBe(7);
});

it('calculates pedido totals with decimal cents and stores stable historical item prices', function (): void {
    $cliente = Cliente::create([
        'nombre' => 'Ana',
        'apellido' => 'Paredes',
        'email' => 'ana.paredes.money@example.com',
        'estado' => 'activo',
    ]);

    $empleado = Empleado::create([
        'nombre' => 'Luis Gomez',
        'rol_operativo' => 'mesero',
        'estado' => 'activo',
    ]);

    $cafe = Producto::create([
        'nombre' => 'Decimal Cafe',
        'categoria' => 'bebida',
        'stock' => 10,
        'estado' => 'activo',
        'precio' => '0.10',
    ]);

    $pan = Producto::create([
        'nombre' => 'Decimal Pan',
        'categoria' => 'panaderia',
        'stock' => 10,
        'estado' => 'activo',
        'precio' => '0.20',
    ]);

    $pedido = app(CreatePedidoAction::class)->handle([
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'metodo_pago' => 'efectivo',
        'productos' => [
            ['id' => $cafe->id, 'cantidad' => 3],
            ['id' => $pan->id, 'cantidad' => 2],
        ],
    ])->load('productos');

    $cafe->update(['precio' => '9.99']);
    $pan->update(['precio' => '8.88']);

    $reloadedPedido = $pedido->fresh()->load('productos');
    $cafeLine = $reloadedPedido->productos->firstWhere('id', $cafe->id);
    $panLine = $reloadedPedido->productos->firstWhere('id', $pan->id);

    expect($reloadedPedido->total)->toBe('0.70')
        ->and((string) $cafeLine->pivot->precio_unitario)->toBe('0.10')
        ->and((string) $cafeLine->pivot->subtotal)->toBe('0.30')
        ->and((string) $panLine->pivot->precio_unitario)->toBe('0.20')
        ->and((string) $panLine->pivot->subtotal)->toBe('0.40');
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

    $this->assertDatabaseCount('stock_movimientos', 0);

    expect($productoConStock->refresh()->stock)->toBe(10)
        ->and($productoSinStock->refresh()->stock)->toBe(1);
});

it('rejects pedido creation when active reservations leave insufficient available stock', function (): void {
    $user = User::factory()->create([
        'rol' => 'trabajador',
    ]);

    $cliente = Cliente::create([
        'nombre' => 'Ana',
        'apellido' => 'Paredes',
        'email' => 'ana.paredes.reserved@example.com',
        'estado' => 'activo',
    ]);

    $empleado = Empleado::create([
        'nombre' => 'Luis Gomez',
        'rol_operativo' => 'mesero',
        'estado' => 'activo',
    ]);

    $producto = Producto::create([
        'nombre' => 'Reserved Sandwich',
        'categoria' => 'desayuno',
        'stock' => 5,
        'estado' => 'activo',
        'precio' => 12.50,
    ]);

    $reservedPedido = Pedido::create([
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'metodo_pago' => 'efectivo',
        'fecha' => '2026-07-04',
        'hora' => '08:30:00',
        'total' => 0,
        'estado' => 'pendiente',
        'observaciones' => null,
    ]);

    StockReservation::reserve($producto, $reservedPedido, 4);

    expect(fn () => app(CreatePedidoAction::class)->handle([
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'metodo_pago' => 'efectivo',
        'observaciones' => 'Pedido con stock reservado',
        'productos' => [
            [
                'id' => $producto->id,
                'cantidad' => 2,
            ],
        ],
    ], $user->id))->toThrow(Exception::class, 'Stock insuficiente para Reserved Sandwich. Disponible: 1');

    $this->assertDatabaseMissing('pedidos', [
        'observaciones' => 'Pedido con stock reservado',
    ]);
    $this->assertDatabaseMissing('pedido_producto', [
        'producto_id' => $producto->id,
        'cantidad' => 2,
    ]);
    $this->assertDatabaseCount('stock_movimientos', 0);

    expect($producto->refresh()->stock)->toBe(5)
        ->and($producto->availableStock())->toBe(1);
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

it('stores whitespace-only pedido observations as null through the admin HTTP store flow', function (): void {
    $user = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $cliente = Cliente::create([
        'nombre' => 'Ana',
        'apellido' => 'Paredes',
        'email' => 'ana.paredes.blank-observations@example.com',
        'estado' => 'activo',
    ]);

    $empleado = Empleado::create([
        'nombre' => 'Luis Gomez',
        'rol_operativo' => 'mesero',
        'estado' => 'activo',
    ]);

    $producto = Producto::create([
        'nombre' => 'Sandwich Observaciones Blank',
        'categoria' => 'desayuno',
        'stock' => 10,
        'estado' => 'activo',
        'precio' => 12.50,
    ]);

    $this->actingAs($user)->post(route('admin.pedidos.store'), [
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'metodo_pago' => 'efectivo',
        'observaciones' => " \t\n ",
        'productos' => [
            [
                'producto_id' => $producto->id,
                'cantidad' => 1,
            ],
        ],
    ])->assertRedirect();

    $this->assertDatabaseHas('pedidos', [
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'observaciones' => null,
    ]);
});

it('trims non-empty pedido observations through the admin HTTP store flow', function (): void {
    $user = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $cliente = Cliente::create([
        'nombre' => 'Ana',
        'apellido' => 'Paredes',
        'email' => 'ana.paredes.trimmed-observations@example.com',
        'estado' => 'activo',
    ]);

    $empleado = Empleado::create([
        'nombre' => 'Luis Gomez',
        'rol_operativo' => 'mesero',
        'estado' => 'activo',
    ]);

    $producto = Producto::create([
        'nombre' => 'Sandwich Observaciones Trimmed',
        'categoria' => 'desayuno',
        'stock' => 10,
        'estado' => 'activo',
        'precio' => 12.50,
    ]);

    $this->actingAs($user)->post(route('admin.pedidos.store'), [
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'metodo_pago' => 'efectivo',
        'observaciones' => "  Leave at reception \n",
        'productos' => [
            [
                'producto_id' => $producto->id,
                'cantidad' => 1,
            ],
        ],
    ])->assertRedirect();

    $this->assertDatabaseHas('pedidos', [
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'observaciones' => 'Leave at reception',
    ]);
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

it('rejects duplicate product lines in the admin HTTP store flow before hitting the pivot constraint', function (): void {
    $user = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $cliente = Cliente::create([
        'nombre' => 'Ana',
        'apellido' => 'Paredes',
        'email' => 'ana.paredes.duplicate-create@example.com',
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

    $response = $this->actingAs($user)
        ->from(route('admin.pedidos.create'))
        ->post(route('admin.pedidos.store'), [
            'cliente_id' => $cliente->id,
            'empleado_id' => $empleado->id,
            'metodo_pago' => 'efectivo',
            'productos' => [
                [
                    'producto_id' => $producto->id,
                    'cantidad' => 1,
                ],
                [
                    'id' => $producto->id,
                    'cantidad' => 2,
                ],
            ],
        ]);

    $response->assertRedirect(route('admin.pedidos.create'));
    $response->assertSessionHasErrors(['productos.0.id', 'productos.1.id']);

    $this->assertDatabaseCount('pedidos', 0);
    $this->assertDatabaseCount('pedido_producto', 0);

    expect($producto->refresh()->stock)->toBe(10);
});

it('exposes subtotal through the product to pedidos pivot relationship', function (): void {
    $cliente = Cliente::create([
        'nombre' => 'Ana',
        'apellido' => 'Paredes',
        'email' => 'ana.paredes.product-pivot@example.com',
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

    $pedido = Pedido::create([
        'numero_pedido' => 'PED-202607-PIVOT1',
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'metodo_pago' => 'efectivo',
        'fecha' => '2026-07-02',
        'hora' => '08:30:00',
        'total' => 37.50,
        'estado' => 'pendiente',
        'observaciones' => null,
    ]);

    $pedido->productos()->attach($producto->id, [
        'cantidad' => 3,
        'precio_unitario' => 12.50,
        'subtotal' => 37.50,
    ]);

    $pedidoFromProduct = $producto->pedidos()->firstOrFail();

    expect((int) $pedidoFromProduct->pivot->cantidad)->toBe(3)
        ->and((float) $pedidoFromProduct->pivot->precio_unitario)->toBe(12.5)
        ->and((float) $pedidoFromProduct->pivot->subtotal)->toBe(37.5);
});

it('keeps pedido product relationships typed and exposes canonical pivot fields', function (): void {
    $pedido = new Pedido;
    $producto = new Producto;

    expect($pedido->productos())->toBeInstanceOf(BelongsToMany::class)
        ->and($producto->pedidos())->toBeInstanceOf(BelongsToMany::class)
        ->and(Pedido::PRODUCTOS_PIVOT_COLUMNS)->toBe(['cantidad', 'precio_unitario', 'subtotal']);
});

it('loads pedido details through the shared helper with canonical product pivot data', function (): void {
    $cliente = Cliente::create([
        'nombre' => 'Ana',
        'apellido' => 'Paredes',
        'email' => 'ana.paredes.details@example.com',
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

    $pedido = Pedido::create([
        'numero_pedido' => 'PED-202607-DETAIL',
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'metodo_pago' => 'efectivo',
        'fecha' => '2026-07-02',
        'hora' => '08:30:00',
        'total' => 37.50,
        'estado' => 'pendiente',
        'observaciones' => null,
    ]);

    $pedido->productos()->attach($producto->id, [
        'cantidad' => 3,
        'precio_unitario' => 12.50,
        'subtotal' => 37.50,
    ]);

    $loadedPedido = Pedido::query()
        ->whereKey($pedido->id)
        ->withDetails()
        ->firstOrFail();

    expect($loadedPedido->relationLoaded('cliente'))->toBeTrue()
        ->and($loadedPedido->relationLoaded('empleado'))->toBeTrue()
        ->and($loadedPedido->relationLoaded('productos'))->toBeTrue()
        ->and($loadedPedido->productos)->toHaveCount(1)
        ->and((int) $loadedPedido->productos->first()->pivot->cantidad)->toBe(3)
        ->and((float) $loadedPedido->productos->first()->pivot->precio_unitario)->toBe(12.5)
        ->and((float) $loadedPedido->productos->first()->pivot->subtotal)->toBe(37.5);

    $reloadedPedido = $pedido->fresh();
    $reloadedPedido->loadDetails();

    expect($reloadedPedido->relationLoaded('cliente'))->toBeTrue()
        ->and($reloadedPedido->relationLoaded('empleado'))->toBeTrue()
        ->and($reloadedPedido->relationLoaded('productos'))->toBeTrue();
});

it('rejects pedido creation with inactive cliente', function (): void {
    $cliente = Cliente::create([
        'nombre' => 'Ana',
        'apellido' => 'Paredes',
        'email' => 'ana.inactive.cliente@example.com',
        'estado' => 'inactivo',
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

    expect(fn () => app(CreatePedidoAction::class)->handle([
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'productos' => [['id' => $producto->id, 'cantidad' => 1]],
    ]))->toThrow(ValidationException::class);
});

it('rejects pedido creation with inactive empleado', function (): void {
    $cliente = Cliente::create([
        'nombre' => 'Ana',
        'apellido' => 'Paredes',
        'email' => 'ana.inactive.empleado@example.com',
        'estado' => 'activo',
    ]);

    $empleado = Empleado::create([
        'nombre' => 'Luis Gomez',
        'rol_operativo' => 'mesero',
        'estado' => 'inactivo',
    ]);

    $producto = Producto::create([
        'nombre' => 'Sandwich',
        'categoria' => 'desayuno',
        'stock' => 10,
        'estado' => 'activo',
        'precio' => 12.50,
    ]);

    expect(fn () => app(CreatePedidoAction::class)->handle([
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'productos' => [['id' => $producto->id, 'cantidad' => 1]],
    ]))->toThrow(ValidationException::class);
});

it('rejects pedido creation with inactive producto', function (): void {
    $cliente = Cliente::create([
        'nombre' => 'Ana',
        'apellido' => 'Paredes',
        'email' => 'ana.inactive.producto@example.com',
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
        'estado' => 'inactivo',
        'precio' => 12.50,
    ]);

    expect(fn () => app(CreatePedidoAction::class)->handle([
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'productos' => [['id' => $producto->id, 'cantidad' => 1]],
    ]))->toThrow(ValidationException::class);

    expect($producto->refresh()->stock)->toBe(10);
});

it('rejects pedido creation with non-positive cantidad before persisting pivot rows', function (): void {
    $cliente = Cliente::create([
        'nombre' => 'Ana',
        'apellido' => 'Paredes',
        'email' => 'ana.invalid.quantity@example.com',
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

    expect(fn () => app(CreatePedidoAction::class)->handle([
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'productos' => [['id' => $producto->id, 'cantidad' => 0]],
    ]))->toThrow(ValidationException::class);

    $this->assertDatabaseMissing('pedido_producto', [
        'producto_id' => $producto->id,
        'cantidad' => 0,
    ]);
});

it('rejects pedido creation with a negative product price before persisting pivot rows', function (): void {
    $cliente = Cliente::create([
        'nombre' => 'Ana',
        'apellido' => 'Paredes',
        'email' => 'ana.invalid.price@example.com',
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
        'precio' => -1,
    ]);

    expect(fn () => app(CreatePedidoAction::class)->handle([
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'productos' => [['id' => $producto->id, 'cantidad' => 1]],
    ]))->toThrow(ValidationException::class);

    $this->assertDatabaseMissing('pedido_producto', [
        'producto_id' => $producto->id,
        'precio_unitario' => -1,
    ]);
});
