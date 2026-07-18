<?php

use App\Enums\PaymentMethod;
use App\Enums\PedidoStatus;
use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;

function pedidoNumberPattern(): string
{
    return '/^PED-\d{6}-[A-Z0-9]{6}$/';
}

function pedidoTestCliente(array $attributes = []): Cliente
{
    return Cliente::create(array_merge([
        'nombre' => 'Cliente',
        'apellido' => 'Pedido',
        'email' => 'cliente.pedido.'.uniqid().'@example.com',
        'estado' => 'activo',
    ], $attributes));
}

function pedidoTestEmpleado(array $attributes = []): Empleado
{
    return Empleado::create(array_merge([
        'nombre' => 'Empleado Pedido',
        'rol_operativo' => 'mesero',
        'estado' => 'activo',
    ], $attributes));
}

function pedidoTestProducto(array $attributes = []): Producto
{
    return Producto::create(array_merge([
        'nombre' => 'Producto Pedido',
        'categoria' => 'desayuno',
        'stock' => 10,
        'estado' => 'activo',
        'precio' => 7.50,
    ], $attributes));
}

function pedidoTestPedido(Cliente $cliente, Empleado $empleado, array $attributes = []): Pedido
{
    return Pedido::create(array_merge([
        'numero_pedido' => 'PED-'.uniqid(),
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'metodo_pago' => 'efectivo',
        'fecha' => '2026-06-01',
        'hora' => '08:30:00',
        'total' => 10.00,
        'estado' => 'pendiente',
        'observaciones' => null,
    ], $attributes));
}

it('defines the canonical pedido status and payment method values', function (): void {
    expect(array_column(PedidoStatus::cases(), 'value'))->toBe([
        'pendiente', 'procesando', 'completado', 'cancelado',
    ])->and(array_column(PaymentMethod::cases(), 'value'))->toBe([
        'efectivo', 'tarjeta', 'transferencia', 'otro',
    ]);
});

it('creates a pedido through the trabajador HTTP flow and persists stock changes', function (): void {
    $user = User::factory()->create([
        'rol' => 'trabajador',
    ]);

    $cliente = Cliente::create([
        'nombre' => 'Ana',
        'apellido' => 'Paredes',
        'email' => 'ana.paredes@example.com',
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

    expect($pedido->numero_pedido)->toMatch(pedidoNumberPattern())
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

it('ignores forged producto precios in the trabajador HTTP store flow', function (): void {
    $user = User::factory()->create([
        'rol' => 'trabajador',
    ]);

    $cliente = Cliente::create([
        'nombre' => 'Ana',
        'apellido' => 'Paredes',
        'email' => 'ana.paredes.forged@example.com',
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
        ->from(route('trabajador.pedidos.create'))
        ->post(route('trabajador.pedidos.store'), [
            'cliente_id' => $cliente->id,
            'empleado_id' => $empleado->id,
            'metodo_pago' => 'efectivo',
            'observaciones' => 'Pedido con precio forjado',
            'productos' => [
                [
                    'producto_id' => $producto->id,
                    'cantidad' => 3,
                    'precio' => 1.00,
                ],
            ],
        ]);

    $pedido = Pedido::query()->with('productos')->firstOrFail();

    $response->assertRedirect(route('trabajador.pedidos.show', $pedido));

    expect((float) $pedido->total)->toBe(37.5)
        ->and((float) $pedido->productos->first()->pivot->precio_unitario)->toBe(12.5)
        ->and((float) $pedido->productos->first()->pivot->subtotal)->toBe(37.5);

    $this->assertDatabaseHas('pedido_producto', [
        'pedido_id' => $pedido->id,
        'producto_id' => $producto->id,
        'cantidad' => 3,
        'precio_unitario' => 12.5,
        'subtotal' => 37.5,
    ]);

    expect($producto->refresh()->stock)->toBe(7);
});

it('returns an error and does not persist a pedido when stock is insufficient in the trabajador HTTP store flow', function (): void {
    $user = User::factory()->create([
        'rol' => 'trabajador',
    ]);

    $cliente = Cliente::create([
        'nombre' => 'María',
        'apellido' => 'Lopez',
        'email' => 'maria.lopez.stock@example.com',
        'estado' => 'activo',
    ]);

    $empleado = Empleado::create([
        'nombre' => 'Luis Gomez',
        'rol_operativo' => 'mesero',
        'estado' => 'activo',
    ]);

    $producto = Producto::create([
        'nombre' => 'Cafe',
        'categoria' => 'bebida',
        'stock' => 2,
        'estado' => 'activo',
        'precio' => 5.00,
    ]);

    $response = $this->actingAs($user)
        ->from(route('trabajador.pedidos.create'))
        ->post(route('trabajador.pedidos.store'), [
            'cliente_id' => $cliente->id,
            'empleado_id' => $empleado->id,
            'metodo_pago' => 'efectivo',
            'observaciones' => 'Pedido sin stock suficiente',
            'productos' => [
                [
                    'producto_id' => $producto->id,
                    'cantidad' => 3,
                ],
            ],
        ]);

    $response->assertRedirect(route('trabajador.pedidos.create'));
    $response->assertSessionHas('error', '❌ Error: Stock insuficiente para Cafe. Disponible: 2');

    $this->assertDatabaseMissing('pedidos', [
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'observaciones' => 'Pedido sin stock suficiente',
    ]);

    expect($producto->refresh()->stock)->toBe(2);
});

it('duplicates a persisted pedido through the admin HTTP flow', function (): void {
    $user = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $cliente = Cliente::create([
        'nombre' => 'Ana',
        'apellido' => 'Paredes',
        'email' => 'ana.paredes.duplicado@example.com',
        'estado' => 'activo',
    ]);

    $empleado = Empleado::create([
        'nombre' => 'Luis Gomez',
        'rol_operativo' => 'mesero',
        'estado' => 'activo',
    ]);

    $producto = Producto::create([
        'nombre' => 'Jugo',
        'categoria' => 'bebida',
        'stock' => 10,
        'estado' => 'activo',
        'precio' => 8.00,
    ]);

    $this->actingAs($user)->post(route('admin.pedidos.store'), [
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'productos' => [
            [
                'producto_id' => $producto->id,
                'cantidad' => 2,
            ],
        ],
    ])->assertRedirect();

    $pedidoOriginal = Pedido::query()->with('productos')->firstOrFail();
    $numeroOriginal = $pedidoOriginal->numero_pedido;
    $precioOriginal = (float) $pedidoOriginal->productos->first()->pivot->precio_unitario;

    $producto->update([
        'precio' => 9.00,
    ]);

    $response = $this->actingAs($user)->post(route('admin.pedidos.duplicar', $pedidoOriginal));

    $pedidoDuplicado = Pedido::query()->whereKeyNot($pedidoOriginal->id)->with('productos')->latest('id')->firstOrFail();
    $precioNuevo = (float) $producto->refresh()->precio;

    $response->assertRedirect(route('admin.pedidos.show', $pedidoDuplicado));

    expect($pedidoDuplicado->numero_pedido)->not->toBe($numeroOriginal)
        ->and($pedidoDuplicado->numero_pedido)->toMatch(pedidoNumberPattern())
        ->and($precioOriginal)->toBe(8.0)
        ->and($precioNuevo)->toBe(9.0)
        ->and((float) $pedidoDuplicado->total)->toBe(18.0)
        ->and($pedidoDuplicado->productos)->toHaveCount(1)
        ->and((int) $pedidoDuplicado->productos->first()->pivot->cantidad)->toBe(2)
        ->and((float) $pedidoDuplicado->productos->first()->pivot->precio_unitario)->toBe(9.0)
        ->and((float) $pedidoDuplicado->productos->first()->pivot->subtotal)->toBe(18.0);

    $this->assertDatabaseHas('pedido_producto', [
        'pedido_id' => $pedidoDuplicado->id,
        'producto_id' => $producto->id,
        'cantidad' => 2,
        'precio_unitario' => 9.0,
        'subtotal' => 18.0,
    ]);

    expect($producto->refresh()->stock)->toBe(6);
});

it('rejects duplicated product ids in pedido create requests for both flows', function (): void {
    $cliente = Cliente::create([
        'nombre' => 'Marta',
        'apellido' => 'Lopez',
        'email' => 'marta.lopez@example.com',
        'estado' => 'activo',
    ]);

    $empleado = Empleado::create([
        'nombre' => 'Luis Gomez',
        'rol_operativo' => 'mesero',
        'estado' => 'activo',
    ]);

    $producto = Producto::create([
        'nombre' => 'Cafe',
        'categoria' => 'bebida',
        'stock' => 10,
        'estado' => 'activo',
        'precio' => 5.00,
    ]);

    $cases = [
        [
            'role' => 'trabajador',
            'from' => route('trabajador.pedidos.create'),
            'store' => route('trabajador.pedidos.store'),
        ],
        [
            'role' => 'administrador',
            'from' => route('admin.pedidos.create'),
            'store' => route('admin.pedidos.store'),
        ],
    ];

    foreach ($cases as $case) {
        $user = User::factory()->create([
            'rol' => $case['role'],
        ]);

        $response = $this->actingAs($user)
            ->from($case['from'])
            ->post($case['store'], [
                'cliente_id' => $cliente->id,
                'empleado_id' => $empleado->id,
                'metodo_pago' => 'efectivo',
                'productos' => [
                    [
                        'producto_id' => $producto->id,
                        'cantidad' => 1,
                    ],
                    [
                        'producto_id' => $producto->id,
                        'cantidad' => 2,
                    ],
                ],
            ]);

        $response->assertRedirect($case['from']);
        $response->assertSessionHasErrors(['productos.1.id']);
    }

    $this->assertDatabaseMissing('pedidos', [
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
    ]);
});

it('rejects invalid pedido updates through the admin HTTP flow', function (): void {
    $user = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $cliente = Cliente::create([
        'nombre' => 'Carla',
        'apellido' => 'Rios',
        'email' => 'carla.rios@example.com',
        'estado' => 'activo',
    ]);

    $empleado = Empleado::create([
        'nombre' => 'Pedro Gomez',
        'rol_operativo' => 'mesero',
        'estado' => 'activo',
    ]);

    $pedido = Pedido::create([
        'numero_pedido' => 'PED-202606-UPDATE',
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'metodo_pago' => 'efectivo',
        'fecha' => '2026-06-01',
        'hora' => '08:30:00',
        'total' => 0,
        'estado' => 'pendiente',
        'observaciones' => null,
    ]);

    $response = $this->actingAs($user)
        ->from(route('admin.pedidos.edit', $pedido))
        ->patch(route('admin.pedidos.update', $pedido), [
            'estado' => 'invalido',
            'observaciones' => 'Cambio de prueba',
        ]);

    $response->assertRedirect(route('admin.pedidos.edit', $pedido));
    $response->assertSessionHasErrors(['estado']);

    $this->assertDatabaseHas('pedidos', [
        'id' => $pedido->id,
        'estado' => 'pendiente',
    ]);
});

it('updates a pedido through the admin HTTP flow and restores stock on cancel', function (): void {
    $user = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $cliente = Cliente::create([
        'nombre' => 'Laura',
        'apellido' => 'Diaz',
        'email' => 'laura.diaz@example.com',
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
        'numero_pedido' => 'PED-202606-WORKER',
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

    expect(Route::has('trabajador.pedidos.edit'))->toBeFalse()
        ->and(Route::has('trabajador.pedidos.update'))->toBeFalse();

    $response = $this->actingAs($user)
        ->from(route('admin.pedidos.edit', $pedido))
        ->patch(route('admin.pedidos.update', $pedido), [
            'estado' => 'cancelado',
            'observaciones' => 'Pedido cancelado por prueba',
        ]);

    $response->assertRedirect(route('admin.pedidos.show', $pedido));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('pedidos', [
        'id' => $pedido->id,
        'estado' => 'cancelado',
        'observaciones' => 'Pedido cancelado por prueba',
    ]);

    expect($producto->refresh()->stock)->toBe(6);
});

it('filters worker pedidos by canonical fecha input', function (): void {
    $user = User::factory()->create([
        'rol' => 'trabajador',
    ]);

    $cliente = Cliente::create([
        'nombre' => 'Laura',
        'apellido' => 'Diaz',
        'email' => 'laura.diaz@example.com',
        'estado' => 'activo',
    ]);

    $empleado = Empleado::create([
        'nombre' => 'Carlos Perez',
        'rol_operativo' => 'cocinero',
        'estado' => 'activo',
    ]);

    $target = Pedido::create([
        'numero_pedido' => 'PED-202606-TARGET',
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'metodo_pago' => 'efectivo',
        'fecha' => '2026-06-01',
        'hora' => '08:30:00',
        'total' => 10.00,
        'estado' => 'pendiente',
        'observaciones' => null,
    ]);

    $other = Pedido::create([
        'numero_pedido' => 'PED-202606-OTHER',
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'metodo_pago' => 'efectivo',
        'fecha' => '2026-06-02',
        'hora' => '09:30:00',
        'total' => 12.00,
        'estado' => 'pendiente',
        'observaciones' => null,
    ]);

    Pedido::query()->whereKey($target->id)->update([
        'created_at' => '2026-07-01 10:00:00',
        'updated_at' => '2026-07-01 10:00:00',
    ]);

    Pedido::query()->whereKey($other->id)->update([
        'created_at' => '2026-06-01 10:00:00',
        'updated_at' => '2026-06-01 10:00:00',
    ]);

    $response = $this->actingAs($user)->get(route('trabajador.pedidos.index', ['fecha' => '2026-06-01']));

    $response->assertOk();
    $response->assertSee($target->numero_pedido);
    $response->assertDontSee($other->numero_pedido);
});

it('filters and orders pedidos by canonical fields for :role', function (string $role, string $routeName): void {
    Carbon::setTestNow('2026-06-02 10:00:00');

    $user = User::factory()->create([
        'rol' => $role,
    ]);

    $cliente = pedidoTestCliente([
        'nombre' => 'Rosa',
        'apellido' => 'Canonical',
    ]);
    $empleado = pedidoTestEmpleado([
        'nombre' => 'Nora Canonical',
        'rol_operativo' => 'cocinero',
    ]);

    $morning = pedidoTestPedido($cliente, $empleado, [
        'numero_pedido' => 'PED-202606-MORNING',
        'fecha' => '2026-06-02',
        'hora' => '08:15:00',
        'estado' => 'completado',
        'total' => 25.00,
        'created_at' => '2026-07-10 09:00:00',
        'updated_at' => '2026-07-10 09:00:00',
    ]);

    $noon = pedidoTestPedido($cliente, $empleado, [
        'numero_pedido' => 'PED-202606-NOON',
        'fecha' => '2026-06-02',
        'hora' => '12:45:00',
        'estado' => 'completado',
        'total' => 30.00,
        'created_at' => '2026-05-01 09:00:00',
        'updated_at' => '2026-05-01 09:00:00',
    ]);

    $createdAtOnlyMatch = pedidoTestPedido($cliente, $empleado, [
        'numero_pedido' => 'PED-202606-CREATED-ONLY',
        'fecha' => '2026-06-03',
        'hora' => '07:00:00',
        'estado' => 'completado',
        'total' => 99.00,
        'created_at' => '2026-06-02 10:00:00',
        'updated_at' => '2026-06-02 10:00:00',
    ]);

    $response = $this->actingAs($user)->get(route($routeName, [
        'search' => 'Canonical',
        'estado' => 'completado',
        'fecha_desde' => '2026-06-02',
        'fecha_hasta' => '2026-06-02',
        'empleado_id' => $empleado->id,
    ]));

    $response->assertOk();
    $response->assertSeeInOrder([$noon->numero_pedido, $morning->numero_pedido]);
    $response->assertSee('12:45');
    $response->assertSee('08:15');
    $response->assertSee('Nora Canonical');
    $response->assertDontSee($createdAtOnlyMatch->numero_pedido);

    Carbon::setTestNow();
})->with([
    'admin' => ['administrador', 'admin.pedidos.index'],
    'worker' => ['trabajador', 'trabajador.pedidos.index'],
]);

it('renders a filtered admin pedido CSV export link without unsupported controls', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);
    $empleado = pedidoTestEmpleado();
    $filters = [
        'search' => 'Export',
        'estado' => 'completado',
        'fecha_desde' => '2026-06-01',
        'fecha_hasta' => '2026-06-30',
        'fecha' => '2026-06-03',
        'empleado_id' => $empleado->id,
    ];

    $response = $this->actingAs($admin)->get(route('admin.pedidos.index', $filters));

    $response->assertOk()
        ->assertSee('href="'.e(route('admin.pedidos.exportar', $filters)).'"', false)
        ->assertSee('Exportar CSV')
        ->assertSee('<i class="fas fa-file-csv me-1" aria-hidden="true"></i>', false)
        ->assertDontSee('fa-file-excel')
        ->assertDontSee('fa-file-pdf')
        ->assertDontSee('> Excel<', false)
        ->assertDontSee('> PDF<', false);
});

it('calculates equivalent pedido stats from canonical fecha for :role', function (string $role, string $routeName): void {
    Carbon::setTestNow('2026-06-02 10:00:00');

    $user = User::factory()->create([
        'rol' => $role,
    ]);

    $cliente = pedidoTestCliente();
    $empleado = pedidoTestEmpleado();

    pedidoTestPedido($cliente, $empleado, [
        'numero_pedido' => 'PED-202606-FECHA-TODAY',
        'fecha' => '2026-06-02',
        'hora' => '08:00:00',
        'estado' => 'completado',
        'total' => 25.00,
        'created_at' => '2026-05-01 10:00:00',
        'updated_at' => '2026-05-01 10:00:00',
    ]);

    pedidoTestPedido($cliente, $empleado, [
        'numero_pedido' => 'PED-202606-CREATED-TODAY',
        'fecha' => '2026-06-01',
        'hora' => '08:00:00',
        'estado' => 'completado',
        'total' => 99.00,
        'created_at' => '2026-06-02 10:00:00',
        'updated_at' => '2026-06-02 10:00:00',
    ]);

    $response = $this->actingAs($user)->get(route($routeName, [
        'fecha' => '2026-06-02',
    ]));

    $response->assertOk();
    $response->assertSee('S/ 25.00');
    $response->assertDontSee('S/ 99.00');
    expect($response->viewData('estadisticas'))->toMatchArray([
        'total_pedidos' => 2,
        'pendientes' => 0,
        'completados' => 2,
        'ventas_hoy' => 25,
        'ventas_mes' => 124,
    ]);

    Carbon::setTestNow();
})->with([
    'admin' => ['administrador', 'admin.pedidos.index'],
    'worker' => ['trabajador', 'trabajador.pedidos.index'],
]);

it('exports filtered admin pedidos with canonical fields and CSV metadata', function (): void {
    $user = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $cliente = pedidoTestCliente([
        'nombre' => 'Export',
        'apellido' => 'Cliente',
    ]);
    $empleado = pedidoTestEmpleado([
        'nombre' => 'Export Worker',
        'rol_operativo' => 'barista',
    ]);

    $latest = pedidoTestPedido($cliente, $empleado, [
        'numero_pedido' => 'PED-202606-EXPORT-LATEST',
        'fecha' => '2026-06-03',
        'hora' => '11:30:00',
        'estado' => 'completado',
        'total' => 18.50,
        'created_at' => '2026-05-01 10:00:00',
        'updated_at' => '2026-05-01 10:00:00',
    ]);

    $earlier = pedidoTestPedido($cliente, $empleado, [
        'numero_pedido' => 'PED-202606-EXPORT-EARLIER',
        'fecha' => '2026-06-03',
        'hora' => '09:45:00',
        'estado' => 'completado',
        'total' => 12.00,
        'created_at' => '2026-07-01 10:00:00',
        'updated_at' => '2026-07-01 10:00:00',
    ]);

    pedidoTestPedido($cliente, $empleado, [
        'numero_pedido' => 'PED-202606-EXPORT-PENDING',
        'fecha' => '2026-06-03',
        'hora' => '12:00:00',
        'estado' => 'pendiente',
        'total' => 99.00,
    ]);

    $otherEmpleado = pedidoTestEmpleado(['nombre' => 'Other Export Worker']);
    pedidoTestPedido($cliente, $otherEmpleado, [
        'numero_pedido' => 'PED-202606-EXPORT-OTHER-WORKER',
        'fecha' => '2026-06-03',
        'hora' => '10:00:00',
        'estado' => 'completado',
    ]);

    pedidoTestPedido($cliente, $empleado, [
        'numero_pedido' => 'PED-202606-EXPORT-OTHER-DATE',
        'fecha' => '2026-06-04',
        'hora' => '10:00:00',
        'estado' => 'completado',
    ]);

    $response = $this->actingAs($user)->get(route('admin.pedidos.exportar', [
        'search' => 'Export',
        'estado' => 'completado',
        'fecha_desde' => '2026-06-03',
        'fecha_hasta' => '2026-06-03',
        'fecha' => '2026-06-03',
        'empleado_id' => $empleado->id,
    ]));

    $response->assertOk()
        ->assertHeader('Content-Type', 'text/csv; charset=UTF-8')
        ->assertHeader('Content-Disposition');

    $csv = $response->streamedContent();

    expect($csv)->toStartWith("\xEF\xBB\xBF")
        ->and($csv)->toContain('Número,Cliente,Empleado,Rol,Fecha,Total,Estado')
        ->and($csv)->toContain('"Export Worker",barista,"03/06/2026 11:30",18.50,Completado')
        ->and($csv)->toContain('"Export Worker",barista,"03/06/2026 09:45",12.00,Completado')
        ->and($csv)->not->toContain('PED-202606-EXPORT-PENDING')
        ->and($csv)->not->toContain('PED-202606-EXPORT-OTHER-WORKER')
        ->and($csv)->not->toContain('PED-202606-EXPORT-OTHER-DATE');

    expect(strpos($csv, $latest->numero_pedido))->toBeLessThan(strpos($csv, $earlier->numero_pedido));
});

it('renders pedido detail pages with canonical fecha hora and pivot product data', function (): void {
    $cliente = pedidoTestCliente([
        'nombre' => 'Detalle',
        'apellido' => 'Cliente',
    ]);
    $empleado = pedidoTestEmpleado([
        'nombre' => 'Detalle Worker',
        'rol_operativo' => 'repartidor',
    ]);
    $producto = pedidoTestProducto([
        'nombre' => 'Combo Detalle',
        'precio' => 6.50,
    ]);
    $pedido = pedidoTestPedido($cliente, $empleado, [
        'numero_pedido' => 'PED-202606-DETAIL',
        'fecha' => '2026-06-04',
        'hora' => '16:20:00',
        'total' => 19.50,
    ]);

    $pedido->productos()->attach($producto->id, [
        'cantidad' => 3,
        'precio_unitario' => 6.50,
        'subtotal' => 19.50,
    ]);

    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);
    $worker = User::factory()->create([
        'rol' => 'trabajador',
    ]);

    $adminResponse = $this->actingAs($admin)->get(route('admin.pedidos.show', $pedido));
    $workerResponse = $this->actingAs($worker)->get(route('trabajador.pedidos.show', $pedido));

    $adminResponse->assertOk();
    $adminResponse->assertSee('04/06/2026');
    $adminResponse->assertSee('16:20');
    $adminResponse->assertSee('Repartidor');
    $adminResponse->assertSee('Combo Detalle');
    $adminResponse->assertSee('3');
    $adminResponse->assertSee('S/ 6.50');
    $adminResponse->assertSee('S/ 19.50');

    $workerResponse->assertOk();
    $workerResponse->assertSee('04/06/2026');
    $workerResponse->assertSee('16:20');
    $workerResponse->assertSee('Combo Detalle');
    $workerResponse->assertSee('3');
    $workerResponse->assertSee('S/ 6.50');
    $workerResponse->assertSee('S/ 19.50');
});

it('renders admin pedido edit page with pivot product data after loading details', function (): void {
    $cliente = pedidoTestCliente([
        'nombre' => 'Edit',
        'apellido' => 'Cliente',
    ]);
    $empleado = pedidoTestEmpleado([
        'nombre' => 'Edit Worker',
    ]);
    $producto = pedidoTestProducto([
        'nombre' => 'Combo Edit Contract',
        'precio' => 99.00,
        'stock' => 8,
    ]);
    $pedido = pedidoTestPedido($cliente, $empleado, [
        'numero_pedido' => 'PED-202606-EDIT-CONTRACT',
        'total' => 19.50,
    ]);

    $pedido->productos()->attach($producto->id, [
        'cantidad' => 3,
        'precio_unitario' => 6.50,
        'subtotal' => 19.50,
    ]);

    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $response = $this->actingAs($admin)->get(route('admin.pedidos.edit', $pedido));

    $response->assertOk();
    $response->assertSee('Combo Edit Contract');
    $response->assertSee('value="3"', false);
    $response->assertSee('S/ 6.50');
    $response->assertSee('S/ 19.50');
});

it('renders admin pedido print page with pivot product data after loading details', function (): void {
    $cliente = pedidoTestCliente([
        'nombre' => 'Print',
        'apellido' => 'Cliente',
    ]);
    $empleado = pedidoTestEmpleado([
        'nombre' => 'Print Worker',
    ]);
    $producto = pedidoTestProducto([
        'nombre' => 'Combo Print Contract',
        'precio' => 99.00,
    ]);
    $pedido = pedidoTestPedido($cliente, $empleado, [
        'numero_pedido' => 'PED-202606-PRINT-CONTRACT',
        'total' => 19.50,
    ]);

    $pedido->productos()->attach($producto->id, [
        'cantidad' => 3,
        'precio_unitario' => 6.50,
        'subtotal' => 19.50,
    ]);

    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $response = $this->actingAs($admin)->get(route('admin.pedidos.imprimir', $pedido));

    $response->assertOk();
    $response->assertSee('Combo Print Contract');
    $response->assertSee('3');
    $response->assertSee('S/ 6.50');
    $response->assertSee('S/ 19.50');
});
