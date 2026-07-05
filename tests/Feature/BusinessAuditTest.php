<?php

declare(strict_types=1);

use App\Actions\Pedido\CreatePedidoAction;
use App\Models\Audit;
use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;

it('audits meaningful product business changes with the authenticated actor', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);
    $producto = Producto::create([
        'nombre' => 'Audited coffee',
        'categoria' => 'bebida',
        'precio' => 10.00,
        'stock' => 5,
        'estado' => 'activo',
    ]);

    $this->actingAs($admin);

    $producto->update([
        'precio' => 12.50,
        'stock' => 8,
        'estado' => 'inactivo',
    ]);

    $audit = Audit::query()
        ->where('auditable_type', Producto::class)
        ->where('auditable_id', $producto->id)
        ->where('action', 'updated')
        ->latest('id')
        ->firstOrFail();

    expect($audit->user_id)->toBe($admin->id)
        ->and($audit->auditable_table)->toBe('productos')
        ->and($audit->old_values)->toMatchArray([
            'precio' => '10.00',
            'stock' => 5,
            'estado' => 'activo',
        ])
        ->and($audit->new_values)->toMatchArray([
            'precio' => '12.50',
            'stock' => 8,
            'estado' => 'inactivo',
        ]);
});

it('does not audit product no-op saves', function (): void {
    $producto = Producto::create([
        'nombre' => 'Stable audited coffee',
        'categoria' => 'bebida',
        'precio' => 10.00,
        'stock' => 5,
        'estado' => 'activo',
    ]);
    $auditCount = Audit::query()->count();

    $producto->save();

    expect(Audit::query()->count())->toBe($auditCount);
});

it('audits pedido status and total changes without requiring an actor', function (): void {
    $cliente = Cliente::create([
        'nombre' => 'Audit client',
        'estado' => 'activo',
    ]);
    $nuevoCliente = Cliente::create([
        'nombre' => 'Updated audit client',
        'estado' => 'activo',
    ]);
    $empleado = Empleado::create([
        'nombre' => 'Audit employee',
        'rol_operativo' => 'ventas',
        'estado' => 'activo',
    ]);
    $nuevoEmpleado = Empleado::create([
        'nombre' => 'Updated audit employee',
        'rol_operativo' => 'ventas',
        'estado' => 'activo',
    ]);
    $producto = Producto::create([
        'nombre' => 'Pedido audited coffee',
        'categoria' => 'bebida',
        'precio' => 10.00,
        'stock' => 5,
        'estado' => 'activo',
    ]);

    $pedido = app(CreatePedidoAction::class)->handle([
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'metodo_pago' => 'efectivo',
        'productos' => [
            ['id' => $producto->id, 'cantidad' => 2],
        ],
    ]);
    $auditCount = Audit::query()->count();

    $pedido->update([
        'cliente_id' => $nuevoCliente->id,
        'empleado_id' => $nuevoEmpleado->id,
        'metodo_pago' => 'tarjeta',
        'estado' => 'procesando',
        'total' => 25.00,
    ]);

    $audit = Audit::query()
        ->where('auditable_type', Pedido::class)
        ->where('auditable_id', $pedido->id)
        ->where('action', 'updated')
        ->latest('id')
        ->firstOrFail();

    expect(Audit::query()->count())->toBe($auditCount + 1)
        ->and($audit->user_id)->toBeNull()
        ->and($audit->auditable_table)->toBe('pedidos')
        ->and($audit->old_values)->toMatchArray([
            'cliente_id' => $cliente->id,
            'empleado_id' => $empleado->id,
            'metodo_pago' => 'efectivo',
            'estado' => 'pendiente',
            'total' => '20.00',
        ])
        ->and($audit->new_values)->toMatchArray([
            'cliente_id' => $nuevoCliente->id,
            'empleado_id' => $nuevoEmpleado->id,
            'metodo_pago' => 'tarjeta',
            'estado' => 'procesando',
            'total' => '25.00',
        ]);
});
