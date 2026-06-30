<?php

declare(strict_types=1);

use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;

function createPedidoDuplicationFixture(int $stock = 10, float $originalPrice = 10.00): array
{
    $cliente = Cliente::create([
        'nombre' => 'Ana',
        'apellido' => 'Paredes',
        'email' => 'ana.paredes.duplication.' . uniqid() . '@example.com',
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
        'stock' => $stock,
        'estado' => 'activo',
        'precio' => $originalPrice,
    ]);

    $pedido = Pedido::create([
        'numero_pedido' => 'PED-202606-DUP' . substr(uniqid(), -3),
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'metodo_pago' => 'efectivo',
        'fecha' => '2026-06-01',
        'hora' => '08:30:00',
        'total' => $originalPrice * 2,
        'estado' => 'completado',
        'observaciones' => 'Original pedido',
    ]);

    $pedido->productos()->attach($producto->id, [
        'cantidad' => 2,
        'precio_unitario' => $originalPrice,
        'subtotal' => $originalPrice * 2,
    ]);

    return [$pedido->fresh('productos'), $producto];
}

it('duplicates a pedido with a fresh number, pending state, stock reservation, and current prices', function (): void {
    [$pedido, $producto] = createPedidoDuplicationFixture(originalPrice: 10.00);

    $producto->update([
        'precio' => 12.50,
    ]);

    $duplicatedPedido = $pedido->duplicarConProductos();

    expect($duplicatedPedido->id)->not->toBe($pedido->id)
        ->and($duplicatedPedido->numero_pedido)->not->toBe($pedido->numero_pedido)
        ->and($duplicatedPedido->numero_pedido)->toMatch('/^PED-\d{6}-[A-Z0-9]{6}$/')
        ->and($duplicatedPedido->estado)->toBe('pendiente')
        ->and((float) $duplicatedPedido->total)->toBe(25.0)
        ->and($duplicatedPedido->productos)->toHaveCount(1)
        ->and((int) $duplicatedPedido->productos->first()->pivot->cantidad)->toBe(2)
        ->and((float) $duplicatedPedido->productos->first()->pivot->precio_unitario)->toBe(12.5)
        ->and((float) $duplicatedPedido->productos->first()->pivot->subtotal)->toBe(25.0);

    $this->assertDatabaseHas('pedidos', [
        'id' => $duplicatedPedido->id,
        'cliente_id' => $pedido->cliente_id,
        'empleado_id' => $pedido->empleado_id,
        'estado' => 'pendiente',
        'total' => 25.0,
    ]);

    $this->assertDatabaseHas('pedido_producto', [
        'pedido_id' => $duplicatedPedido->id,
        'producto_id' => $producto->id,
        'cantidad' => 2,
        'precio_unitario' => 12.5,
        'subtotal' => 25.0,
    ]);

    expect($producto->refresh()->stock)->toBe(8);
});

it('rolls back duplication when product stock is insufficient', function (): void {
    [$pedido, $producto] = createPedidoDuplicationFixture(stock: 1);

    expect(fn (): Pedido => $pedido->duplicarConProductos())
        ->toThrow(Exception::class, 'Stock insuficiente para Sandwich');

    $this->assertDatabaseCount('pedidos', 1);
    $this->assertDatabaseCount('pedido_producto', 1);

    expect($producto->refresh()->stock)->toBe(1);
});

it('duplicates a pedido through the admin HTTP flow', function (): void {
    $user = User::factory()->create([
        'rol' => 'administrador',
    ]);
    [$pedido, $producto] = createPedidoDuplicationFixture();

    $response = $this->actingAs($user)
        ->post(route('admin.pedidos.duplicar', $pedido));

    $duplicatedPedido = Pedido::query()
        ->whereKeyNot($pedido->id)
        ->with('productos')
        ->firstOrFail();

    $response->assertRedirect(route('admin.pedidos.show', $duplicatedPedido));

    expect($duplicatedPedido->numero_pedido)->not->toBe($pedido->numero_pedido)
        ->and($duplicatedPedido->estado)->toBe('pendiente')
        ->and((float) $duplicatedPedido->total)->toBe(20.0)
        ->and($producto->refresh()->stock)->toBe(8);
});
