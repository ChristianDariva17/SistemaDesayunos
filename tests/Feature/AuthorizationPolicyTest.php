<?php

declare(strict_types=1);

use App\Models\Cliente;
use App\Models\DailyCashClosure;
use App\Models\Empleado;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

function authorizationUser(string $role): User
{
    return User::factory()->create([
        'rol' => $role,
    ]);
}

function authorizationUserWithRole(string $role): User
{
    return User::factory()->make([
        'rol' => $role,
    ]);
}

function authorizationCliente(): Cliente
{
    return Cliente::create([
        'nombre' => 'Policy Cliente',
        'estado' => 'activo',
    ]);
}

function authorizationEmpleado(): Empleado
{
    return Empleado::create([
        'nombre' => 'Policy Empleado',
        'rol_operativo' => 'mesero',
        'estado' => 'activo',
    ]);
}

function authorizationProducto(): Producto
{
    return Producto::create([
        'nombre' => 'Policy Producto',
        'categoria' => 'bebida',
        'precio' => 10.00,
        'stock' => 5,
        'estado' => 'activo',
    ]);
}

function authorizationPedido(): Pedido
{
    return Pedido::create([
        'cliente_id' => authorizationCliente()->id,
        'empleado_id' => authorizationEmpleado()->id,
        'fecha' => now()->toDateString(),
        'hora' => now()->format('H:i:s'),
        'total' => 10.00,
        'estado' => 'pendiente',
    ]);
}

/**
 * @return array{cliente: Cliente, empleado: Empleado, producto: Producto}
 */
function authorizationOrderFixture(): array
{
    return [
        'cliente' => authorizationCliente(),
        'empleado' => authorizationEmpleado(),
        'producto' => authorizationProducto(),
    ];
}

/**
 * @return array<string, mixed>
 */
function authorizationPedidoPayload(Cliente $cliente, Empleado $empleado, Producto $producto): array
{
    return [
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'metodo_pago' => 'efectivo',
        'observaciones' => 'Authorization HTTP flow',
        'productos' => [
            [
                'producto_id' => $producto->id,
                'cantidad' => 1,
            ],
        ],
    ];
}

/**
 * @return array<string, mixed>
 */
function authorizationProductoPayload(array $overrides = []): array
{
    return array_merge([
        'nombre' => 'Authorization Producto '.uniqid(),
        'categoria' => 'bebida',
        'precio' => 15.50,
        'stock' => 8,
        'stock_minimo' => 1,
        'estado' => 'activo',
    ], $overrides);
}

/**
 * @return array<string, mixed>
 */
function authorizationClientePayload(array $overrides = []): array
{
    return array_merge([
        'nombre' => 'Authorization Cliente '.uniqid(),
        'apellido' => 'Policy',
        'telefono' => '555 0101',
        'email' => null,
        'direccion' => 'Authorization Street 123',
        'fecha_nacimiento' => null,
        'estado' => 'activo',
        'notas' => 'Authorization cliente flow',
    ], $overrides);
}

/**
 * @return array<string, mixed>
 */
function authorizationEmpleadoPayload(array $overrides = []): array
{
    return array_merge([
        'user_id' => null,
        'nombre' => 'Authorization Empleado '.uniqid(),
        'rol_operativo' => 'mesero',
        'telefono' => '555 0202',
        'observaciones' => 'Authorization empleado flow',
        'estado' => 'activo',
    ], $overrides);
}

it('allows staff to view and create pedidos but only administrators can update pedido status', function (): void {
    $admin = authorizationUser('administrador');
    $worker = authorizationUser('trabajador');
    $pedido = authorizationPedido();

    expect(Gate::forUser($admin)->allows('view', $pedido))->toBeTrue()
        ->and(Gate::forUser($worker)->allows('view', $pedido))->toBeTrue()
        ->and(Gate::forUser($worker)->allows('create', Pedido::class))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('update', $pedido))->toBeTrue()
        ->and(Gate::forUser($worker)->denies('update', $pedido))->toBeTrue()
        ->and(Gate::forUser($worker)->denies('changeStatus', $pedido))->toBeTrue();
});

it('honors legacy role aliases in policy checks through shared normalization', function (): void {
    $adminAlias = authorizationUserWithRole('admin');
    $workerAlias = authorizationUserWithRole('empleado');
    $pedido = authorizationPedido();
    $producto = authorizationProducto();

    expect(Gate::forUser($adminAlias)->allows('update', $pedido))->toBeTrue()
        ->and(Gate::forUser($adminAlias)->allows('create', Producto::class))->toBeTrue()
        ->and(Gate::forUser($workerAlias)->allows('create', Pedido::class))->toBeTrue()
        ->and(Gate::forUser($workerAlias)->allows('duplicate', $pedido))->toBeTrue()
        ->and(Gate::forUser($workerAlias)->allows('delete', $pedido))->toBeTrue()
        ->and(Gate::forUser($workerAlias)->denies('update', $producto))->toBeTrue();
});

it('allows workers to read products and clients while denying their write operations', function (): void {
    $worker = authorizationUser('trabajador');
    $producto = authorizationProducto();
    $cliente = authorizationCliente();

    expect(Gate::forUser($worker)->allows('view', $producto))->toBeTrue()
        ->and(Gate::forUser($worker)->allows('view', $cliente))->toBeTrue()
        ->and(Gate::forUser($worker)->denies('create', Producto::class))->toBeTrue()
        ->and(Gate::forUser($worker)->denies('update', $producto))->toBeTrue()
        ->and(Gate::forUser($worker)->denies('delete', $producto))->toBeTrue()
        ->and(Gate::forUser($worker)->denies('create', Cliente::class))->toBeTrue()
        ->and(Gate::forUser($worker)->denies('update', $cliente))->toBeTrue()
        ->and(Gate::forUser($worker)->denies('delete', $cliente))->toBeTrue();
});

it('restricts empleado and daily cash closure operations to administrators', function (): void {
    $admin = authorizationUser('administrador');
    $worker = authorizationUser('trabajador');
    $empleado = authorizationEmpleado();
    $closure = DailyCashClosure::create([
        'business_date' => now()->toDateString(),
        'total_orders' => 0,
        'total_revenue' => 0,
        'settled_order_count' => 0,
        'pending_order_count' => 0,
        'cancelled_order_count' => 0,
        'payment_method_totals' => [],
        'closed_by_user_id' => $admin->id,
        'closed_at' => now(),
    ]);

    expect(Gate::forUser($admin)->allows('create', Empleado::class))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('update', $empleado))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('create', DailyCashClosure::class))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('view', $closure))->toBeTrue()
        ->and(Gate::forUser($worker)->denies('viewAny', Empleado::class))->toBeTrue()
        ->and(Gate::forUser($worker)->denies('create', Empleado::class))->toBeTrue()
        ->and(Gate::forUser($worker)->denies('update', $empleado))->toBeTrue()
        ->and(Gate::forUser($worker)->denies('create', DailyCashClosure::class))->toBeTrue()
        ->and(Gate::forUser($worker)->denies('view', $closure))->toBeTrue();
});

it('allows administrators through product create store and update HTTP flows', function (): void {
    $admin = authorizationUser('administrador');
    $producto = authorizationProducto();

    $this->actingAs($admin)
        ->get(route('admin.productos.create'))
        ->assertOk();

    $storeResponse = $this->actingAs($admin)
        ->post(route('admin.productos.store'), authorizationProductoPayload([
            'nombre' => 'Authorization Created Producto',
        ]));

    $storeResponse->assertRedirect(route('admin.productos.index'));
    $storeResponse->assertSessionHas('success');

    $this->assertDatabaseHas('productos', [
        'nombre' => 'Authorization Created Producto',
        'categoria' => 'bebida',
    ]);

    $updateResponse = $this->actingAs($admin)
        ->put(route('admin.productos.update', $producto), authorizationProductoPayload([
            'nombre' => 'Authorization Updated Producto',
            'stock' => $producto->stock,
        ]));

    $updateResponse->assertRedirect(route('admin.productos.show', $producto));
    $updateResponse->assertSessionHas('success');

    $this->assertDatabaseHas('productos', [
        'id' => $producto->id,
        'nombre' => 'Authorization Updated Producto',
    ]);
});

it('denies workers from admin product create store and update HTTP flows', function (): void {
    $worker = authorizationUser('trabajador');
    $producto = authorizationProducto();

    $this->actingAs($worker)
        ->get(route('admin.productos.create'))
        ->assertForbidden();

    $this->actingAs($worker)
        ->post(route('admin.productos.store'), authorizationProductoPayload([
            'nombre' => 'Forbidden Worker Producto',
        ]))
        ->assertForbidden();

    $this->actingAs($worker)
        ->put(route('admin.productos.update', $producto), authorizationProductoPayload([
            'nombre' => 'Forbidden Update Producto',
        ]))
        ->assertForbidden();

    $this->assertDatabaseMissing('productos', [
        'nombre' => 'Forbidden Worker Producto',
    ]);
    $this->assertDatabaseHas('productos', [
        'id' => $producto->id,
        'nombre' => 'Policy Producto',
    ]);
});

it('allows administrators through client create store and update HTTP flows', function (): void {
    $admin = authorizationUser('administrador');
    $cliente = authorizationCliente();

    $this->actingAs($admin)
        ->get(route('admin.clientes.create'))
        ->assertOk();

    $storeResponse = $this->actingAs($admin)
        ->post(route('admin.clientes.store'), authorizationClientePayload([
            'nombre' => 'Authorization Created Cliente',
            'email' => 'created-cliente@example.test',
        ]));

    $storeResponse->assertRedirect(route('admin.clientes.index'));
    $storeResponse->assertSessionHas('success');

    $this->assertDatabaseHas('clientes', [
        'nombre' => 'Authorization Created Cliente',
        'email' => 'created-cliente@example.test',
    ]);

    $updateResponse = $this->actingAs($admin)
        ->put(route('admin.clientes.update', $cliente), authorizationClientePayload([
            'nombre' => 'Authorization Updated Cliente',
            'email' => 'updated-cliente@example.test',
        ]));

    $updateResponse->assertRedirect(route('admin.clientes.show', $cliente));
    $updateResponse->assertSessionHas('success');

    $this->assertDatabaseHas('clientes', [
        'id' => $cliente->id,
        'nombre' => 'Authorization Updated Cliente',
        'email' => 'updated-cliente@example.test',
    ]);
});

it('denies workers from admin client create store and update HTTP flows without changing data', function (): void {
    $worker = authorizationUser('trabajador');
    $cliente = authorizationCliente();
    $initialCount = Cliente::count();

    $this->actingAs($worker)
        ->get(route('admin.clientes.create'))
        ->assertForbidden();

    $this->actingAs($worker)
        ->post(route('admin.clientes.store'), authorizationClientePayload([
            'nombre' => 'Forbidden Worker Cliente',
            'email' => 'forbidden-cliente@example.test',
        ]))
        ->assertForbidden();

    $this->actingAs($worker)
        ->put(route('admin.clientes.update', $cliente), authorizationClientePayload([
            'nombre' => 'Forbidden Update Cliente',
            'email' => 'forbidden-update-cliente@example.test',
        ]))
        ->assertForbidden();

    expect(Cliente::count())->toBe($initialCount);
    $this->assertDatabaseMissing('clientes', [
        'nombre' => 'Forbidden Worker Cliente',
    ]);
    $this->assertDatabaseHas('clientes', [
        'id' => $cliente->id,
        'nombre' => 'Policy Cliente',
    ]);
});

it('allows administrators through employee create store and update HTTP flows', function (): void {
    $admin = authorizationUser('administrador');
    $empleado = authorizationEmpleado();

    $this->actingAs($admin)
        ->get(route('admin.empleados.create'))
        ->assertOk();

    $storeResponse = $this->actingAs($admin)
        ->post(route('admin.empleados.store'), authorizationEmpleadoPayload([
            'nombre' => 'Authorization Created Empleado',
        ]));

    $storeResponse->assertRedirect(route('admin.empleados.index'));
    $storeResponse->assertSessionHas('success');

    $this->assertDatabaseHas('empleados', [
        'nombre' => 'Authorization Created Empleado',
        'rol_operativo' => 'mesero',
    ]);

    $updateResponse = $this->actingAs($admin)
        ->put(route('admin.empleados.update', $empleado), authorizationEmpleadoPayload([
            'nombre' => 'Authorization Updated Empleado',
            'rol_operativo' => 'cajero',
        ]));

    $updateResponse->assertRedirect(route('admin.empleados.show', $empleado));
    $updateResponse->assertSessionHas('success');

    $this->assertDatabaseHas('empleados', [
        'id' => $empleado->id,
        'nombre' => 'Authorization Updated Empleado',
        'rol_operativo' => 'cajero',
    ]);
});

it('denies workers from admin employee create store and update HTTP flows without changing data', function (): void {
    $worker = authorizationUser('trabajador');
    $empleado = authorizationEmpleado();
    $initialCount = Empleado::count();

    $this->actingAs($worker)
        ->get(route('admin.empleados.create'))
        ->assertForbidden();

    $this->actingAs($worker)
        ->post(route('admin.empleados.store'), authorizationEmpleadoPayload([
            'nombre' => 'Forbidden Worker Empleado',
        ]))
        ->assertForbidden();

    $this->actingAs($worker)
        ->put(route('admin.empleados.update', $empleado), authorizationEmpleadoPayload([
            'nombre' => 'Forbidden Update Empleado',
            'rol_operativo' => 'chef',
        ]))
        ->assertForbidden();

    expect(Empleado::count())->toBe($initialCount);
    $this->assertDatabaseMissing('empleados', [
        'nombre' => 'Forbidden Worker Empleado',
    ]);
    $this->assertDatabaseHas('empleados', [
        'id' => $empleado->id,
        'nombre' => 'Policy Empleado',
        'rol_operativo' => 'mesero',
    ]);
});

it('allows admin and worker pedido store HTTP flows through FormRequest authorization', function (string $role, string $routePrefix): void {
    $user = authorizationUser($role);
    ['cliente' => $cliente, 'empleado' => $empleado, 'producto' => $producto] = authorizationOrderFixture();

    $response = $this->actingAs($user)
        ->from(route("{$routePrefix}.pedidos.create"))
        ->post(route("{$routePrefix}.pedidos.store"), authorizationPedidoPayload($cliente, $empleado, $producto));

    $pedido = Pedido::query()->firstOrFail();

    $response->assertRedirect(route("{$routePrefix}.pedidos.show", $pedido));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('pedidos', [
        'id' => $pedido->id,
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'estado' => 'pendiente',
    ]);
})->with([
    'admin' => ['administrador', 'admin'],
    'worker' => ['trabajador', 'trabajador'],
]);

it('allows administrators and denies workers on pedido update HTTP flows', function (): void {
    $admin = authorizationUser('administrador');
    $worker = authorizationUser('trabajador');
    $pedido = authorizationPedido();

    $adminResponse = $this->actingAs($admin)
        ->from(route('admin.pedidos.edit', $pedido))
        ->put(route('admin.pedidos.update', $pedido), [
            'estado' => 'procesando',
            'observaciones' => 'Admin updated through FormRequest',
        ]);

    $adminResponse->assertRedirect(route('admin.pedidos.show', $pedido));
    $adminResponse->assertSessionHas('success');

    $this->assertDatabaseHas('pedidos', [
        'id' => $pedido->id,
        'estado' => 'procesando',
        'observaciones' => 'Admin updated through FormRequest',
    ]);

    $workerResponse = $this->actingAs($worker)
        ->from(route('admin.pedidos.edit', $pedido))
        ->put(route('admin.pedidos.update', $pedido), [
            'estado' => 'completado',
            'observaciones' => 'Forbidden worker update',
        ]);

    $workerResponse->assertForbidden();

    $this->assertDatabaseHas('pedidos', [
        'id' => $pedido->id,
        'estado' => 'procesando',
        'observaciones' => 'Admin updated through FormRequest',
    ]);
});

it('documents that workers can duplicate and delete pedidos through their existing routes', function (): void {
    $worker = authorizationUser('trabajador');
    $pedidoToDuplicate = authorizationPedido();
    $producto = authorizationProducto();
    $pedidoToDuplicate->productos()->attach($producto->id, [
        'cantidad' => 1,
        'precio_unitario' => 10.00,
        'subtotal' => 10.00,
    ]);

    $duplicateResponse = $this->actingAs($worker)
        ->post(route('trabajador.pedidos.duplicar', $pedidoToDuplicate));

    $duplicatedPedido = Pedido::query()
        ->whereKeyNot($pedidoToDuplicate->id)
        ->firstOrFail();

    $duplicateResponse->assertRedirect(route('trabajador.pedidos.show', $duplicatedPedido));
    $duplicateResponse->assertSessionHas('success');

    $pedidoToDelete = authorizationPedido();

    $deleteResponse = $this->actingAs($worker)
        ->from(route('trabajador.pedidos.show', $pedidoToDelete))
        ->delete(route('trabajador.pedidos.destroy', $pedidoToDelete));

    $deleteResponse->assertRedirect(route('trabajador.pedidos.index'));
    $deleteResponse->assertSessionHas('success');

    $this->assertDatabaseMissing('pedidos', [
        'id' => $pedidoToDelete->id,
    ]);
});
