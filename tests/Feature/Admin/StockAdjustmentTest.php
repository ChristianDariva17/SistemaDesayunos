<?php

declare(strict_types=1);

use App\Models\Producto;
use App\Models\StockMovimiento;
use App\Models\User;
use App\Support\InventoryLimits;

function stockAdjustmentProducto(array $attributes = []): Producto
{
    return Producto::create(array_merge([
        'nombre' => 'Adjustment Coffee',
        'categoria' => 'desayuno',
        'stock' => 10,
        'estado' => 'activo',
        'precio' => 12.50,
    ], $attributes));
}

it('requires an administrator to access stock adjustment routes', function (): void {
    $worker = User::factory()->create([
        'rol' => 'trabajador',
    ]);

    $this->get(route('admin.stock-adjustments.create'))
        ->assertRedirect(route('login', absolute: false));

    $this->post(route('admin.stock-adjustments.store'), [])
        ->assertRedirect(route('login', absolute: false));

    $this->actingAs($worker)
        ->get(route('admin.stock-adjustments.create'))
        ->assertForbidden();

    $this->actingAs($worker)
        ->post(route('admin.stock-adjustments.store'), [])
        ->assertForbidden();
});

it('renders the stock adjustment form for administrators', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);
    stockAdjustmentProducto(['nombre' => 'Adjustment Toast', 'stock' => 7]);

    $this->actingAs($admin)
        ->get(route('admin.stock-adjustments.create'))
        ->assertOk()
        ->assertSee('Registrar Ajuste de Stock')
        ->assertSee('Adjustment Toast')
        ->assertSee('stock actual: 7');
});

it('sets product stock upward and records an ajuste ledger movement', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);
    $producto = stockAdjustmentProducto([
        'stock' => 5,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.stock-adjustments.store'), [
            'producto_id' => $producto->id,
            'stock_nuevo' => 12,
            'motivo' => 'Physical count correction',
        ])
        ->assertRedirect(route('admin.reportes.stock-movimientos', [
            'producto_id' => $producto->id,
        ]));

    expect($producto->refresh()->stock)->toBe(12);

    $this->assertDatabaseHas('stock_movimientos', [
        'producto_id' => $producto->id,
        'pedido_id' => null,
        'pedido_numero' => null,
        'user_id' => $admin->id,
        'tipo' => StockMovimiento::TIPO_AJUSTE,
        'cantidad' => 7,
        'stock_anterior' => 5,
        'stock_nuevo' => 12,
        'motivo' => 'Physical count correction',
    ]);
});

it('sets product stock downward and records the absolute adjustment quantity', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);
    $producto = stockAdjustmentProducto([
        'stock' => 15,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.stock-adjustments.store'), [
            'producto_id' => $producto->id,
            'stock_nuevo' => 4,
            'motivo' => 'Inventory shrink correction',
        ])
        ->assertRedirect(route('admin.reportes.stock-movimientos', [
            'producto_id' => $producto->id,
        ]));

    expect($producto->refresh()->stock)->toBe(4);

    $this->assertDatabaseHas('stock_movimientos', [
        'producto_id' => $producto->id,
        'user_id' => $admin->id,
        'tipo' => StockMovimiento::TIPO_AJUSTE,
        'cantidad' => 11,
        'stock_anterior' => 15,
        'stock_nuevo' => 4,
        'motivo' => 'Inventory shrink correction',
    ]);
});

it('requires a stock adjustment reason', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);
    $producto = stockAdjustmentProducto([
        'stock' => 5,
    ]);

    $this->actingAs($admin)
        ->from(route('admin.stock-adjustments.create'))
        ->post(route('admin.stock-adjustments.store'), [
            'producto_id' => $producto->id,
            'stock_nuevo' => 8,
        ])
        ->assertRedirect(route('admin.stock-adjustments.create'))
        ->assertSessionHasErrors(['motivo']);

    expect($producto->refresh()->stock)->toBe(5);
    $this->assertDatabaseCount('stock_movimientos', 0);
});

it('rejects blank stock adjustment reasons', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);
    $producto = stockAdjustmentProducto([
        'stock' => 5,
    ]);

    $this->actingAs($admin)
        ->from(route('admin.stock-adjustments.create'))
        ->post(route('admin.stock-adjustments.store'), [
            'producto_id' => $producto->id,
            'stock_nuevo' => 8,
            'motivo' => '   ',
        ])
        ->assertRedirect(route('admin.stock-adjustments.create'))
        ->assertSessionHasErrors(['motivo']);

    expect($producto->refresh()->stock)->toBe(5);
    $this->assertDatabaseCount('stock_movimientos', 0);
});

it('rejects stock adjustment values above the safe integer bound', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);
    $producto = stockAdjustmentProducto([
        'stock' => 5,
    ]);

    $this->actingAs($admin)
        ->from(route('admin.stock-adjustments.create'))
        ->post(route('admin.stock-adjustments.store'), [
            'producto_id' => $producto->id,
            'stock_nuevo' => InventoryLimits::MAX_STOCK_LEVEL + 1,
            'motivo' => 'Invalid correction',
        ])
        ->assertRedirect(route('admin.stock-adjustments.create'))
        ->assertSessionHasErrors(['stock_nuevo']);

    expect($producto->refresh()->stock)->toBe(5);
    $this->assertDatabaseCount('stock_movimientos', 0);
});

it('rejects unchanged stock adjustments without writing a movement', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);
    $producto = stockAdjustmentProducto([
        'stock' => 5,
    ]);

    $this->actingAs($admin)
        ->from(route('admin.stock-adjustments.create'))
        ->post(route('admin.stock-adjustments.store'), [
            'producto_id' => $producto->id,
            'stock_nuevo' => 5,
            'motivo' => 'No change after recount',
        ])
        ->assertRedirect(route('admin.stock-adjustments.create'))
        ->assertSessionHasErrors(['stock_nuevo']);

    expect($producto->refresh()->stock)->toBe(5);
    $this->assertDatabaseCount('stock_movimientos', 0);
});
