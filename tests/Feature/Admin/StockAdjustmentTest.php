<?php

declare(strict_types=1);

use App\Http\Requests\Admin\StoreStockAdjustmentRequest;
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
    $producto = stockAdjustmentProducto(['stock' => 6]);

    $this->get(route('admin.stock-adjustments.create'))
        ->assertRedirect(route('login', absolute: false));

    $this->post(route('admin.stock-adjustments.store'), [])
        ->assertRedirect(route('login', absolute: false));

    $this->actingAs($worker)
        ->get(route('admin.stock-adjustments.create'))
        ->assertForbidden();

    $this->actingAs($worker)
        ->post(route('admin.stock-adjustments.store'), [
            'producto_id' => $producto->id,
            'stock_nuevo' => 9,
            'motivo' => 'Unauthorized correction',
        ])
        ->assertForbidden();

    expect($producto->refresh()->stock)->toBe(6);
    $this->assertDatabaseCount('stock_movimientos', 0);
});

it('authorizes stock adjustments through the updateStock policy', function (string $role, bool $authorized): void {
    $user = User::factory()->make(['rol' => $role]);
    $request = StoreStockAdjustmentRequest::create('/stock-adjustments', 'POST');
    $request->setUserResolver(static fn (): User => $user);

    expect($request->authorize())->toBe($authorized);
})->with([
    'administrator' => ['administrador', true],
    'legacy administrator' => ['admin', true],
    'worker' => ['trabajador', false],
    'legacy worker' => ['empleado', false],
]);

it('denies stock adjustment authorization to guests', function (): void {
    $request = StoreStockAdjustmentRequest::create('/stock-adjustments', 'POST');
    $request->setUserResolver(static fn (): null => null);

    expect($request->authorize())->toBeFalse();
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

it('rejects unicode-only stock adjustment reasons', function (string $motivo): void {
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
            'motivo' => $motivo,
        ])
        ->assertRedirect(route('admin.stock-adjustments.create'))
        ->assertSessionHasErrors(['motivo']);

    expect($producto->refresh()->stock)->toBe(5);
    $this->assertDatabaseCount('stock_movimientos', 0);
})->with([
    'non-breaking spaces' => ["\u{00A0}\u{00A0}"],
    'unicode separators' => ["\u{2002}\u{2003}"],
    'byte order marks' => ["\u{FEFF}\u{FEFF}"],
]);

it('trims unicode whitespace around a stock adjustment reason', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);
    $producto = stockAdjustmentProducto([
        'stock' => 5,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.stock-adjustments.store'), [
            'producto_id' => $producto->id,
            'stock_nuevo' => 8,
            'motivo' => "\u{00A0}\u{FEFF}Physical count correction\u{2003}",
        ])
        ->assertRedirect(route('admin.reportes.stock-movimientos', [
            'producto_id' => $producto->id,
        ]));

    $this->assertDatabaseHas('stock_movimientos', [
        'producto_id' => $producto->id,
        'tipo' => StockMovimiento::TIPO_AJUSTE,
        'motivo' => 'Physical count correction',
    ]);
});

it('accepts 255 meaningful adjustment reason characters wrapped in unicode whitespace', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);
    $producto = stockAdjustmentProducto([
        'stock' => 5,
    ]);
    $motivo = str_repeat('x', 255);

    $this->actingAs($admin)
        ->post(route('admin.stock-adjustments.store'), [
            'producto_id' => $producto->id,
            'stock_nuevo' => 8,
            'motivo' => "\u{00A0}{$motivo}\u{FEFF}",
        ])
        ->assertRedirect(route('admin.reportes.stock-movimientos', [
            'producto_id' => $producto->id,
        ]));

    $this->assertDatabaseHas('stock_movimientos', [
        'producto_id' => $producto->id,
        'tipo' => StockMovimiento::TIPO_AJUSTE,
        'motivo' => $motivo,
    ]);
});

it('keeps invalid adjustment product IDs as validation errors for authorized users', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $this->actingAs($admin)
        ->from(route('admin.stock-adjustments.create'))
        ->post(route('admin.stock-adjustments.store'), [
            'producto_id' => 999999,
            'stock_nuevo' => 8,
            'motivo' => 'Physical count correction',
        ])
        ->assertRedirect(route('admin.stock-adjustments.create'))
        ->assertSessionHasErrors(['producto_id']);

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
